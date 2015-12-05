<?php

class NewsHolder extends Page{

	private static $description = 'News article collection holder';

	private static $allowed_children = array(
		'NewsArticle'
	);

	private static $extensions = array(
		'Lumberjack',
	);

	private static $articles_per_page = 10;


	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->fieldByName('Root.ChildPages')->setTitle('News Articles');

		if(Config::inst()->get('NewsArticle', 'enable_tags')) {
			$fields->addFieldsToTab(
				'Root.Tags',
				GridField::create(
					'Tags',
					'News Article Tags',
					NewsTag::get(),
					GridFieldConfig_recordEditor::create()
				)
			);
		}

		if(Config::inst()->get('NewsArticle', 'author_mode') == 'object') {
			$fields->addFieldsToTab(
				'Root.Authors',
				GridField::create(
					'Authors',
					'News Article Authors',
					NewsAuthor::get(),
					GridFieldConfig_recordEditor::create()
				)
			);
		}

		$this->extend('updateNewsHolderCMSFields', $fields);

		return $fields;
	}


	public function getArticleList($tag=null, $year=null, $month=null) {
		$list = NewsArticle::get()->filter('ParentID', $this->ID);

		// filter by tag?
		if($tag) {
			$list = $list->filter('Tags.ID:exactMatch', $tag);
		}

		// filter buy date range?
		$year = (int) $year;
		$month = (int) $month;

		if ($year && $month) {
			$beginDate = "$year-$month-01 00:00:00";
			$endDate = date('Y-m-d H:i:s', strtotime("$year-$month-1 00:00:00 +1 month"));
			$list = $list->where("(\"NewsArticle\".\"PublishDate\">='$beginDate' AND \"NewsArticle\".\"PublishDate\"<'$endDate')");
		}elseif($year) {
			$beginDate = "$year-01-01 00:00:00";
			$endDate = "$year-12-31 23:59:59";
			$list = $list->where("(\"NewsArticle\".\"PublishDate\">='$beginDate' AND \"NewsArticle\".\"PublishDate\"<'$endDate')");
		}

		return $list;
	}


	/**
	 * List of News tags used by articles in this section
	 * @return DataList
	 **/
	public function getTagList() {
		$articleIDs = $this->getArticleList()->column('ID');
		$articleIDs = implode(',', $articleIDs);
		$tags = NewsTag::get()
			->innerJoin('NewsArticle_Tags', '"NewsTag"."ID"="NewsArticle_Tags"."NewsTagID"')
			->where("\"NewsArticle_Tags\".\"NewsArticleID\" IN ($articleIDs)")
			->sort('Title');
		return $tags;
	}


	/**
	 * Generates an ArrayList of archive years/months that contain news articles.
	 * The currently filtered tag is considered an limits archive dates too.
	 * @return ArrayList
	 **/
	public function getArchiveList($link=null, $currentYear=null, $currentMonth=null, $tag=null) {
		$link = $link ? $link : $this->Link();
		$articles = $this->getArticleList($tag);

		$years = array();

		foreach ($articles as $article) {
			$date = $article->obj('PublishDate');
			$year = $date->Format('Y');

			if($year) {
				$monthNumber = $date->Format('n');
				$monthName = $date->Format('M');

				// if this month is aleady set, we don't need to set it again.
				if(isset($years[$year]['Months'][$monthNumber])) {
					continue;
				}

				// Check if the currently processed month is the one that is selected via GET params.
				$active = false;
				if (isset($monthNumber)) {
					$active = (((int) $currentYear)==$year && ((int) $currentMonth)==$monthNumber);
				}

				// Build the link - keep the tag and date filter, but reset the pagination.
				$link = HTTP::setGetVar('month', $monthNumber, $link, '&');
				$link = HTTP::setGetVar('year', $year, $link, '&');
				$monthLink = HTTP::setGetVar('start', null, $link, '&');
				$yearLink = HTTP::setGetVar('month', null, $link, '&');


				// Set up the relevant year array, if not yet available.
				if(!isset($years[$year])) {
					// Check if the currently processed year is the one that is selected via GET params.
					$isActiveYear = (((int) $currentYear)==$year);

					$years[$year] = array(
						'YearName'=> $year,
						'Months'=> array(),
						'Link' => $yearLink,
						'Active'=> $isActiveYear,
						'LinkingMode'=> $isActiveYear ? "current" : "link"
					);
				}

				if(!isset($years[$year]['Months'][$monthNumber])) {
					$years[$year]['Months'][$monthNumber] = array(
						'MonthName'=>$monthName,
						'MonthNumber'=>$monthNumber,
						'Link'=>$monthLink,
						'Active'=>$active,
						'LinkingMode'=> $active ? "current" : "link"
					);
				}
			}
		}

		// ArrayList will not recursively walk through the supplied array, so manually build nested ArrayLists.
		foreach ($years as &$year) {
			$year['Months'] = new ArrayList($year['Months']);
		}

		// Reverse the list so the most recent years appear first.
		return new ArrayList(array_reverse($years));
	}

	/**
	 * Used by templates to access and iterate over archive years/months
	 * @return ArrayList
	 **/
	public function HolderController() {
		return ModelAsController::controller_for($this);
	}
}

class NewsHolder_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'rss'
	);

	/**
	 * Used by templates to access and iterate over articles
	 * @var $limit - number of articles per page (defaults to articles_per_page config value)
	 * @return PaginatedList
	 **/
	public function Articles($limit = null) {
		$tag = $this->getCurrentTag();
		$year = $this->getCurrentYear();
		$month = $this->getCurrentMonth();
		$start = (int) $this->request->requestVar('start') ?: null;
		$limit = $limit ? $limit : NewsHolder::config()->get('articles_per_page');

		$list = $this->data()->getArticleList($tag, $year, $month);

		$paged = new PaginatedList($list, $this->request);
		$paged->setPageLength($limit);

		return $paged;
	}

	/**
	 * Used by templates to access and iterate over archive years/months
	 * @return ArrayList
	 **/
	public function Archive() {
		$link = Director::makeRelative($_SERVER['REQUEST_URI']);
		$currentYear = $this->getCurrentYear();
		$currentMonth = $this->getCurrentMonth();
		$tag = $this->getCurrentTag();

		return $this->data()->getArchiveList($link, $currentYear, $currentMonth, $tag);
	}

	/**
	 * Link to RSS feed
	 * @return string
	 **/
	public function RSSLink() {
		return $this->Link('rss');
	}

	/**
	 * Renders RSS feed
	 **/
	public function rss() {
		$rss = new RSSFeed($this->data()->getArticleList()->limit(20), $this->Link(), $this->Title);
		return $rss->outputToBrowser();
	}

	/**
	 * getLinkedTagList
	 * Gets a list of tags with links, keeping current year and month request vars
	 * @return ArrayList
	 **/
	public function getLinkedTagList() {
		$link = $this->Link();
		$link = HTTP::setGetVar('year', $this->getCurrentYear(), $link, '&');
		$link = HTTP::setGetVar('month', $this->getCurrentMonth(), $link, '&');

		$tags = $this->data()->getTagList();
		$list = ArrayList::create();
		$currentTag = $this->getCurrentTag();

		$list->push(ArrayData::create(array(
			"Title" => 'All',
			"Link" => HTTP::setGetVar('tag', null, $link, '&'),
			"Current" => (!$currentTag)
		)));

		foreach ($tags as $tag) {
			$list->push(ArrayData::create(array(
				"Title" => $tag->Title,
				"Link" => HTTP::setGetVar('tag', $tag->ID, $link, '&'),
				"Current" => ($tag->ID == $currentTag)
			)));
		}

		return $list;
	}


	/**
	 * getCurrentYear
	 * @return int
	 **/
	public function getCurrentYear() {
		return (int) $this->request->requestVar('year') ?: null;
	}


	/**
	 * getCurrentMonth
	 * @return int
	 **/
	public function getCurrentMonth() {
		return (int) $this->request->requestVar('month') ?: null;
	}


	/**
	 * getCurrentTag
	 * @return int
	 **/
	public function getCurrentTag() {
		return (int) $this->request->requestVar('tag') ?: null;
	}
}

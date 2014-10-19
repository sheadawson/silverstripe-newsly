<?php

class NewsArticle extends Page{

	private static $description = 'A news article page';
	
	private static $db = array(
		"Summary" => "HTMLText",
		"PublishDate" => "Date",
		"Author" => "Varchar",
		"Featured" => "Boolean"
	);

	private static $has_one = array(
		"Attachment" => "File",
		"Image" => "Image",
		"NewsAuthor" => "NewsAuthor"
	);	

	private static $many_many = array(
		"Tags" => "NewsTag"
	);	

	private static $defaults = array(
		"ShowInMenus" => 0
	);

	private static $default_sort = 'PublishDate DESC';

	private static $summary_fields = array(
		'Title' => 'Title',
		'PublishDate.Nice' => 'Date Published',
	);

	/**
	 * Enable documents to be attached to articles
	 * @var bool
	 **/
	private static $enable_attachments = true;

	/**
	 * Enable images to be attached to articles
	 * @var bool
	 **/
	private static $enable_images = true;

	/**
	 * Enable a summary field on articles
	 * @var bool
	 **/
	private static $enable_summary = true;

	/**
	 * Enable article tagging
	 * @var bool
	 **/
	private static $enable_tags = true;

	/**
	 * Enable featured articles
	 * @var bool
	 **/
	private static $enable_featured_articles = true;

	/**
	 * Folder where article images should be stored
	 * @var string
	 **/
	private static $image_folder = 'news/images';

	/**
	 * Folder where article attachments should be stored
	 * @var string
	 **/
	private static $attachment_folder = 'news/attachments';

	/**
	 * Author mode, currently can be 'string' to enable a Author text field
	 * or (bool)false to disable. 'Object' author mode may be available in future
	 * @var string|bool
	 **/
	private static $author_mode = 'string';


	/**
	 * getCMSFields
	 * @return FieldList
	 **/
	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->dataFieldByName('Title')->setTitle('Article Title');
		$fields->dataFieldByName('Content')->setTitle('Article Content');

		$config = $this->config();

		// publish date
		$fields->addFieldToTab('Root.Main', DateField::create('PublishDate')->setAttribute('placeholder', $this->dbObject('Created')->Format('M d, Y')), 'Content');

		// tags
		if($config->enable_tags){
			$tagSource = function(){
				return NewsTag::get()->map()->toArray();
			};

			$fields->addFieldToTab(
				'Root.Main', 
				ListboxField::create('Tags', 'Tags', $tagSource(), null, null, true)
					->useAddnew('NewsTag', $tagSource),
				'Content'
			);
		}

		// author
		if($config->author_mode == 'string'){
			$fields->addFieldToTab('Root.Main', TextField::create('Author', 'Author'), 'Content');
		}

		if($config->author_mode == 'object'){
			$authorSource = function(){ 
				return NewsAuthor::get()->map('ID', 'Name')->toArray();
			};
			
			$fields->addFieldToTab(
				'Root.Main', 
				DropdownField::create(
					'NewsAuthorID', 
					'Author', 
					$authorSource()
				)
				->useAddNew('NewsAuthor', $authorSource)
				->setHasEmptyDefault(true),
				'Summary'
			);
		}

		// featured
		if($config->enable_featured_articles){
			$fields->addFieldToTab(
				'Root.Main', 
				CheckboxField::create('Featured', _t('NewsArticle.FEATURED', 'Feature this article')),
				'Content'
			);
		}

		// todo - object author mode

		// images
		if($config->enable_images){
			$fields->addFieldToTab(
				'Root.FilesAndImages', 
				UploadField::create('Image')
					->setAllowedFileCategories('image')
					->setAllowedMaxFileNumber(1)
					->setFolderName($config->get('image_folder'))
			);
		}

		// attachments
		if($config->enable_attachments){
			$fields->addFieldToTab(
				'Root.FilesAndImages', 
				UploadField::create('Attachment')
					->setAllowedFileCategories('doc')
					->setAllowedMaxFileNumber(1)
					->setFolderName($config->get('attachment_folder'))
			);
		}

		// summary
		if($config->enable_summary){
			$fields->addFieldToTab('Root.Main', HTMLEditorField::create('Summary', 'Article Summary'), 'Content');	
		}

		// parent
		$holders = NewsHolder::get();
		$fields->addFieldToTab('Root.Main', DropdownField::create('ParentID', 'News Section', $holders->map()->toArray()), 'Title');

		$this->extend('updateArticleCMSFields', $fields);

		return $fields;
	}


	/**
	 * onBeforeWrite
	 **/
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		// Set publish date to the created date, if publish date not set
		if(!$this->PublishDate){
			$this->PublishDate = $this->Created;
		}

		// a bit hackey, but we need to set the parent ID somehow in GridField form...
		if($currentPage = Controller::curr()->currentPage()){
			if($currentPage->ClassName == 'NewsHolder' || is_subclass_of($currentPage, 'NewsHolder')){
				$this->ParentID = $currentPage->ID;
			}
		}
	}


	/**
	 * Generates an ArrayList of this artices tags with Links
	 * @return ArrayList
	 **/
	public function LinkedTags(){
		$tags = $this->Tags();
		if($tags->count()){
			$list = ArrayList::create();
			$holder = $this->Parent();
			foreach ($tags as $tag) {
				$linkedTag = ArrayData::create(array(
					'Title' => $tag->Title,
					'ID' => $tag->ID,
					'Link' => Controller::join_links($holder->Link(), "?tag=$tag->ID")
				));	

				$list->push($linkedTag);
			}
			return $list;
		}
	}


	/**
	 * Author Name
	 * @return String
	 **/
	public function AuthorName(){
		$author = $this->NewsAuthor();
		if($author){
			return $author->Name;
		}
		return $this->Author;
	}
}

class NewsArticle_Controller extends Page_Controller{
	
}
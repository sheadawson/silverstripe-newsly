<?php

class NewsTag extends DataObject{
	private static $db = array(
		"Title" => "Varchar"
	);

	private static $belongs_many_many = array(
		"NewsArticles" => "NewsArticle"
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'NewsArticles.Count' => 'Number of articles'
	);

	private static $default_sort = 'Title ASC';

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('NewsArticles');

		return $fields;
	}
}

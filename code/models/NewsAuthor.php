<?php

class NewsAuthor extends DataObject {
	private static $db = array(
		'Name' => 'Varchar',
		'Website' => 'Varchar(255)'
	);

	private static $has_many = array(
		'NewsArticles' => 'NewsArticle'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('NewsArticles');
		return $fields;
	}

	public function forTemplate(){
		if($this->Website){
			return "<a href='$this->Website' target='_blank'>$this->Name</a>";
		}else{
			return $this->Name;
		}
	}
}
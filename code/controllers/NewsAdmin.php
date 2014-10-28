<?php

class NewsAdmin extends ModelAdmin{
	private static $menu_title = 'News';

	private static $url_segment = 'news';

	private static $menu_icon = 'newsly/images/news-icon.png';

	private static $managed_models = array(
		'NewsArticle',
		'NewsTag',
		'NewsAuthor'
	);

	public function getManagedModels() {
		$models = parent::getManagedModels();
		
		if(NewsArticle::config()->get('author_mode') != 'object'){
			unset($models['NewsAuthor']);
		}

		if(!NewsArticle::config()->get('enable_tags')){
			unset($models['NewsTag']);
		}

		return $models;
	}
}
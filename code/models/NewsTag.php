<?php

class NewsTag extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        "Title" => "Varchar"
    );

    /**
     * @var array
     */
    private static $belongs_many_many = array(
        "NewsArticles" => "NewsArticle"
    );

    /**
     * @var array
     */
    private static $summary_fields = array(
        'Title' => 'Title',
        'NewsArticles.Count' => 'Number of articles'
    );

    /**
     * @var string
     */
    private static $default_sort = 'Title ASC';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('NewsArticles');

        return $fields;
    }
}

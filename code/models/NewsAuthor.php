<?php

class NewsAuthor extends DataObject
{
    private static $db = array(
        'Name' => 'Varchar',
    );

    private static $has_one = array(
        'Image' => 'Image',
        'Link' => 'Link'
    );

    private static $has_many = array(
        'NewsArticles' => 'NewsArticle'
    );


    /**
     * Enable links to be attached to authors
     * @var bool
     **/
    private static $enable_links = true;


    /**
     * Enable images to be attached to authors
     * @var bool
     **/
    private static $enable_images = true;


    /**
     * Folder where news author images should be stored
     * @var string
     **/
    private static $image_folder = 'news/authors';


    public function getCMSFields()
    {
        $config = $this->config();
        $fields = parent::getCMSFields();
        $fields->removeByName('NewsArticles');

        // images
        if ($config->enable_images) {
            $fields->dataFieldByName('Image')->setFolderName($config->image_folder);
        } else {
            $fields->removeByName('Image');
        }

        // links
        if ($config->enable_links) {
            $fields->addFieldToTab('Root.Main', LinkField::create('LinkID', 'Link'));
        } else {
            $fields->removeByName('LinkID');
        }

        return $fields;
    }


    public function forTemplate()
    {
        return $this->Name;
    }
}

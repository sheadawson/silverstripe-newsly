<?php

class NewsArticle extends Page
{
    /**
     * @var string
     */
    private static $description = 'A news article page';

    /**
     * @var array
     */
    private static $db = array(
        "Summary" => "HTMLText",
        "PublishDate" => "Date",
        "Author" => "Varchar",
        "Featured" => "Boolean"
    );

    /**
     * @var array
     */
    private static $has_one = array(
        "Attachment" => "File",
        "Image" => "Image",
        "NewsAuthor" => "NewsAuthor"
    );

    /**
     * @var array
     */
    private static $many_many = array(
        "Tags" => "NewsTag"
    );

    /**
     * @var array
     */
    private static $defaults = array(
        "ShowInMenus" => 0
    );

    /**
     * @var array
     */
    private static $summary_fields = array(
        'Title' => 'Title',
        'PublishDate.Nice' => 'Date Published',
        'ArticleIsPublished.Nice' => 'Published'
    );

    /**
     * @var string
     */
    private static $default_sort = 'PublishDate DESC';

    /**
     * Manage these pages in a gridfield, rather than site tree
     *
     * @var bool
     **/
    private static $show_in_sitetree = false;

    /**
     * Enable documents to be attached to articles
     *
     * @var bool
     **/
    private static $enable_attachments = true;

    /**
     * Enable images to be attached to articles
     *
     * @var bool
     **/
    private static $enable_images = true;

    /**
     * Enable a summary field on articles
     *
     * @var bool
     **/
    private static $enable_summary = true;

    /**
     * Enable article tagging
     *
     * @var bool
     **/
    private static $enable_tags = true;

    /**
     * Enable featured articles
     *
     * @var bool
     **/
    private static $enable_featured_articles = true;

    /**
     * Folder where article images should be stored
     *
     * @var string
     **/
    private static $image_folder = 'news/images';

    /**
     * Folder where article attachments should be stored
     *
     * @var string
     **/
    private static $attachment_folder = 'news/attachments';

    /**
     * Author mode can be:
     * - string "string" to enable an Author text field (default)
     * - string "object" to enable Author DataObject
     * - boolean false to disable Article Authors
     *
     * @var string|bool
     **/
    private static $author_mode = 'string';

    /**
     * @return FieldList
     **/
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // set different names for some fields
        $fields->dataFieldByName('Title')->setTitle('Article Title');
        $fields->dataFieldByName('Content')->setTitle('Article Content');

        $config = $this->config();

        // publish date
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('PublishDate')
                ->setAttribute('placeholder', $this->dbObject('Created')->Format('M d, Y'))
                ->setConfig('showcalendar', true),
            'Content'
        );

        // tags
        if ($config->enable_tags) {
            $tagSource = function () {
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
        if ($config->author_mode == 'string') {
            $fields->addFieldToTab('Root.Main', TextField::create('Author', 'Author'), 'Content');
        }

        if ($config->author_mode == 'object') {
            $authorSource = function () {
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
                'Content'
            );
        }

        // featured
        if ($config->enable_featured_articles) {
            $fields->addFieldToTab(
                'Root.Main',
                CheckboxField::create('Featured', _t('NewsArticle.FEATURED', 'Feature this article')),
                'Content'
            );
        }

        // images
        if ($config->enable_images) {
            $fields->addFieldToTab(
                'Root.FilesAndImages',
                UploadField::create('Image')
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
                    ->setFolderName($config->get('image_folder'))
            );
        }

        // attachments
        if ($config->enable_attachments) {
            $fields->addFieldToTab(
                'Root.FilesAndImages',
                UploadField::create('Attachment')
                    ->setAllowedFileCategories('doc')
                    ->setAllowedMaxFileNumber(1)
                    ->setFolderName($config->get('attachment_folder'))
            );
        }

        // summary
        if ($config->enable_summary) {
            $fields->addFieldToTab(
                'Root.Main',
                HTMLEditorField::create('Summary', 'Article Summary')->setRows(5),
                'Content'
            );
        }

        // parent
        $holders = NewsHolder::get();
        if ($holders->count() > 1) {
            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create('ParentID', 'News Section', $holders->map()->toArray()),
                'Title'
            );
        } else {
            $fields->addFieldToTab(
                'Root.Main',
                HiddenField::create('ParentID', 'News Section', $holders->first()->ID),
                'Title'
            );
        }

        $this->extend('updateArticleCMSFields', $fields);

        return $fields;
    }

    /**
     * onBeforeWrite
     **/
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Set publish date to the created date, if publish date not set
        if (!$this->PublishDate) $this->PublishDate = $this->Created;

        // a bit hackey, but we need to set the parent ID somehow in GridField form...
        if ($currentPage = Controller::curr()->currentPage()) {
            if ($currentPage->ClassName == 'NewsHolder' || is_subclass_of($currentPage, 'NewsHolder')) {
                $this->ParentID = $currentPage->ID;
            }
        }
    }


    /**
     * Generates an ArrayList of this artices tags with Links
     *
     * @return ArrayList
     **/
    public function LinkedTags()
    {
        $tags = $this->Tags();
        if ($tags->count()) {
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
     *
     * @return string
     **/
    public function AuthorName()
    {
        $author = $this->NewsAuthor();

        return ($author->exists()) ? $author->Name : $this->Author;
    }

    /**
     * RelatedArticles - Returns a list of articles that share the same tags as this one
     *
     * @param int $limit
     * @return DataList
     **/
    public function RelatedArticles($limit = null)
    {
        $tagIDs = $this->Tags()->column('ID');

        if (count($tagIDs)) {
            return NewsArticle::get()
                ->filter("Tags.ID:exactMatch", $tagIDs)
                ->exclude('ID', $this->ID);
        }
    }


    /**
     * ArticleIsPublished - flag for summary_fields
     *
     * @return DBField
     **/
    public function ArticleIsPublished()
    {
        $field = Boolean::create('IsPublished');

        $field->setValue($this->isPublished());

        return $field;
    }


    /**
     * ShareLink
     *
     * @return string
     **/
    public function ShareLink()
    {
        return urlencode($this->AbsoluteLink());
    }
}

class NewsArticle_Controller extends Page_Controller
{
}

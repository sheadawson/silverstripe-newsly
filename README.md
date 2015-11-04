# SilverStripe Newsly

Newsly is a simple news/blog module for SilverStripe. It aims to offer configurable and optional features that cater to a range of common news-section requirements.

## Requirements

* SilverStripe 3
* [Lumberjack](https://github.com/micmania1/silverstripe-lumberjack)
* [Quickaddnew](https://github.com/sheadawson/silverstripe-quickaddnew)


## Maintainers

* shea@livesource.co.nz

## Installation

	composer require sheadawson/silverstripe-newsly

## Features

* NewsHolder and NewsArticle page types
* Articles managed in gridfield with lumberjack
* Articles can have images and attachements (optional)
* Article can tags (optional)
* Related articles (related via shared tags)
* Article Authors (optional, can be configured as a simple string or a NewsAuthor object/profile)

## Configuration

The following configurations can be set in your yml config:

```yml
NewsArticle:
  setting: value
```

| Setting | Description | Options | Default |
|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------|-------------|
| enable_attachments | Enables a file attachment field in the NewsArticle CMSFields | boolean | true |
| enable_images | Enable documents to be attached to articles | boolean | true |
| enable_summary | Enables a Summary HTMLText field on NewsArticle, to be used for the article introduction/summary.  | boolean | true |
| enable_tags | Enables Article Tagging. Disable this to remove the Tag field from the News Article CMS Fields  | boolean | true |
| enable_featured_articles | Enables a "Feature this Article" check box to the News Article CMS Fields. You can use this to feature worthy articles on a home page, for example  | boolean | true |
| image_folder | Folder where article images should be stored | string | news/images |
| attachment_folder | Folder where article attachments should be stored |  |  |
| author_mode | Author mode can be: - string "string" to enable an Author text field (default) - string "object" to enable Author DataObject - boolean false to disable Article Authors | string | boolean |  |

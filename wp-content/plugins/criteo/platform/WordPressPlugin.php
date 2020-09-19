<?php

defined('ABSPATH') or die('Forbidden');

/**
 * The model class for a WordPress plugin.
 */
class WordPressPlugin {

    public $name;
    public $uri;
    public $version;
    public $description;
    public $author;
    public $authorUri;
    public $textDomain;
    public $network;
    public $title;
    public $authorName;
    public $active;

    function __construct($name, $uri, $version, $description, $author, $authorUri, $textDomain,
                         $network, $title, $authorName, $active)
    {
        $this->name = $name;
        $this->uri = $uri;
        $this->version = $version;
        $this->description = $description;
        $this->author = $author;
        $this->authorUri = $authorUri;
        $this->textDomain = $textDomain;
        $this->network = $network;
        $this->title = $title;
        $this->authorName = $authorName;
        $this->active = $active;
    }

}
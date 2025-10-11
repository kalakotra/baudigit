<?php

class OfferPage {
    // Properties and methods for the OfferPage class
    protected $title;
    protected $content;

    public function __construct($title, $content) {
        $this->title = $title;
        $this->content = $content;
    }

    public function render() {
        // Logic to render the offer page
        return "<h1>{$this->title}</h1><p>{$this->content}</p>";
    }
}
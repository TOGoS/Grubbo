<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/StringBlob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_Value_SimpleResource implements Grubbo_Value_Resource {
    protected $content;
    protected $contentMetadata;

    public function __construct( $content, $contentMetadata ) {
        if( is_string($content) ) $content = new Grubbo_Value_StringBlob($content);
        $this->content = $content;
        $this->contentMetadata = $contentMetadata;
    }

    public function getContent() {
        return $this->content;
    }

    public function getContentMetadata() {
        return $this->contentMetadata;
    }
}

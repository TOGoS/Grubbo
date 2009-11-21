<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_Value_StringResource implements Grubbo_Value_Resource, Grubbo_Value_Blob {
    protected $content;
    protected $contentMetadata;

    public function __construct( $content, $contentMetadata ) {
        $this->content = $content;
        $this->contentMetadata = $contentMetadata;
    }

    public function getContentMetadata() {
        return $this->contentMetadata;
    }

    public function getContent() {
        return $this->content;
    }

    public function writeContent($stream) {
        fwrite( $stream, $this->getContent() );
    }
}

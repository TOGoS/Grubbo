<?php

class Grubbo_Value_StringBlob implements Grubbo_Value_Blob {
    protected $data;

    public function __construct( $data ) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function writeDataToStream($stream) {
        $stream->write( $this->getData() );
    }
}
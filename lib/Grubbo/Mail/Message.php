<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_Mail_Message implements Grubbo_Value_Blob, Grubbo_Value_Resource {
    function __construct( $from, $to, $subject, $content, $cc=array(), $bcc=array() ) {
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->content = $content;
        $this->cc = $cc;
        $this->bcc = $bcc;
    }

    function getTo() {
        return $this->to;
    }
    function getFrom() {
        return $this->from;
    }
    function getSubject() {
        return $this->subject;
    }
    function getCc() {
        return $this->cc;
    }
    function getBcc() {
        return $this->bcc;
    }
    function getContentMetadata() {
        return array();
    }
    function getContent() {
        return $this->content;
    }
    function writeContent($stream) {
        fwrite( $stream, $this->getContent() );
    }
}

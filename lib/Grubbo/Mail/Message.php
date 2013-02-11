<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_Mail_Message implements Grubbo_Value_Blob, Grubbo_Value_Resource {
	function __construct( $from, $to, $subject, $text, $cc=array(), $bcc=array() ) {
		$this->from = $from;
		$this->to = $to;
		$this->subject = $subject;
		$this->text = $text;
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
		return $this;
	}

	function getData() {
		return $this->text;
	}
	function writeDataToStream($stream) {
		$stream->write( $this->getData() );
	}
}

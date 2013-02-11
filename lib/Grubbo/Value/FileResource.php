<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_Value_FileResource implements Grubbo_Value_Resource, Grubbo_Value_Blob {
	protected $file;
	protected $contentMetadata;

	public function __construct( $file, $contentMetadata=array() ) {
		$this->file = $file;
		$this->contentMetadata = $contentMetadata;
	}

	public function getContentMetadata() {
		return $this->contentMetadata;
	}

	public function getContent() {
		return file_get_contents( $this->file );
	}

	public function writeContent($stream) {
		fwrite( $stream, $this->getContent() );
	}
}


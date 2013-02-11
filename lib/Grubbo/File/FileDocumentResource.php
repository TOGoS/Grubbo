<?php

require_once 'Grubbo/Value/Blob.php';
require_once 'Grubbo/Value/Resource.php';

class Grubbo_File_FileDocumentResource implements Grubbo_Value_Resource, Grubbo_Value_Blob {
	protected $file;
	protected $contentMetadata;
	protected $content;
	protected $loaded;

	public function __construct( $file ) {
		$this->file = $file;
	}

	protected function load() {
		if( $this->loaded ) return;

		$this->contentMetadata = array();

		$fh = fopen( $this->file, 'r' );
		while( $l = trim(fgets($fh)) ) {
			if( preg_match( '/^(.*?):\s+(.*)$/', $l, $bif ) ) {
				$this->contentMetadata['doc/'.$bif[1]] = $bif[2];
			}
		}
		$this->content = stream_get_contents( $fh );
		fclose( $fh );
	}

	public function getContent() {
		return $this;
	}

	public function getContentMetadata() {
		$this->load();
		return $this->contentMetadata;
	}

	public function getData() {
		$this->load();
		return $this->content;
	}

	public function writeDataToStream( $stream ) {
		$stream->write( $this->getData() );
	}
}



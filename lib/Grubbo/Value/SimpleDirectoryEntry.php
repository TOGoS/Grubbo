<?php

require_once 'Grubbo/Value/DirectoryEntry.php';

class Grubbo_Value_SimpleDirectoryEntry
	extends Grubbo_Value_SimpleResource
	implements Grubbo_Value_DirectoryEntry
{
	protected $name;
	protected $target;
	protected $targetMetadata;

	public function __construct( $name, $target, $targetMetadata ) {
		$this->name = $name;
		$this->target = $target;
		$this->targetMetadata = $targetMetadata;
	}

	public function getName() {
		return $this->name;
	}

	public function getContent() {
		return $this->target;
	}

	public function getContentMetadata() {
		return $this->targetMetadata;
	}
}

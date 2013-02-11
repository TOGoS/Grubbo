<?php

class Grubbo_Vcs_CommitInfo {
	protected $author;
	protected $date;
	protected $description;

	public function __construct( Grubbo_Value_User $author, $date, $description ) {
		$this->author = $author;
		$this->date = $date;
		$this->description = $description;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getDate() {
		return $this->date;
	}

	public function getDescription() {
		return $this->description;
	}
}

<?php

class Grubbo_Mvc_ResourceAction {
	protected $name;
	protected $title;
	protected $qsArgs;

	public function __construct( $name, $title, $qsArgs=null ) {
		$this->name = $name;
		$this->title = $title;
		if( $qsArgs === null ) {
			$qsArgs = array('action'=>$name);
		}
		$this->qsArgs = $qsArgs;
	}
	public function getActionName() {
		return $this->name;
	}
	public function getActionTitle() {
		return $this->title;
	}
	public function getActionQueryString() {
		$ss = array();
		foreach( $this->qsArgs as $k=>$v ) {
			$ss[] = urlencode($k).'='.urlencode($v);
		}
		return implode('&',$ss);
	}
}

<?php

class Grubbo_Proc_ExternalProcess {
	protected $argv;

	public function __construct() {
		$args = func_get_args();
		if( is_array($args[0]) ) $args = $args[0];
		$this->argv = $args;
	}

	protected function getShellString() {
		$argvesc = array();
		foreach( $this->argv as $a ) {
			$argvesc[] = escapeshellarg($a);
		}
		return implode(' ',$argvesc);
	}

	public function run() {
		system( $this->getShellString(), $ret );
		return $ret;
	}

	public function runOrDie() {
		$cmd = $this->getShellString();
		exec( "$cmd 2>&1", $output, $ret );
		if( $ret ) {
			throw new Exception("Process returned error code $ret: $cmd: ".implode("\n",$output));
		}
	}
}

<?php

class Grubbo_Mvc_Template {
	protected $dispatcher;
	protected $tplDir;
	protected $name;
	protected $_args;

	function __construct( $dispatcher, $tplDir, $name ) {
		$this->dispatcher = $dispatcher;
		$this->tplDir = $tplDir;
		$this->name = $name;
		$this->_args = array();
	}

	function getTemplate( $name ) {
		return new Grubbo_Mvc_Template( $this->dispatcher, $this->tplDir, $name );
	}

	function outputTemplate( $tplName, $args=array() ) {
		$this->getTemplate($tplName)->output(array_merge($args,$this->_args));
	}

	function formatDropdown( $name, $values, $default ) {
		$str = "<select name=\"".htmlspecialchars($name)."\">\n";
		foreach( $values as $k=>$v ) {
			$str .= "<option value=\"".htmlspecialchars($k)."\"";
			if( $default == $k ) {
				$str .= " selected";
			}
			$str .= ">".htmlspecialchars($v)."</option>\n";
		}
		$str .= "</select>";
		return $str;
	}

	function pathTo( $uri ) {
		return $this->dispatcher->pathTo($uri);
	}

	function htmlPathTo( $uri ) {
		return htmlspecialchars($this->pathTo($uri));
	}

	function output( $__args ) {
		$__oldArgs = $this->_args;
		$this->_args = $__args;
		$__tplFile = $this->tplDir.'/'.$this->name.'.php';
		if( !file_exists($__tplFile) ) {
			throw new Exception("Template '$__tplFile' does not exist");
		}
		extract( $__args, EXTR_SKIP );
		include $__tplFile;
		$this->_args = $__oldArgs;
	}
}

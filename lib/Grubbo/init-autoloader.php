<?php

function Grubbo_autoload($className) {
	require_once str_replace(array('\\','_'),'/',$className).'.php';
	$classNameVariations = array( 
		str_replace('\\','_',$className)
	);
	if( !class_exists($className,false) ) {
		foreach( $classNameVariatiosn as $classNameB ) {
			if( class_exists($classNameB,false) ) {
				class_alias($classNameB,$className);
			}
		}
	}
}
spl_autoload_register('Grubbo_autoload');

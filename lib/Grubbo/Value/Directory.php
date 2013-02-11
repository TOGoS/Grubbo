<?php

interface Grubbo_Value_Directory {
	/** @return an array of name => blob or directory */
	function getEntries();
}

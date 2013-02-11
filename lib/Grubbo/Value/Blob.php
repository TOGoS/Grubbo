<?php

interface Grubbo_Value_Blob {
	function getData();
	function writeDataToStream($stream);
}

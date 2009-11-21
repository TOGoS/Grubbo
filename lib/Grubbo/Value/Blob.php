<?php

interface Grubbo_Value_Blob {
    function getContent();
    function writeContent($stream);
}

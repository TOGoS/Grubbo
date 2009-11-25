<?php

$rmd = $resource->getContentMetadata();
if( !$title ) { $title = @$rmd['title'];     }
if( !$title ) { $title = @$rmd['doc/title']; }
if( !$title ) { $title = 'Some Document';    }

?>
<?php $this->outputTemplate('page-header',array('pageTitle'=>$title)); ?>

<?php
$blob = $resource->getContent();
$blob->writeDataToStream( $outputStream );
?>

<?php $this->outputTemplate('page-footer',array()); ?>
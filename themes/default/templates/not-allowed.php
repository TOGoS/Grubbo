<?php $title = 'Action disallowed'; ?>

<?php $this->outputTemplate('page-header',array('pageTitle'=>$title)); ?>

<?php

echo $errorMessageHtml;

?>

<?php $this->outputTemplate('page-footer',array()); ?>
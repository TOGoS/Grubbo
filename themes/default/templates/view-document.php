<?php

$rmd = $resource->getContentMetadata();
$title = Grubbo_Util_ArrayUtil::coalesceArr(array(
    &$title, &$rmd['title'], &$rmd['doc/title'], 'Some Document'
));

$isTicket   = Grubbo_Util_ArrayUtil::coalesce($rmd['doc/ticket'],false);
$assignedTo = Grubbo_Util_ArrayUtil::coalesce($rmd['doc/assigned-to'], false);
$status     = Grubbo_Util_ArrayUtil::coalesce($rmd['doc/status'], false);

?>
<?php $this->outputTemplate('page-header',array('pageTitle'=>$title)); ?>

<?php if($isTicket) { ?>
<p><a href="my-open-tickets">My open tickets</a> | <a href="new-ticket">Create new ticket</a></p>
<table class="form-pairs">
<tr><th>Assigned to</th><td><?php echo htmlspecialchars($assignedTo); ?></td></tr>
<tr><th>Status</th><td><?php echo htmlspecialchars($status); ?></td></tr>
</table>
<?php } ?>

<?php
$blob = $resource->getContent();
$blob->writeDataToStream( $outputStream );
?>

<?php $this->outputTemplate('page-footer',array()); ?>

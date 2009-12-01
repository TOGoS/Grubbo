<?php

$rmd = $resource->getContentMetadata();
if( !$title ) { $title = @$rmd['title'];     }
if( !$title ) { $title = @$rmd['doc/title']; }
if( !$title ) { $title = 'Some Document';    }

$isTicket = $rmd['doc/ticket'];
$assignedTo = $rmd['doc/assigned-to'];
$status = $rmd['doc/status'];

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

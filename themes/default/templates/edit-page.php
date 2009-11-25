<?php $this->outputTemplate('page-header'); ?>

<?php

$metadata = $resource->getContentMetadata();
$content = $resource->getContent();
$hiddenMetadata = array();
$docTitle = $metadata['doc/title'] or $docTitle = 'Some Page';
$isTicket = $metadata['doc/ticket'];
$ticketStatus = $metadata['doc/status'];
$assignedTo = $metadata['doc/assigned-to'];
$module = $metadata['doc/module'];
$cc = $metadata['doc/cc'];
foreach( $metadata as $k=>$v ) {
    if( $k == 'doc/title' or $k == 'doc/cc' or $k == 'doc/assigned-to' ) {
    } else {
        $hiddenMetadata[$k] = $v;
    }
}
$text = $content->getData();

?>

<form method="POST">
<table class="form-pairs">
<input type="hidden" name="action" value="post"/>
<?php foreach( $hiddenMetadata as $k=>$v ) { ?>
<input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>"/>
<?php } ?>
<tr><th>Title</th><td><input type="text" name="doc/title" value="<?php echo htmlspecialchars($docTitle); ?>" size="60"/></td></tr>
<?php if($isTicket) { ?>
<tr><th>Assign to</th><td><input type="text" name="doc/assigned-to" value="<?php echo htmlspecialchars($assignedTo); ?>" size="60"/></td></tr>
<tr><th>Status</th><td><?php echo $this->formatDropdown('doc/status', $ticketStatusOptions, $ticketStatus); ?></td></tr>
<tr><th>Module</th><td><input type="text" name="doc/module" value="<?php echo htmlspecialchars($module); ?>" size="60"/></td></tr>
<?php } ?>
<tr><th>CC</th><td><input type="text" name="doc/cc" value="<?php echo htmlspecialchars($cc); ?>" size="60"/></td></tr>
<tr><td colspan="2">
<textarea name="content" rows="20" cols="80"><?php echo htmlspecialchars($text); ?></textarea><br />
</td></tr>
<tr><td colspan="2">
<input type="submit" name="update" value="Submit"/>
<?php if(!$newPage) { ?>
<input type="submit" name="delete" onclick="return confirm('Delete page?');" value="Delete"/>
<?php } ?>
</td></tr>
</table>
</form>

<?php $this->outputTemplate('page-footer'); ?>

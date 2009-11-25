<?php $this->outputTemplate('page-header',array('pageTitle'=>$pageTitle)); ?>

<form method="GET" action="filter-tickets">
<fieldset>
<legend>Filter tickets</legend>
<table class="form-pairs">
<tr><th>Assigned to</th><td><input type="text" name="assigned-to" value="<?php echo $currentFilter->assignedTo; ?>"/></td>
<tr><th>Status</th><td><?php echo $this->formatDropdown('status', $filterTicketStatusOptions, $currentFilter->status); ?></td></tr>
<tr><td colspan="2"><input type="submit" value="Apply Filter"/></td></tr>
</fieldset>
</table>
</form>

<p><a href="new-ticket">Create new ticket</a></p>

<table>
<tr><th>#</th><th>Module</th><th>Status</th><th>Assigned</th><th>Title</th></tr>
<?php

foreach( $tickets as $name=>$entry ) {
    $md = $entry->getContentMetadata();
    $target = $entry->getContent();

    if( $target instanceof Grubbo_Value_Directory ) {
        $href = "$name/";
    } else {
        $href = $name;
    }

    echo "<tr>";
    echo "<td><a href=\"", htmlspecialchars($href), "\">", htmlspecialchars($href), "</a></td>";
    echo "<td>", htmlspecialchars($md['doc/module']), "</td>";
    echo "<td>", htmlspecialchars($md['doc/status']), "</td>";
    echo "<td>", htmlspecialchars($md['doc/assigned-to']), "</td>";
    echo "<td><a href=\"", htmlspecialchars($href), "\">", htmlspecialchars($md['doc/title']), "</a></td>";
    echo "</tr>\n";
}

?>
</table>

<?php $this->outputTemplate('page-footer',array()); ?>

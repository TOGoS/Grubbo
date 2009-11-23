<?php $this->outputTemplate('page-header',array('pageTitle'=>$pageTitle)); ?>

<p style="color:red">TODO: put form with filtering options here.</p>
<p><a href="new-ticket">Create new ticket</a></p>

<table>
<tr><th>#</th><th>Module</th><th>Status</th><th>Assigned</th><th>Title</th></tr>
<?php

foreach( $tickets as $name=>$target ) {
    $md = $target->getContentMetadata();

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

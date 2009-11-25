<?php $this->outputTemplate('page-header',array('pageTitle'=>$pageTitle)); ?>

<ul>
<?php

$entries = $resource->getEntries();

$sortedEntries = array();
ksort($entries);
foreach( $entries as $name=>$target ) if( $target instanceof Grubbo_Value_Directory ) {
    $sortedEntries[$name] = $target;
}
foreach( $entries as $name=>$target ) if( !($target instanceof Grubbo_Value_Directory) ) {
    $sortedEntries[$name] = $target;
}

foreach( $sortedEntries as $name=>$entry ) {
    $target = $entry->getContent();
    if( $target instanceof Grubbo_Value_Directory ) {
        $href = "$name/";
    } else {
        $href = $name;
    }

    echo "<li><a href=\"", htmlspecialchars($href), "\">", htmlspecialchars($href), "</a></li>\n";
}

?>
</ul>

<?php $this->outputTemplate('page-footer',array()); ?>

<?php


?>
<?php $this->outputTemplate('page-header',array('pageTitle'=>$pageTitle)); ?>

<p>poopy doop</p>

<ul>
<?php

$entries = $resource->getEntries();

$sortedEntries = array();
ksort($entries);
foreach( $entries as $name=>$target ) if( $target instanceof EITCMS_Directory ) {
    $sortedEntries[$name] = $target;
}
foreach( $entries as $name=>$target ) if( !($target instanceof EITCMS_Directory) ) {
    $sortedEntries[$name] = $target;
}

foreach( $sortedEntries as $name=>$target ) {
    if( $target instanceof EITCMS_Directory ) {
        $href = "$name/";
    } else {
        $href = $name;
    }

    echo "<li><a href=\"", htmlspecialchars($href), "\">", htmlspecialchars($href), "</a></li>\n";
}

?>
</ul>

<p>poopy doop</p>

<?php $this->outputTemplate('page-footer',array()); ?>

<html>
<head>
<title><?php echo htmlspecialchars($pageTitle); if($siteTitle) echo ' - ', htmlspecialchars($siteTitle); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->htmlPathTo('resource:css/bogs.css'); ?>"/>
</head>
<body>

<?php

$links = array();
$showActionLinks = false;
foreach( $documentActions as $act ) {
    if( $act->getActionName() == $currentActionName ) {
        $links[] = htmlspecialchars($act->getActionTitle());
    } else {
        $showActionLinks = true;
        $links[] = "<a href=\"?".htmlspecialchars($act->getActionQueryString())."\">".htmlspecialchars($act->getActionTitle())."</a>";
    }
}

if( $showActionLinks ) {
    ?><div class="action-bar"><?php echo implode(' | ',$links); ?></div><?php
}

?>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

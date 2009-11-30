<html>
<head>
<title><?php echo htmlspecialchars($pageTitle); if($siteTitle and $siteTitle != $pageTitle) echo ' - ', htmlspecialchars($siteTitle); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->htmlPathTo('resource:css/bogs.css'); ?>"/>
</head>
<body>

<?php

$links = array();
$showActionLinks = false;
if( $documentActions !== null ) foreach( $documentActions as $act ) {
    if( $act->getActionName() == $currentActionName ) {
        $links[] = htmlspecialchars($act->getActionTitle());
    } else {
        $showActionLinks = true;
        $links[] = "<a href=\"?".htmlspecialchars($act->getActionQueryString())."\">".htmlspecialchars($act->getActionTitle())."</a>";
    }
}

?>

<div class="action-bar">
<?php
    if( $showLoginLinks ) {
        echo "<span style=\"float:right\">\n";
        if( $user ) {
            echo "Logged in as ", htmlspecialchars($user->getName()), ". &nbsp; ";
            echo "<a href=\"", $this->htmlPathTo('page:logout'), "\">Log out</a>";
        } else {
            echo "<a href=\"", $this->htmlPathTo('page:login'), "\">Log in</a>";
        }
        echo "</span>\n";
    }
    if( $showActionLinks ) {
        echo implode(' | ',$links);
    }
?>
<span style="clear:both">&nbsp;</span>
</div>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

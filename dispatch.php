<?php

ini_set('include_path',
        dirname(__FILE__).'/ext-lib'.PATH_SEPARATOR.
        dirname(__FILE__).'/lib'.PATH_SEPARATOR.
        ini_get('include_path'));

require_once 'Grubbo/debug-functions.php';

require_once 'Net/SMTP.php';
require_once 'Grubbo/Mail/Message.php';
require_once 'Grubbo/Mail/SmtpMailer.php';
require_once 'Grubbo/Mvc/Dispatcher.php';

$smtp = new Net_SMTP('mail.earthit.com',25,'grubbo.earthit.com');
#$smtp->setDebug(true);
try {
    $d = new Grubbo_Mvc_Dispatcher();
    $d->siteTitle = 'Grubbo';
    preg_match( '/(.*\/)dispatch\.php$/', $_SERVER['SCRIPT_NAME'], $bif );
    $d->siteUri = 'http://' . $_SERVER['SERVER_NAME'] . $bif[1];

    // Override any of these settings in site/config.php, if it exists
    if( file_exists('site/config.php') ) {
        $d->loadConfigFile('site/config.php');
    } else {
        $d->loadConfigFile('default-config.php');
    }

    $d->dispatch();
} catch( Exception $e ) {
    ez_print_exception( $e );
}
die();

?>
<html>
<head>
<title>Welcome to EITBugs!</title>
</head>
<body>

<h2>Welcome to EITBugs!</h2>

<?php echo $rp; ?>

</body>
</html>
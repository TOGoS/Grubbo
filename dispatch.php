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
    $d->siteTitle = 'EarthIT Grubbo';
    $d->siteUri = 'http://grubbo.earthit.com/';
#    $d->mailer = new Grubbo_Mail_SmtpMailer($smtp);
    $d->emailSourceDomain = 'grubbo.earthit.com';
    $d->docUpdateFromAddress = 'EarthIT Grubbo Updates <updates@grubbo.earthit.com>';
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
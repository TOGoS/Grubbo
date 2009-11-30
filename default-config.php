<?php

require_once 'Grubbo/Auth/EarthITUsers.php';
require_once 'Grubbo/Vcs/GitDocumentStore.php';
require_once 'Grubbo/Mvc/FormLoginController.php';

$userStore = new Grubbo_Auth_EarthITUsers();

$dispatcher->resourceStore = new Grubbo_Vcs_GitDocumentStore('site','site/.git','site/documents/','.edoc');
$dispatcher->userFunc = array($userStore,'getUserByUsername');
$dispatcher->siteTitle = "New Grubbo Site";
$dispatcher->loginController = new Grubbo_Mvc_FormLoginController( $dispatcher, $dispatcher->userFunc );

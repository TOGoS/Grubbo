<?php

require_once 'Grubbo/Mvc/LoginController.php';

class Grubbo_Mvc_HttpLoginController implements Grubbo_Mvc_LoginController {
	protected $useInternalLoginPage;
	protected $dispatcher;
	protected $userFunc;
	
	public function __construct( $dispatcher, $userFunc, $useInternalLoginPage=true ) {
		$this->dispatcher = $dispatcher;
		$this->userFunc = $userFunc;
		$this->useInternalLoginPage = $useInternalLoginPage;
	}

	protected function getUserByUsername( $username ) {
		if( $username === null ) return null;
		return call_user_func( $this->userFunc, $username );
	}

	public function requiresInternalLoginPage() {
		return $this->useInternalLoginPage;
	}

	public function handleLogin() {
		$this->dispatcher->startSession();
		$username = $_SERVER['PHP_AUTH_USER'];
		if( !$username ) {
			return $this->dispatcher->showErrorPage(
				"<p>Set up to use HTTP authentication, but no " .
				"PHP_AUTH_USER found.  Probably your web server's " .
				"not set up right, or you need to configure a " .
				"different authentication method.</p>" );
		}
		$user = $this->getUserByUsername( $username );
		$this->setLoggedInUser( $user );
	}

	public function handleLogout() {
		$this->setLoggedInUser(null);
	}

	public function getLoggedInUser() {
		return $this->getUserByUsername( @$_SESSION['username'] );
	}

	public function setLoggedInUser( $user ) {
		$this->dispatcher->startSession();
		$_SESSION['username'] = ($user === null) ? null : $user->getUsername();
	}
}

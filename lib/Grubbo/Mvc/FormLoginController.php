<?php

require_once 'Grubbo/Auth/PassHash.php';
require_once 'Grubbo/Mvc/LoginController.php';

class Grubbo_Mvc_FormLoginController implements Grubbo_Mvc_LoginController {
	protected $dispatcher;
	protected $userFunc;
	
	public function __construct( $dispatcher, $userFunc ) {
		$this->dispatcher = $dispatcher;
		$this->userFunc = $userFunc;
	}

	protected function getUserByUsername( $username ) {
		if( $username === null ) return null;
		return call_user_func( $this->userFunc, $username );
	}

	public function requiresInternalLoginPage() {
		return true;
	}

	public function handleLogin() {
		$this->dispatcher->startSession();
		$username = Grubbo_Util_ArrayUtil::coalesce($_REQUEST['username']);
		if( !$username ) {
			$this->dispatcher->showLoginPage();
			return false;
		}
		$user = $this->getUserByUsername( $username );
		$password = $_REQUEST['password'];
		if( $user === null ) {
			$this->dispatcher->showLoginPage(
				"<p>No such user as '$username'.</p>"
			);
			return false;
		}

		if( !Grubbo_Auth_PassHash::checkPassword( $password, $user->passwordHash ) ) {
			$newPasshash = Grubbo_Auth_PassHash::hashPassword( $password );
			$this->dispatcher->showLoginPage(
				"<p>Password entered is WRONG!</p>".
				"<p>To reset your password, give this hash to your admin: $newPasshash</p>"
			);
			return false;
		}

		$this->setLoggedInUser( $user );
		return true;
	}

	public function handleLogout() {
		$this->setLoggedInUser(null);
		return true;
	}

	public function getLoggedInUser() {
		return $this->getUserByUsername( @$_SESSION['username'] );
	}

	public function setLoggedInUser( $user ) {
		$this->dispatcher->startSession();
		$_SESSION['username'] = ($user === null) ? null : $user->getUsername();
	}
}

<?php

interface Grubbo_Mvc_LoginController {
	public function requiresInternalLoginPage();
	/**
	 * Return true if login has succedded and the dispatcher should
	 * redirect the user back to wherever they came from.
	 */
	public function handleLogin();
	public function handleLogout();
	public function setLoggedInUser( $user );
	public function getLoggedInUser();
}
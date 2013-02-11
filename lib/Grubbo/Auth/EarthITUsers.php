<?php

class Grubbo_Auth_EarthITUsers {
	protected $users;

	public function __construct() {
		$this->users = array(
			'stevens' => new Grubbo_Value_User( 'stevens', 'Dan Stevens', 'stevens@earthit.com' ),
			'fagan' => new Grubbo_Value_User( 'fagan', 'Pitt Fagan', 'fagan@earthit.com' ),
			'chapiewsky' => new Grubbo_Value_User( 'chapiewsky', 'Jared Chapiewsky', 'chapiewsky@earthit.com' ),
			'simcock' => new Grubbo_Value_User( 'simcock', 'Adam Simcock', 'simcock@earthit.com' ),
		);
		$this->users['stevens']->passwordHash    = 'fae8:97ca396e8cc94025657b740b798d2a21335d5d24';
		$this->users['chapiewsky']->passwordHash = 'bfd8:326e771f1ba62cb6f0592384ba2e4326b7a1288f';
		$this->users['fagan']->passwordHash      = '8c0b:4beee71d98beb1da2f627503303a3c6f0bf216af';
	}

	function getUserByUsername( $username ) {
		return Grubbo_Util_ArrayUtil::coalesce($this->users[$username]);
	}
}

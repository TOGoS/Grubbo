<?php

class Grubbo_Auth_EarthITUsers {
    protected $users;

    public function __construct() {
        $this->users = array(
            'stevens' => new Grubbo_Value_User( 'stevens', 'Dan Stevens', 'stevens@earthit.com' ),
            'fagan' => new Grubbo_Value_User( 'fagan', 'Pitt Fagan', 'fagan@earthit.com' ),
            'chapiewsky' => new Grubbo_Value_User( 'chapiewsky', 'Jared Chapiewsky', 'chapiewsky@earthit.com' ),
            'losenegger' => new Grubbo_Value_User( 'losenegger', 'Corey Losenegger', 'losenegger@earthit.com' ),
            'zeisloft' => new Grubbo_Value_User( 'zeisloft', 'Jennifer Zeisloft', 'zeisloft@earthit.com' ),
            'simcock' => new Grubbo_Value_User( 'simcock', 'Adam Simcock', 'simcock@earthit.com' ),
			'larner' => new Grubbo_Value_User( 'larner', 'Aaron Larner', 'larner@earthit.com' ),
        );
        $this->users['stevens']->passwordHash    = 'a433:a5dff4e7669e8bbc336c6996038a9cf1dcde5ede';
        $this->users['chapiewsky']->passwordHash = 'bfd8:326e771f1ba62cb6f0592384ba2e4326b7a1288f';
        $this->users['fagan']->passwordHash      = '8c0b:4beee71d98beb1da2f627503303a3c6f0bf216af';
        $this->users['zeisloft']->passwordHash   = '7d2e:81c3d7a02f3c89b9e0ad91506fda21d588c58d26';
		$this->users['larner']->passwordHash     = '9b69:49e2c38d4dc5f1b9cf90aec9a1a536900a56b51e';
    }

    function getUserByUsername( $username ) {
        return @$this->users[$username];
    }
}
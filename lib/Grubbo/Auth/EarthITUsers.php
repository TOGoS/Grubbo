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
        );
    }

    function getUserByUsername( $username ) {
        return @$this->users[$username];
    }
}
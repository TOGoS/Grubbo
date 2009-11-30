<?php

class Grubbo_Value_User {
    protected $username;
    protected $name;
    protected $emailAddress;

    public $passwordHash;

    public function __construct( $username, $name, $emailAddress ) {
        $this->username = $username;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmailAddress() {
        return $this->emailAddress;
    }
}

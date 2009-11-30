<?php

require_once 'Grubbo/Value/User.php';

class Grubbo_Store_DocumentUserStore {
    protected $documentStore;

    function __construct( Grubbo_Store_ResourceStore $documentStore ) {
        $this->documentStore = $documentStore;
    }

    function getUserByUsername( $username ) {
        $res = $this->documentStore->getResource( $username );
        if( $res === null ) return null;
        $md = $res->getContentMetadata();
        $user = new Grubbo_Value_User( $username, $md['doc/name'], $md['doc/email'] );
        $user->passwordHash = $md['doc/passhash'];
        return $user;
    }
}
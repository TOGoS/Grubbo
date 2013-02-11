<?php

/**
 * Represents what some user can and can't do.
 */
class Grubbo_Auth_Permissions {
    /**
     * @param array $permissions array of '*' => true (meaning user can do anything) or
     *   ..., verb => '*' (user can <verb> anything) or
     *   ..., verb => array( target1 => true, target2 => true, ... ) (user can <verb> <target1> and <target2>)
     *
     */
    function __construct( $permissions ) {
        $this->permissions = $permissions;
    }

    function isActionAllowed( $verb, $target ) {
        if( @$this->permissions['*'] ) return true;
        $perm =& $this->permissions[$verb];
        if( $perm == '*' or is_array($perm) && $perm[$target] ) return true;
        return false;
    }
}

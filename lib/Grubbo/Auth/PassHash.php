<?php

class Grubbo_Auth_PassHash {
    public static function hashPassword( $password, $salt=null ) {
        if( $salt === null ) $salt = sprintf('%04x',rand(0,65535));
        return $salt.':'.sha1($salt.$password);
    }

    public static function checkPassword( $password, $hash ) {
        list($salt,$_junk) = explode(':',$hash);
        return self::hashPassword($password,$salt) == $hash;
    }
}
<?php

interface Grubbo_Store_Store {
    public function get( $resName );
    public function put( $resName, $resource );
}

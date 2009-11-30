<?php

interface Grubbo_Store_ResourceStore {
    public function getResource( $resName );
    public function putResource( $resName, $resource );
}

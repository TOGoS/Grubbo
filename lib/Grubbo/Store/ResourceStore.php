<?php

interface Grubbo_Store_ResourceStore {
    /**
     * @return a Grubbo_Value_Resource object, or null if none exists by
     *   the given name in this ResourceStore
     */
    public function getResource( $resName );
    /**
     * @return true if the resource was stored, false if this ResourceStore
     *   is not responsible for the named resource.  Any other situation
     *   should throw an exception.
     */
    public function putResource( $resName, $resource );
}

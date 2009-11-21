<?php

require_once 'Grubbo/Value/Directory.php';
require_once 'Grubbo/Value/Resource.php';

/**
 * An implementation of Grubbo_Directory that depends on another object
 * and a path to get entries (useful to keep all the 'smarts' in one plce).
 */
class Grubbo_Store_StoreDirectory implements Grubbo_Value_Directory, Grubbo_Value_Resource {
    protected $store;
    protected $dirName;
    protected $contentMetadata;

    public function __construct( $store, $dirName, $contentMetadata ) {
        $this->store = $store;
        $this->dirName = $dirName;
        $this->contentMetadata = $contentMetadata;
    }

    public function getEntries() {
        return $this->store->getEntries( $this->dirName );
    }

    public function getContentMetadata() {
        return $this->contentMetadata;
    }
}

<?php

require_once 'Grubbo/Store/Store.php';
require_once 'Grubbo/Store/StoreDirectory.php';
require_once 'Grubbo/File/FileDocumentResource.php';

class Grubbo_File_FileDocumentStore implements Grubbo_Store_Store {
    protected $pathPrefix;
    protected $docPostfix;
    protected $backupStore;

    function __construct( $pathPrefix, $docPostfix='.edoc' ) {
        $this->pathPrefix = $pathPrefix;
        $this->docPostfix = $docPostfix;
    }

    protected function assertSaneDocId( $objId ) {
        if( preg_match('/^\/|\.[\/\.]|\/\/|[^a-zA-Z0-9\-\/\.]/',$objId,$bif) ) {
            throw new Exception("Object ID '$objId' contains invalid string: '".$bif[0]."'");
        }
    }

    protected function getFile( $fullPath ) {
        return new Grubbo_FileResource( $fullPath );
    }

    protected function getDoc( $fullPath ) {
        return new Grubbo_File_FileDocumentResource( $fullPath );
    }

    protected function getDir( $name ) {
        return new Grubbo_Store_StoreDirectory( $this, $name, array() );
    }

    public function get( $name ) {
        $fullPath = $this->pathPrefix.$name;
        if( is_dir( $fullPath ) ) {
            return $this->getDir( $name );
        } else if( file_exists( $fullPath ) ) {
            return $this->getFile( $fullPath );
        } else if( file_exists( $fullPath = $this->pathPrefix.$name.$this->docPostfix ) ) {
            return $this->getDoc( $fullPath );
        } else if( $this->backupStore ) {
            return $this->backupStore->get($name);
        } else {
            return null;
        }
    }

    public function getEntries( $dirName ) {
        $entries = array();
        $dirFullPath = $this->pathPrefix.$dirName;
        $dh = opendir( $dirFullPath );
        while( $e = readdir($dh) ) {
            if( $e{0} == '.' ) continue;
            $fp = $dirFullPath.'/'.$e;
            if( is_dir($fp) ) {
                $entries[$e] = $this->getDir($dirName.'/'.$e);
            } else if( substr($e,strlen($e)-strlen($this->docPostfix)) == $this->docPostfix ) {
                $entries[substr($e,0,strlen($e)-strlen($this->docPostfix))] = $this->getDoc($fp);
            } else {
                $entries[$e] = $this->getFile($dirFullPath.'/'.$e);
            }
        }
        closedir( $dh );
        return $entries;
    }

    public function _put( $name, $document ) {
        $path = $this->pathPrefix.$name;
        if( is_dir($path) ) throw new Exception("Can't put at a dir path: $name -> $path");
        $dir = dirname($path);
        if( $dir and !is_dir($dir)) mkdir( $dir, 0775, true );
        $fh = fopen( $path, "w" );
        fwrite( $fh, $document->getContent() );
        fclose( $fh );
    }

    public function put( $name, $resource ) {
        $docStr = "";
        $isDoc = false;
        $nonDocProps = array();
        if( $resource === null and file_exists($this->pathPrefix.$name.$this->docPostfix) ) {
            // Deleting a doc
            $isDoc = true;
        } else if( $resource !== null ) foreach( $resource->getContentMetadata() as $k=>$v ) {
            if( preg_match('/^doc\/(.*)$/',$k,$bif) ) {
                $isDoc = true;
                $docStr .= $bif[1].": $v\n";
            } else {
                $nonDocProps[$k] = $v;
            }
        }
        if( $isDoc ) {
            if( $resource === null ) {
                $fileDoc = null;
            } else {
                $docStr .= "\n";
                $docStr .= $resource->getContent();
                $fileDoc = new Grubbo_Value_StringResource( $docStr, $nonDocProps );
            }
            $filename = $name.$this->docPostfix;
        } else {
            $fileDoc = $resource;
            $filename = $name;
        }
        $this->_put( $filename, $fileDoc );
    }
}

<?php

require_once 'Grubbo/Store/ResourceStore.php';
require_once 'Grubbo/Vcs/Vcs.php';

class Grubbo_Store_PrefixMuxResourceStore implements Grubbo_Store_ResourceStore,
	Grubbo_Vcs_Vcs
{
	protected $subStores;
	
	public function __construct() {
		$this->subStores = array();
	}
	
	public function addSubStore( $prefix, $store ) {
		$this->subStores[$prefix] = $store;
	}
	
	protected function match( $resName, &$storePrefix, &$postPrefixName ) {
		$closestStore = null;
		$closestPrefix = null;
		
		foreach( $this->subStores as $prefix=>$store ) {
			if( $closestPrefix === null or strlen($prefix) > strlen($closestPrefix) ) {
				if( substr($resName,0,strlen($prefix)) == $prefix ) {
					$closestPrefix = $prefix;
					$closestStore = $store;
				}
			}
		}
		
		if( $closestPrefix !== null ) {
			$storePrefix = $closestPrefix;
			$postPrefixName = substr($resName,strlen($storePrefix));
		} else {
			$storePrefix = null;
			$postPrefixName = null;
		}
		
		return $closestStore;
	}
	
	//// ResourceStore methods ////
	
	public function getResource( $resName ) {
		$store = $this->match( $resName, $prefix, $postfix );
		if( $store === null ) return null;
		return $store->getResource( $postfix );
	}
	
	public function putResource( $resName, $resource ) {
		$store = $this->match( $resName, $prefix, $postfix );
		if( $store === null ) return false;
		return $store->putResource( $postfix, $resource );
	}
	
	//// Vcs methods ////

	protected function matchTransactable( $path, &$prefix, &$postfix ) {
		$store = $this->match( $resName, $prefix, $postfix );
		if( !($store instanceof Grubbo_Vcs_Transactable) ) {
			throw new Exception("No Transactable at '$path'");
		}
		return $store;
	}

	public function openTransaction( $resName ) {
		$store = $this->matchTransactable( $resName, $prefix, $postfix );
		$store->openTransaction( $postfix );
	}

	public function closeTransaction( $resName ) {
		$store = $this->matchTransactable( $resName, $prefix, $postfix );
		$store->closeTransaction( $postfix );
	}

	public function cancelTransaction( $resName ) {
		$store = $this->matchTransactable( $resName, $prefix, $postfix );
		$store->cancelTransaction( $postfix );
	}

	public function commit( $resName, Grubbo_Vcs_CommitInfo $commitInfo ) {
		$store = $this->match( $resName, $prefix, $postfix );
		if( !($store instanceof Grubbo_Vcs_Committable) ) {
			throw new Exception("No Committable at '$resName'");
		}
		$store->commit( $postfix, $commitInfo );
	}    
}
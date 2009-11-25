<?php

class Grubbo_Ticket_TicketFilter {
    public $assignedTo;
    public $status;
    public $milestone;
    public $module;

    protected function parseSet( $i ) {
        if( is_string($i) ) $i = explode(',',$i);
        $set = array();
        foreach( $i as $v ) {
            $v = strtolower(trim($v));
            $set[$v] = $v;
        }
        return $set;
    }

    protected function inList( $needleStr, $haystackStr ) {
        if( !$needleStr ) return true;
        if( !$haystackStr ) return false;

        $needles = $this->parseSet($needleStr);
        $haystack = $this->parseSet($haystackStr);

        foreach( $needles as $needle ) {
            if( $haystack[$needle] ) return true;
        }
        return false;
    }

    public function filter( $entry ) {
        $md = $entry->getContentMetadata();
        if( !$this->inList( $this->assignedTo, $md['doc/assigned-to'] ) ) return null;
        if( !$this->inList( $this->status,     $md['doc/status']      ) ) return null;
        if( !$this->inList( $this->milestone,  $md['doc/milestone']   ) ) return null;
        if( !$this->inList( $this->module,     $md['doc/module']      ) ) return null;
        return $entry;
    }
}
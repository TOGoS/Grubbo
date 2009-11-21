<?php

require_once 'Grubbo/File/FileDocumentStore.php';
require_once 'Grubbo/Vcs/Committable.php';
require_once 'Grubbo/Vcs/Transactable.php';
require_once 'Grubbo/Proc/ExternalProcess.php';

class Grubbo_Vcs_GitDocumentStore extends Grubbo_File_FileDocumentStore implements Grubbo_Vcs_Committable, Grubbo_Vcs_Transactable {
    protected $wtDir;
    protected $gitDir;
    protected $postWtPrefix;
    protected $lock;

    function __construct( $wtDir, $gitDir, $pathPrefix, $docPostfix='.edoc' ) {
        if( substr($pathPrefix,0,strlen($wtDir)) != $wtDir ) {
            throw new Exception("File prefix must begin with working tree dir ('$wtDir'), but did not: '$pathPrefix'");
        }
        parent::__construct( $pathPrefix, $docPostfix );
        $this->postWtPrefix = substr($pathPrefix,strlen($wtDir)+1);
        $this->wtDir = $wtDir;
        $this->gitDir = $gitDir;
        $this->gitBaseArgv = array('git');
    }

    protected function getGitArgv( $subCmdArgv ) {
        return array_merge(
            $this->gitBaseArgv,
            /* Specifying git-dir and work-tree doesn't actually work.
             * We need to chdir instead (see git(), below). */
            // array('--git-dir='.$this->gitDir,'--work-tree='.$this->wtDir),
            $subCmdArgv
        );
    }

    /**
     * Use this to clean out index.lock after every git invocation,
     * since git often fails to and will cause problems if it's still
     * around when we run it again.
     */
    protected function cleanUpIndexLock() {
        $gitIndexLock = "{$this->gitDir}/index.lock";
        if( file_exists($gitIndexLock) ) unlink($gitIndexLock);
    }

    public function git() {
        $oldDir = getcwd();
        chdir( $this->wtDir );
        $args = $this->getGitArgv( func_get_args() );
        $proc = new Grubbo_Proc_ExternalProcess($args);
        try {
            $r = $proc->runOrDie();
            $this->cleanUpIndexLock();
            chdir( $oldDir );
            return $r;
        } catch( Exception $e ) {
            $this->cleanUpIndexLock();
            chdir( $oldDir );
            throw $e;
        }
    }

    public function openTransaction() {
        if( $this->lock !== null ) {
            throw new Exception("Lock already aquired!");
        }
        if( file_exists($lockFile = $this->wtDir."/.lock") ) {
            $this->lock = fopen( $lockFile, "r" );
            if( $this->lock === false ) {
                throw new Exception("Failed to open lock file in 'r' mode: $lockFile");
            }
        } else {
            $this->lock = fopen( $lockFile, "w" );
            if( $this->lock === false ) {
                throw new Exception("Failed to create lock file: $lockFile");
            }
        }
    }

    public function _put( $name, $res ) {
        if( $res === null ) {
            // DELETED!!!
            $this->git('rm',$this->postWtPrefix.$name);
        } else {
            parent::_put( $name, $res );
            $this->git('add',$this->postWtPrefix.$name);
        }
    }

    public function commit( Grubbo_Vcs_CommitInfo $commitInfo ) {
        $this->git('commit',
                   '-m',$commitInfo->getDescription(),
                   '--author='.$commitInfo->getAuthor()->getName().' <'.$commitInfo->getAuthor()->getEmailAddress().'>');
    }

    public function cancelTransaction() {
        $this->closeTransaction();
    }

    public function closeTransaction() {
        fclose( $this->lock ); // Release lock!
        $this->lock = null;
    }
}
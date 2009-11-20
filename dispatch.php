<?php

function ezecho() {
    echo "<pre>";
    foreach( func_get_args() as $v ) {
        echo htmlspecialchars(print_r($v,true)), "\n\n";
    }
    echo "</pre>\n";
}

function ez_format_stacktrace( Exception $e ) {
    $r = '';
    $r .= "<pre>";
    $r .= $e->getTraceAsString() . "\n";
    $r .= "</pre>\n";
    return $r;
}

/** Prints only the stacktrace of an exception */
function ez_print_stacktrace( Exception $e ) {
    echo ez_format_stacktrace($e);
}

function ezdie() {
    $q = func_get_args();
    call_user_func_array( 'ezecho', $q );

    try {
        throw new Exception("");
    } catch( Exception $e ) {
        echo "<hr />\n";
        ez_print_stacktrace( $e );
    }
    die();
}

function ez_format_exception( Exception $e ) {
    $r = '';
    if( $e instanceof global_util_ExceptionWithExtraInfo ) {
        $r .= "<p style=\"font-weight: bold\">" . htmlspecialchars($e->getMessage()) . "</p>";
        $r .= call_user_func_array('ezformat', $e->getExtraInfos());
        $r .= ez_format_stacktrace( $e );
    } else {
        $r .= "<p style=\"font-weight: bold\">" . htmlspecialchars($e->getMessage()) . "</p>";
        $r .= ez_format_stacktrace( $e );
    }
    return $r;
}

function ez_print_exception( Exception $e ) {
    echo ez_format_exception( $e );
}





////

interface EITCMS_Blob {
    function getContent();
    function writeContent($stream);
}

interface EITCMS_Directory {
    /** @return an array of name => resource or directory */
    function getEntries();
}

interface EITCMS_Resource {
    function getContentMetadata();
}

interface EITCMS_Response extends EITCMS_Resource {
    function getStatus();
    function getMetadata();
}

class EITCMS_StringResource implements EITCMS_Resource, EITCMS_Blob {
    protected $content;
    protected $contentMetadata;

    public function __construct( $content, $contentMetadata ) {
        $this->content = $content;
        $this->contentMetadata = $contentMetadata;
    }

    public function getContentMetadata() {
        return $this->contentMetadata;
    }

    public function getContent() {
        return $this->content;
    }

    public function writeContent($stream) {
        fwrite( $stream, $this->getContent() );
    }
}

/**
 * An implementation of EITCMS_Directory that depends on another object
 * and a path to get entries (useful to keep all the 'smarts' in one plce).
 */
class EITCMS_StoreDirectory implements EITCMS_Directory, EITCMS_Resource {
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

class EITCMS_FileResource implements EITCMS_Resource, EITCMS_Blob {
    protected $file;
    protected $contentMetadata;

    public function __construct( $file, $contentMetadata=array() ) {
        $this->file = $file;
        $this->contentMetadata = $contentMetadata;
    }

    public function getContentMetadata() {
        return $this->contentMetadata;
    }

    public function getContent() {
        return file_get_contents( $this->file );
    }

    public function writeContent($stream) {
        fwrite( $stream, $this->getContent() );
    }
}

class EITCMS_FileDocumentResource implements EITCMS_Resource, EITCMS_Blob {
    protected $file;
    protected $contentMetadata;
    protected $content;
    protected $loaded;

    public function __construct( $file ) {
        $this->file = $file;
    }

    protected function load() {
        if( $this->loaded ) return;

        $this->contentMetadata = array();

        $fh = fopen( $this->file, 'r' );
        while( $l = trim(fgets($fh)) ) {
            if( preg_match( '/^(.*?):\s+(.*)$/', $l, $bif ) ) {
                $this->contentMetadata['doc/'.$bif[1]] = $bif[2];
            }
        }
        $this->content = stream_get_contents( $fh );
        fclose( $fh );
    }

    public function getContentMetadata() {
        $this->load();
        return $this->contentMetadata;
    }

    public function getContent() {
        $this->load();
        return $this->content;
    }

    public function writeContent( $stream ) {
        fwrite( $stream, $this->getContent() );
    }
}

interface EITCMS_Store {
    public function get( $resName );
    public function put( $resName, $resource );
}

interface EITCMS_Committable {
    public function commit( EITCMS_CommitInfo $commitInfo );
}

interface EITCMS_Transactable {
    public function openTransaction();
    public function closeTransaction();
    public function cancelTransaction();
}

interface EITCMS_VCS extends EITCMS_Store, EITCMS_Transactable, EITCMS_Committable { }

class EITCMS_FileDocumentStore implements EITCMS_Store {
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
        return new EITCMS_FileResource( $fullPath );
    }

    protected function getDoc( $fullPath ) {
        return new EITCMS_FileDocumentResource( $fullPath );
    }

    protected function getDir( $name ) {
        return new EITCMS_StoreDirectory( $this, $name, array() );
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
                $fileDoc = new EITCMS_StringResource( $docStr, $nonDocProps );
            }
            $filename = $name.$this->docPostfix;
        } else {
            $fileDoc = $resource;
            $filename = $name;
        }
        $this->_put( $filename, $fileDoc );
    }
}

class EITCMS_User {
    protected $username;
    protected $name;
    protected $emailAddress;

    public function __construct( $username, $name, $emailAddress ) {
        $this->username = $username;
        $this->name = $name;
        $this->emailAddress = $emailAddress;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmailAddress() {
        return $this->emailAddress;
    }
}

class EITCMS_CommitInfo {
    protected $author;
    protected $date;
    protected $description;

    public function __construct( EITCMS_User $author, $date, $description ) {
        $this->author = $author;
        $this->date = $date;
        $this->description = $description;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getDate() {
        return $this->date;
    }

    public function getDescription() {
        return $this->description;
    }
}

class EITCMS_ExternalProcess {
    protected $argv;

    public function __construct() {
        $args = func_get_args();
        if( is_array($args[0]) ) $args = $args[0];
        $this->argv = $args;
    }

    protected function getShellString() {
        $argvesc = array();
        foreach( $this->argv as $a ) {
            $argvesc[] = escapeshellarg($a);
        }
        return implode(' ',$argvesc);
    }

    public function run() {
        system( $this->getShellString(), $ret );
        return $ret;
    }

    public function runOrDie() {
        $cmd = $this->getShellString();
        exec( "$cmd 2>&1", $output, $ret );
        if( $ret ) {
            throw new Exception("Process returned error code $ret: $cmd: ".implode("\n",$output));
        }
    }
}

class EITCMS_GitStore extends EITCMS_FileDocumentStore implements EITCMS_Committable, EITCMS_Transactable {
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
        $proc = new EITCMS_ExternalProcess($args);
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

    public function commit( EITCMS_CommitInfo $commitInfo ) {
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

/**
 * Represents what the logged-in user can and can't do.
 */
class EITCMS_Permissions {
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
        $perm = $this->permissions[$verb];
        if( $perm == '*' or is_array($perm) && $perm[$target] ) return true;
        return false;
    }
}

class EITCMS_Template {
    protected $dispatcher;
    protected $tplDir;
    protected $name;
    protected $_args;

    function __construct( $dispatcher, $tplDir, $name ) {
        $this->dispatcher = $dispatcher;
        $this->tplDir = $tplDir;
        $this->name = $name;
        $this->_args = array();
    }

    function getTemplate( $name ) {
        return new EITCMS_Template( $this->dispatcher, $this->tplDir, $name );
    }

    function outputTemplate( $tplName, $args=array() ) {
        $this->getTemplate($tplName)->output(array_merge($args,$this->_args));
    }

    function formatDropdown( $name, $values, $default ) {
        $str = "<select name=\"".htmlspecialchars($name)."\">\n";
        foreach( $values as $k=>$v ) {
            $str .= "<option value=\"".htmlspecialchars($v)."\"";
            if( $default == $k ) {
                $str .= " selected";
            }
            $str .= ">".htmlspecialchars($v)."</option>\n";
        }
        $str .= "</select>";
        return $str;
    }

    function pathTo( $uri ) {
        return $this->dispatcher->pathTo($uri);
    }

    function htmlPathTo( $uri ) {
        return htmlspecialchars($this->pathTo($uri));
    }

    function output( $__args ) {
        $__oldArgs = $this->_args;
        $this->_args = $__args;
        $__tplFile = $this->tplDir.'/'.$this->name.'.php';
        if( !file_exists($__tplFile) ) {
            throw new Exception("Template '$__tplFile' does not exist");
        }
        extract( $__args, EXTR_SKIP );
        include $__tplFile;
        $this->_args = $__oldArgs;
    }
}

class EITCMS_ResourceAction {
    protected $name;
    protected $title;
    protected $qsArgs;

    public function __construct( $name, $title, $qsArgs=null ) {
        $this->name = $name;
        $this->title = $title;
        if( $qsArgs === null ) {
            $qsArgs = array('action'=>$name);
        }
        $this->qsArgs = $qsArgs;
    }
    public function getActionName() {
        return $this->name;
    }
    public function getActionTitle() {
        return $this->title;
    }
    public function getActionQueryString() {
        $ss = array();
        foreach( $this->qsArgs as $k=>$v ) {
            $ss[] = urlencode($k).'='.urlencode($v);
        }
        return implode('&',$ss);
    }
}

class EITCMS_Dispatcher {
    function __construct() {
        $this->resourceStore = new EITCMS_GitStore('site','site/.git','site/documents/','.edoc');
        $this->templateDir = 'themes/default/templates';
    }

    function getTemplate( $name ) {
        return new EITCMS_Template( $this, $this->templateDir, $name );
    }

    function textParagraphsToHtml( $html ) {
        if( $html == '' ) return '';
        $html = str_replace("\n","<br />\n",$html);
        $html = str_replace("<br />\n<br />\n","</p>\n\n<p>",$html);
        $html = "<p>$html</p>";
        return $html;
    }

    function replaceWikiLink( $match ) {
        $uri = $match[1];
        $linkText = $match[2];
        $normalText = $match[3];
        if( $normalText ) {
            return htmlspecialchars($normalText);
        } else {
            if( !$linkText ) $linkText = $uri;
            return "<a href=\"".htmlspecialchars($this->pathTo($uri))."\">".htmlspecialchars($linkText)."</a>";
        }
    }

    function openWikiTextState( $state, &$html ) {
        if( $state == 'p' ) {
            $html .= "<p>";
        } else if( $state == 'li' ) {
            $html .= "<ul>\n";
        }
    }

    function closeWikiTextState( $state, &$html ) {
        if( $state == 'p' ) {
            $html .= "</p>\n\n";
        } else if( $state == 'li' ) {
            $html .= "</ul>\n\n";
        }
    }

    function formatWikiText( $wiki ) {
        $fixedLinks = preg_replace_callback( '/\[([a-z]+:\S+)(?:\s([^\]]+))?\]|([^\[]*|\[)/', array($this,'replaceWikiLink'), $wiki );

        // TODO: get a real wikitext formatter

        $state = null;
        $html = '';

        $lines = explode( "\n", $fixedLinks );
        foreach( $lines as $line ) {
            $line = trim($line);
            if( preg_match( '/^\* (.*)$/', $line, $bif ) ) {
                $newState = 'li';
                $line = $bif[1];
            } else if( $line == '' ) {
                $newState = null;
            } else {
                $newState = 'p';
            }

            if( $state != $newState ) {
                $this->closeWikiTextState( $state, $html );
                $this->openWikiTextState( $newState, $html );
            }
            $state = $newState;

            if( $state == 'li' ) {
                $html .= "<li>".$line."</li>\n";
            } else {
                $html .= $line;
            }
        }
        $this->closeWikiTextState( $state, $html );
        return $html;
    }

    function formatDocumentBody( $res ) {
        if( $res === null ) return null;

        $md = $res->getContentMetadata();
        $fmt = $md['doc/format'];
        if( $fmt == 'wiki' ) {
            $content = $res->getContent();
            $content = $this->formatWikiText( $content );
            $md['doc/format'] = 'html';
            return new EITCMS_StringResource( $content, $md );            
        } else if( $fmt == 'text' ) {
            $content = $res->getContent();
            $content = htmlspecialchars($content);
            $content = $this->textParagraphsToHtml($content);
            $md['doc/format'] = 'html';
            return new EITCMS_StringResource( $content, $md );
        }
        return $res;
    }

    function getUser( $username ) {
        $users = array(
            'stevens' => new EITCMS_User( 'stevens', 'Dan Stevens', 'stevens@earthit.com' ),
            'fagan' => new EITCMS_User( 'fagan', 'Pitt Fagan', 'fagan@earthit.com' ),
            'chapiewsky' => new EITCMS_User( 'chapiewsky', 'Jared Chapiewsky', 'chapiewsky@earthit.com' ),
            'losenegger' => new EITCMS_User( 'losenegger', 'Corey Losenegger', 'losenegger@earthit.com' ),
            'zeisloft' => new EITCMS_User( 'zeisloft', 'Jennifer Zeisloft', 'zeisloft@earthit.com' ),
            'simcock' => new EITCMS_User( 'simcock', 'Adam Simcock', 'simcock@earthit.com' ),
        );
        return @$users[$username];
    }

    function getLoggedInUser() {
        if( !$this->user ) {
            $this->startSession();
            $username = $_SESSION['username'];
            if( $username ) {
                $this->user = $this->getUser($username);
            } else {
                $this->user = null;
            }
        }
        return $this->user;
    }

    function getCurrentDate() {
        return date('Y-m-d H:i:s');
    }

    function getPermissions() {
        if( !$this->permissions ) {
            $this->permissions = new EITCMS_Permissions( array('*'=>'*') );
        }
        return $this->permissions;
    }

    function getPossibleActions( $resourceName, $resource ) {
        if( $resource === null ) return array();
        
        $actions = array();
        $actions[] = new EITCMS_ResourceAction('view', 'View', array());
        if( $resource instanceof EITCMS_Blob ) {
            $actions[] = new EITCMS_ResourceAction('edit', 'Edit');
        }
        return $actions;
    }

    function getAllowedActions( $resourceName, $resource ) {
        $p = $this->getPermissions();
        $actions = array();
        foreach( $this->getPossibleActions( $resourceName, $resource ) as $act ) {
            if( $p->isActionAllowed($act->getActionName(),$resourceName) ) {
                $actions[] = $act;
            }
        }
        return $actions;
    }

    function resourceIsDocument( $resource ) {
        $md = $resource->getContentMetadata();        
        foreach( $md as $k=>$v ) {
            if( strpos($k,'doc/') == 0 ) return true;
        }
        return false;
    }
    
    function outputResource( $resource, $tplVars ) {
        if( $resource === null ) {
            header('HTTP/1.0 404 Not Found');
            $this->getTemplate( 'not-found' )->output( $tplVars );
        } else if( $this->resourceIsDocument($resource) ) {
            $resource = $this->formatDocumentBody( $resource );
            $tplVars['resource'] = $resource;
            $this->getTemplate( 'view-document' )->output( $tplVars );
        } else if( $resource instanceof EITCMS_Directory ) {
            $tplVars['resource'] = $resource;
            $rmd = $resource->getContentMetadata();
            if( !$title ) { $title = @$rmd['title']; }
            if( !$title ) { $title = $tplVars['resourceName'];  }
            $tplVars['pageTitle'] = "Index of $title";
            $this->getTemplate( 'view-directory' )->output( $tplVars);
        } else if( $resoruce instanceof EITCMS_Blob ) {
            $md = $resource->getContentMetadata();
            if( $ct = @$md['type'] ) header('content-type: '.$ct);
            if( $mt = @$md['last-modified'] ) {
                if( !is_numeric($mt) ) {  $mt = strtotime($mt);  }
                header('last-modified: '.gmdate('D, d M Y H:i:s T', $mt));
            }
            $resource->writeContent(fopen('php://output','w'));
        }
    }
    
    function replaceSigText( $text ) {
        $sig = $this->getLoggedInUser()->getName().', '.$this->getCurrentDate();
        return str_replace( '~~~~', $sig, $text );
    }

    function getDocumentFromRequest() {
        $cmd = array();
        foreach( $_REQUEST as $k=>$v ) {
            if( preg_match('/^doc\/(.*)$/',$k,$bif) ) {
                $cmd["doc/".$bif[1]] = $v;
            }
        }
        $content = $_REQUEST['content'];
        $content = $this->replaceSigText( $content );
        return new EITCMS_StringResource( $content, $cmd );
    }

    function pathTo( $uri ) {
        if( preg_match('/^page:(.*)$/',$uri,$bif) ) {
            $absWebPath = $bif[1];
        } else if( preg_match('/^resource:(.*)$/',$uri,$bif) ) {
            $absWebPath = 'resources/'.$bif[1];
        } else if( preg_match('/^http:/',$uri) ) {
            return $uri;
        }

        // Assume PATH_INFO of format '/(.+/)*.*'

        $slashCount = substr_count( $_SERVER['PATH_INFO'], '/' );
        if( $slashCount == 1 ) {
            return "./".$absWebPath;
        } else {
            return str_repeat('../', $slashCount-1).$absWebPath;
        }
    }

    function redirectSeeOther( $url ) {
        header("HTTP/1.0 302 Redirect See Other");
        header("Location: $url");
        exit();
    }

    function ensureEditPermission( $resourceName=null ) {
        if( $resourceName === null ) $resouceName = $this->resourceName;

        if( !$this->getPermissions()->isActionAllowed('edit',$this->resourceName) ) {
            $tplVars['errorMessageHtml'] = "</p>You cannot edit this page!</p>";
            $this->getTemplate('not-allowed')->output($tplVars);
            exit();
        }
    }

    function createBlankTicket() {
        $metadata = array(
            'doc/format' => 'wiki',
            'doc/ticket' => 'true',
            'doc/title' => 'New Ticket',
        );
        $content = "Enter text here";
        return new EITCMS_StringResource( $content, $metadata );
    }

    function createBlankDocument() {
        $metadata = array(
            'doc/format' => 'wiki',
            'doc/title' => 'New Page',
        );
        $content = "Enter text here";
        return new EITCMS_StringResource( $content, $metadata );
    }

    protected $sessionStarted;
    function startSession() {
        if( !$this->sessionStarted ) {
            session_name('Grubbo');
            session_start();
        }
    }

    function dispatch() {
        $rp = substr($_SERVER['PATH_INFO'],1);
        $an = @$_REQUEST['action'] or $an = 'view';
        $user = $this->getLoggedInUser();
        $date = $this->getCurrentDate();

        // If we're at a ../ URL, find an index page if one exists
        if( preg_match('/^$|\/$/',$rp) and $resource = $this->resourceStore->get($rp.'index') ) {
            $rp = $rp.'index';
        } else {
            $resource = $this->resourceStore->get($rp);
        }

        $this->resourceName = $rp;
        $this->resource = $resource;
        $this->currentActionName = $an;

        $tplVars = array(
            'user' => $user,
            'resourceName' => $this->resourceName,
            'currentActionName' => $this->currentActionName,
            'documentActions' => array(),
            'ticketStatusOptions' => array(
                'assigned' => 'Assigned',
                'development' => 'Development',
                'waiting' => 'Waiting',
                'testing' => 'Testing',
                'closed' => 'Closed',
            ),
        );

        if( $this->resource !== null ) {
            $resourceMetadata = $this->resource->getContentMetadata();
            $docTitle = @$resourceMetadata['title'] or
            $docTitle = @$resourceMetadata['doc/title'];
        } else {
            $resourceMetadata = array();
            $docTitle = null;
        }

        $p = $this->getPermissions();

        if( $this->resource === null ) {
            if( preg_match('/^(.*)\/new-ticket$/',$this->resourceName,$bif) ) {
                $ticketDirName = $bif[1];
                $this->ensureEditPermission();
                $tplVars['pageTitle'] = "Creating new ticket";
                if( $an == 'post' ) {
                    $doc = $this->getDocumentFromRequest();
                    $this->resourceStore->openTransaction();
                    try {
                        $ticketDir = $this->resourceStore->get( $ticketDirName );
                        $ticketList = ($ticketDir === null) ? array() : $ticketDir->getEntries();
                        $highest = 1;
                        foreach( $ticketList as $name=>$_uhm ) {
                            if( preg_match('/^(\d+).*/',$name,$bif) ) {
                                if( $bif[1] > $highest ) {
                                    $highest = $bif[1];
                                }
                            }
                        }
                        ++$highest;
                        $newResourceName = "$ticketDirName/$highest";
                        $metadata = $doc->getContentMetadata();
                        $title = $metadata['doc/title'];
                        $commitInfo = new EITCMS_CommitInfo( $user, $date, "New ticket $newResourceName" . ($title ? " - $title" : ''));
                        $this->resourceStore->put( $newResourceName, $doc );
                        $this->resourceStore->commit( $commitInfo );
                        $this->resourceStore->closeTransaction();
                    } catch( Exception $e ) {
                        $this->resourceStore->cancelTransaction();
                        throw $e;
                    }
                    $this->redirectSeeOther( $this->pathTo("page:$newResourceName") );
                } else {
                    $tplVars['newPage'] = true;
                    $tplVars['resource'] = $this->createBlankTicket();
                    $tplVars['projectDirName'] = $projectDirName;
                    $this->getTemplate('new-ticket')->output($tplVars);
                    return;
                }
            } else if( $rp == 'login' ) {
                $this->startSession();
                $username = $_SERVER['PHP_AUTH_USER'];
                $_SESSION['username'] = $username;
                $this->redirectSeeOther( $this->pathTo('page:') );
            } else if( $rp == 'logout' ) {
                $this->startSession();
                $_SESSION['username'] = null;
                $this->redirectSeeOther( $this->pathTo('page:') );
            }
        }

        if( preg_match('/^(.*)\/tickets\/(\d+)$/',$this->resourceName,$bif ) ) {
            if( $resource ) {
                $rmd = $this->resource->getContentMetadata();
                $tplVars['pageTitle'] = $bif[1].' #'.$bif[2].': '.$rmd['doc/title'];
            }
        }

        $tplVars['documentActions'] = $this->getAllowedActions( $this->resourceName, $this->resource );
        $tplVars['resource'] = $this->resource;

        $pageIsNew = ($this->resource === null);

        if( $an == 'edit' and $this->resource !== null ) {
            $this->ensureEditPermission();
            $tplVars['pageTitle'] = "Editing $docTitle";
            $this->getTemplate('edit-page')->output($tplVars);
        } else if( $an == 'post' and $_REQUEST['delete'] ) {
            $this->ensureEditPermission();
            if( $this->resource ) {
                $metadata = $this->resource->getContentMetadata();
                $docTitle = $metadata['doc/title'];
            } else {
                $docTitle = null;
            }
            $commitTitle = "Deleted ".$this->resourceName.($title ? " - $docTitle" : '');
            $commitInfo = new EITCMS_CommitInfo( $user, $date, $commitTitle );
            $this->resourceStore->openTransaction();
            try {
                $this->resourceStore->put( $this->resourceName, null );
                $this->resourceStore->commit( $commitInfo );
                $this->resourceStore->closeTransaction();
            } catch( Exception $e ) {
                $this->resourceStore->cancelTransaction();
                throw $e;
            }
            $url = $this->pathTo('page:');
            $this->redirectSeeOther( $url );
        } else if( $an == 'post' ) {
            $this->ensureEditPermission();
            $doc = $this->getDocumentFromRequest();
            $metadata = $doc->getContentMetadata();
            $docTitle = $metadata['doc/title'];
            $commitTitle = ($pageIsNew ? "New page" : "Edited").": ".$this->resourceName.($title ? " - $docTitle" : '');
            $commitInfo = new EITCMS_CommitInfo( $user, $date, $commitTitle );
            $this->resourceStore->openTransaction();
            try {
                $this->resourceStore->put( $this->resourceName, $doc );
                $this->resourceStore->commit( $commitInfo );
                $this->resourceStore->closeTransaction();
            } catch( Exception $e ) {
                $this->resourceStore->cancelTransaction();
                throw $e;
            }
            $this->redirectSeeOther( $this->pathTo("page:".$this->resourceName) );
        } else if( $this->resource === null ) {
            $tplVars['pageTitle'] = "Create new page";
            $tplVars['newPage'] = true;
            $tplVars['resource'] = $this->createBlankDocument();
            $this->getTemplate('new-page')->output($tplVars);
        } else {
            if( !$tplVars['pageTitle'] ) { $tplVars['pageTitle'] = $docTitle; }
            $this->outputResource( $this->resource, $tplVars );
        }
    }
}

try {
    $d = new EITCMS_Dispatcher();
    $d->dispatch();
} catch( Exception $e ) {
    ez_print_exception( $e );
}
die();

?>
<html>
<head>
<title>Welcome to EITBugs!</title>
</head>
<body>

<h2>Welcome to EITBugs!</h2>

<?php echo $rp; ?>

</body>
</html>
<?php

require_once 'Grubbo/Auth/Permissions.php';
require_once 'Grubbo/Mvc/ResourceAction.php';
require_once 'Grubbo/Mvc/Template.php';
require_once 'Grubbo/Value/StringResource.php';
require_once 'Grubbo/Value/User.php';
require_once 'Grubbo/Vcs/CommitInfo.php';
require_once 'Grubbo/Vcs/GitDocumentStore.php';

class Grubbo_Mvc_Dispatcher {
    public $resourceStore;

    public $siteTitle = 'Grubbo Demo';
    public $siteUri = 'http://grubbo.x/';    

    public $mailer = null;
    public $emailSourceDomain = 'grubbo.x';
    public $docUpdateFromAddress = 'updates@grubbo.x';

    function __construct() {
        $this->resourceStore = new Grubbo_Vcs_GitDocumentStore('site','site/.git','site/documents/','.edoc');

        // TODO: Replace filesystem template dir path with same
        // virtual directory structure used for documents...this
        // should be relatively easy.
        $this->templateDir = 'themes/default/templates';
    }

    function getTemplate( $name ) {
        return new Grubbo_Mvc_Template( $this, $this->templateDir, $name );
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
        if( $state == 'bq' ) {
            $html .= "<blockquote><pre>";
        } else if( $state == 'p' ) {
            $html .= "<p>";
        } else if( $state == 'li' ) {
            $html .= "<ul>\n";
        }
    }

    function closeWikiTextState( $state, &$html ) {
        if( $state == 'bq' ) {
            $html .= "</pre></blockquote>\n\n";
        } else if( $state == 'p' ) {
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
            $line = rtrim($line);
            if( preg_match( '/^\* (.*)$/', $line, $bif ) ) {
                $newState = 'li';
                $line = $bif[1];
            } else if( preg_match( '/^\s+(.*)$/', $line, $bif ) ) {
                $newState = 'bq';
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
            if( $state == $newState and $state == 'p' ) {
                $html .= "<br />";
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
            return new Grubbo_Value_StringResource( $content, $md );            
        } else if( $fmt == 'text' ) {
            $content = $res->getContent();
            $content = htmlspecialchars($content);
            $content = $this->textParagraphsToHtml($content);
            $md['doc/format'] = 'html';
            return new Grubbo_Value_StringResource( $content, $md );
        }
        return $res;
    }

    function getUser( $username ) {
        $users = array(
            'stevens' => new Grubbo_Value_User( 'stevens', 'Dan Stevens', 'stevens@earthit.com' ),
            'fagan' => new Grubbo_Value_User( 'fagan', 'Pitt Fagan', 'fagan@earthit.com' ),
            'chapiewsky' => new Grubbo_Value_User( 'chapiewsky', 'Jared Chapiewsky', 'chapiewsky@earthit.com' ),
            'losenegger' => new Grubbo_Value_User( 'losenegger', 'Corey Losenegger', 'losenegger@earthit.com' ),
            'zeisloft' => new Grubbo_Value_User( 'zeisloft', 'Jennifer Zeisloft', 'zeisloft@earthit.com' ),
            'simcock' => new Grubbo_Value_User( 'simcock', 'Adam Simcock', 'simcock@earthit.com' ),
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
            $this->permissions = new Grubbo_Auth_Permissions( array('*'=>'*') );
        }
        return $this->permissions;
    }

    function getPossibleActions( $resourceName, $resource ) {
        if( $resource === null ) return array();
        
        $actions = array();
        $actions[] = new Grubbo_Mvc_ResourceAction('view', 'View', array());
        if( $resource instanceof Grubbo_Value_Blob ) {
            $actions[] = new Grubbo_Mvc_ResourceAction('edit', 'Edit');
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
        } else if( $resource instanceof Grubbo_Value_Directory ) {
            $tplVars['resource'] = $resource;
            $rmd = $resource->getContentMetadata();
            if( !$title ) { $title = @$rmd['title']; }
            if( !$title ) { $title = $tplVars['resourceName'];  }
            $tplVars['pageTitle'] = "Index of $title";
            $this->getTemplate( 'view-directory' )->output( $tplVars);
        } else if( $resoruce instanceof Grubbo_Value_Blob ) {
            $md = $resource->getContentMetadata();
            if( $ct = @$md['type'] ) header('content-type: '.$ct);
            if( $mt = @$md['last-modified'] ) {
                if( !is_numeric($mt) ) {  $mt = strtotime($mt);  }
                header('last-modified: '.gmdate('D, d M Y H:i:s T', $mt));
            }
            $resource->writeContent(fopen('php://output','w'));
        } else {
            throw new Exception("Don't know how to output ".$resource);
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
        return new Grubbo_Value_StringResource( $content, $cmd );
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
        return new Grubbo_Value_StringResource( $content, $metadata );
    }

    function createBlankDocument() {
        $metadata = array(
            'doc/format' => 'wiki',
            'doc/title' => 'New Page',
        );
        $content = "Enter text here";
        return new Grubbo_Value_StringResource( $content, $metadata );
    }

    protected $sessionStarted;
    function startSession() {
        if( !$this->sessionStarted ) {
            session_name('Grubbo');
            session_start();
        }
    }

    protected function usernamesToRecipientStrings( $usernames ) {
        $rs = array();
        foreach( $usernames as $un ) {
            $user = $this->getUser( $un );
            if( $user !== null ) {
                $rs[] = $user->getName().' <'.$user->getEmailAddress().'>';
            }
        }
        return $rs;
    }

    function docUpdated( $doc, $docName ) {
        if( $m = $this->mailer ) {
            $md = $doc->getContentMetadata();
            $at = explode(', ', $md['doc/assigned-to']);
            $cc = explode(', ', $md['doc/cc']);
            $body = $md['doc/title']." has been updated\n"
                  . $this->siteUri . $docName . "\n"
                  . "\n"
                  . '== '.$md['doc/title']." ==\n"
                  . $doc->getContent();
            $toAddys = $this->usernamesToRecipientStrings( array_merge($at,$cc) );
            $mes = new Grubbo_Mail_Message( $this->docUpdateFromAddress, $toAddys,
                                            $md['doc/title'].' updated', $body );
            $m->send( $mes );
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
            'siteTitle' => $this->siteTitle,
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
                        $commitInfo = new Grubbo_Vcs_CommitInfo( $user, $date, "New ticket $newResourceName" . ($title ? " - $title" : ''));
                        $this->resourceStore->put( $newResourceName, $doc );
                        $this->resourceStore->commit( $commitInfo );
                        $this->resourceStore->closeTransaction();
                        $this->docUpdated( $doc, $newResourceName );
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
            $commitInfo = new Grubbo_Vcs_CommitInfo( $user, $date, $commitTitle );
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
            $commitInfo = new Grubbo_Vcs_CommitInfo( $user, $date, $commitTitle );
            $this->resourceStore->openTransaction();
            try {
                $this->resourceStore->put( $this->resourceName, $doc );
                $this->resourceStore->commit( $commitInfo );
                $this->resourceStore->closeTransaction();
                $this->docUpdated( $doc, $this->resourceName );
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

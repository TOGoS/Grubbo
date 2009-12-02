<?php

class Grubbo_Wikitext_WikitextProcessor {
    protected $uriProcessor;
    function __construct( $uriProcessor ) {
        $this->uriProcessor = $uriProcessor;
    }

    function replaceWikiLink( $match ) {
        $uri = $match[1];
        $linkText = $match[2];
        $normalText = $match[3];
        if( $normalText ) {
            return htmlspecialchars($normalText);
        } else {
            if( !$linkText ) $linkText = $uri;
            return "<a href=\"".htmlspecialchars($this->uriProcessor->pathTo($uri))."\">".htmlspecialchars($linkText)."</a>";
        }
    }

    function openWikitextState( $state, &$html ) {
        if( $state == 'bq' ) {
            $html .= "<blockquote><pre>";
        } else if( $state == 'hr' ) {
            $html .= "<hr />\n\n";
        } else if( $state == 'p' ) {
            $html .= "<p>";
        }
    }

    function closeWikitextState( $state, &$html ) {
        if( $state == 'bq' ) {
            $html .= "</pre></blockquote>\n\n";
        } else if( $state == 'p' ) {
            $html .= "</p>\n\n";
        } else if( $state == 'li' ) {
            $html .= "</li>\n";
        }
    }

    function wikitextToHtml( $wiki ) {
        $fixedLinks = preg_replace_callback( '/\[([a-z]+:\S+)(?:\s([^\]]+))?\]|([^\[]*|\[)/', array($this,'replaceWikiLink'), $wiki );

        // TODO: get a real wikitext formatter

        $state = null;
        $listLevel = 0;
        $html = '';

        $lines = explode( "\n", $fixedLinks );
        $lines[] = false;
        foreach( $lines as $line ) {
            if( is_string($line) ) $line = rtrim($line);
            $newListLevel = 0;
            if( $line === false ) {
                $newState = 'end';
            } else if( preg_match( '/^--+$/', $line ) ) {
                $newState = 'hr';
                $line = '';
            } else if( preg_match( '/^(\*+) (.*)$/', $line, $bif ) ) {
                $newState = 'li';
                $newListLevel = strlen($bif[1]);
                $line = $bif[2];
            } else if( preg_match( '/^\s+(.*)$/', $line, $bif ) ) {
                $newState = 'bq';
                $line = $bif[1]."\n";
            } else if( $line == '' ) {
                $newState = null;
            } else {
                $newState = 'p';
            }

            if( $state != $newState ) {
                $this->closeWikitextState( $state, $html );
                $this->openWikitextState( $newState, $html );
            }

            if( $state == 'li' and $newListLevel <= $listLevel ) {
                $html .= "</li>\n";
            }
            #$html .= "(cll:$listLevel,$newListLevel $line)";
            while( $listLevel > $newListLevel ) {
                $html .= str_repeat("  ",$listLevel-1)."</ul>";
                if( $listLevel > 0 ) {
                    $html .= "</li>\n";
                } else {
                    $html .= "\n\n";
                }
                $listLevel--;
            }
            if( $newState == 'li' ) {
                #$html .= "(oll:$listLevel,$newListLevel)";
                if( $listLevel == $newListLevel ) {
                    $html .= str_repeat("  ",$newListLevel)."<li>";
                } else {
                    while( $listLevel < $newListLevel ) {
                        $listLevel++;
                        $html .= "<ul>\n".str_repeat("  ",$listLevel)."<li>";
                    }
                }
            }

            if( $state == $newState ) {
                if( $state == 'p' ) {
                    $html .= "<br />";
                }
            }
            $state = $newState;

            $html .= $line;
        }
        return $html;
    }
}
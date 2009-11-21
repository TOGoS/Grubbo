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

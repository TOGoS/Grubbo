<?php

require_once 'Grubbo/IO/OutputStream.php';

/**
 * OutputStream implementation that writes data back to the browser
 * (or any registered output buffers) using 'echo'.
 */
class Grubbo_IO_WebOutputStream implements Grubbo_IO_OutputStream {
    public function write( $data ) {
        echo $data;
    }
}
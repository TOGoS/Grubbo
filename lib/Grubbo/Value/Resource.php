<?php

interface Grubbo_Value_Resource {
    function getContentMetadata();
    // TODO: 'resource' should not be the same object
    // as its content.  Change Blob, Directory to not
    // be subclasses of Resource, and instead give
    // Resource a 'content' property, which can reference anything.
}

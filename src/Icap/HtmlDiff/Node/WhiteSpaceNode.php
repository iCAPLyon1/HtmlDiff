<?php

namespace Icap\HtmlDiff\Node;

class WhiteSpaceNode extends TextNode {

    function __construct($parent, $s, Node $like = null) {
        parent::__construct($parent, $s);
        if(!is_null($like) && $like instanceof TextNode) {
            $newModification = clone $like->modification;
            $newModification->firstOfID = false;
            $this->modification = $newModification;
        }
    }
}
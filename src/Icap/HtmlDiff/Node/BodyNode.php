<?php

/**
 * Represents the root of a HTML document.
 */

namespace Icap\HtmlDiff\Node;

class BodyNode extends TagNode {

    function __construct() {
        parent::__construct(null, 'body', array());
    }

    public function copyTree() {
        $newThis = new BodyNode();
        foreach ($this->children as &$child) {
            $newChild = $child->copyTree();
            $newChild->setParent($newThis);
            $newThis->children[] = $newChild;
        }
        return $newThis;
    }

    public function getMinimalDeletedSet($id, &$allDeleted, &$somethingDeleted) {
        $nodes = array();
        foreach ($this->children as &$child) {
            $childrenChildren = $child->getMinimalDeletedSet($id,
                        $allDeleted, $somethingDeleted);
            $nodes = array_merge($nodes, $childrenChildren);
        }
        return $nodes;
    }

}
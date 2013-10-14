<?php

/**
 * Node that can contain other nodes. Represents an HTML tag.
 */

namespace Icap\HtmlDiff\Node;

use Icap\HtmlDiff\Html\TextOnlyComparator;
use Icap\HtmlDiff\Xml\Xml;

class TagNode extends Node {

    public $children = array();

    public $qName;

    public $attributes = array();

    public $openingTag;

    function __construct($parent, $qName, /*array*/ $attributes) {
        parent::__construct($parent);
        $this->qName = strtolower($qName);
        foreach($attributes as $key => &$value){
            $this->attributes[strtolower($key)] = $value;
        }
        return $this->openingTag = Xml::openElement($this->qName, $this->attributes);
    }

    public function addChildAbsolute(Node $node, $index) {
        array_splice($this->children, $index, 0, array($node));
    }

    public function getIndexOf(Node $child) {
        // don't trust array_search with objects
        foreach ($this->children as $key => &$value){
            if ($value === $child) {
                return $key;
            }
        }
        return null;
    }

    public function getNbChildren() {
        return count($this->children);
    }

    public function getMinimalDeletedSet($id, &$allDeleted, &$somethingDeleted) {
        $nodes = array();

        $allDeleted = false;
        $somethingDeleted = false;
        $hasNonDeletedDescendant = false;

        if (empty($this->children)) {
            return $nodes;
        }

        foreach ($this->children as &$child) {
            $allDeleted_local = false;
            $somethingDeleted_local = false;
            $childrenChildren = $child->getMinimalDeletedSet($id, $allDeleted_local, $somethingDeleted_local);
            if ($somethingDeleted_local) {
                $nodes = array_merge($nodes, $childrenChildren);
                $somethingDeleted = true;
            }
            if (!$allDeleted_local) {
                $hasNonDeletedDescendant = true;
            }
        }
        if (!$hasNonDeletedDescendant) {
            $nodes = array($this);
            $allDeleted = true;
        }
        return $nodes;
    }

    public function splitUntil(TagNode $parent, Node $split, $includeLeft) {
        $splitOccured = false;
        if ($parent !== $this) {
            $part1 = new TagNode(null, $this->qName, $this->attributes);
            $part2 = new TagNode(null, $this->qName, $this->attributes);
            $part1->setParent($this->parent);
            $part2->setParent($this->parent);

            $onSplit = false;
            $pastSplit = false;
            foreach ($this->children as &$child)
            {
                if ($child === $split) {
                    $onSplit = true;
                }
                if(!$pastSplit || ($onSplit && $includeLeft)) {
                    $child->setParent($part1);
                    $part1->children[] = $child;
                } else {
                    $child->setParent($part2);
                    $part2->children[] = $child;
                }
                if ($onSplit) {
                    $onSplit = false;
                    $pastSplit = true;
                }
            }
            $myindexinparent = $this->parent->getIndexOf($this);
            if (!empty($part1->children)) {
                $this->parent->addChildAbsolute($part1, $myindexinparent);
            }
            if (!empty($part2->children)) {
                $this->parent->addChildAbsolute($part2, $myindexinparent);
            }
            if (!empty($part1->children) && !empty($part2->children)) {
                $splitOccured = true;
            }

            $this->parent->removeChild($myindexinparent);

            if ($includeLeft) {
                $this->parent->splitUntil($parent, $part1, $includeLeft);
            } else {
                $this->parent->splitUntil($parent, $part2, $includeLeft);
            }
        }
        return $splitOccured;

    }

    private function removeChild($index) {
        unset($this->children[$index]);
        $this->children = array_values($this->children);
    }

    public static $blocks = array('html', 'body','p','blockquote', 'h1',
        'h2', 'h3', 'h4', 'h5', 'pre', 'div', 'ul', 'ol', 'li', 'table',
        'tbody', 'tr', 'td', 'th', 'br');

    public function copyTree() {
        $newThis = new TagNode(null, $this->qName, $this->attributes);
        $newThis->whiteBefore = $this->whiteBefore;
        $newThis->whiteAfter = $this->whiteAfter;
        foreach ($this->children as &$child) {
            $newChild = $child->copyTree();
            $newChild->setParent($newThis);
            $newThis->children[] = $newChild;
        }
        return $newThis;
    }

    public function getMatchRatio(TagNode $other) {
        $txtComp = new TextOnlyComparator($other);
        return $txtComp->getMatchRatio(new TextOnlyComparator($this));
    }

    public function expandWhiteSpace() {
        $shift = 0;
        $spaceAdded = false;

        $nbOriginalChildren = $this->getNbChildren();
        for ($i = 0; $i < $nbOriginalChildren; ++$i) {
            $child = $this->children[$i + $shift];

            if ($child instanceof TagNode) {
                if (!$child->isPre()) {
                    $child->expandWhiteSpace();
                }
            }
            if (!$spaceAdded && $child->whiteBefore) {
                $ws = new WhiteSpaceNode(null, ' ', $child->getLeftMostChild());
                $ws->setParent($this);
                $this->addChildAbsolute($ws,$i + ($shift++));
            }
            if ($child->whiteAfter) {
                $ws = new WhiteSpaceNode(null, ' ', $child->getRightMostChild());
                $ws->setParent($this);
                $this->addChildAbsolute($ws,$i + 1 + ($shift++));
                $spaceAdded = true;
            } else {
                $spaceAdded = false;
            }

        }
    }

    public function getLeftMostChild() {
        if (empty($this->children)) {
            return $this;
        }
        return $this->children[0]->getLeftMostChild();
    }

    public function getRightMostChild() {
        if (empty($this->children)) {
            return $this;
        }
        return $this->children[$this->getNbChildren() - 1]->getRightMostChild();
    }

    public function isPre() {
        return 0 == strcasecmp($this->qName,'pre');
    }

    public static function toDiffLine(TagNode $node) {
        return $node->openingTag;
    }
}
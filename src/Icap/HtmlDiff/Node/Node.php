<?php

/**
 * Any element in the DOM tree of an HTML document.
 * @ingroup DifferenceEngine
 */

namespace Icap\HtmlDiff\Node;

use Icap\HtmlDiff\Html\LastCommonParentResult;

class Node {

    public $parent;

    protected $parentTree;

    public $whiteBefore = false;

    public $whiteAfter = false;

    function __construct($parent) {
        $this->parent = $parent;
    }

    public function getParentTree() {
        if (!isset($this->parentTree)) {
            if (!is_null($this->parent)) {
                $this->parentTree = $this->parent->getParentTree();
                $this->parentTree[] = $this->parent;
            } else {
                $this->parentTree = array();
            }
        }
        return $this->parentTree;
    }

    public function getLastCommonParent(Node $other) {
        $result = new LastCommonParentResult();

        $myParents = $this->getParentTree();
        $otherParents = $other->getParentTree();

        $i = 1;
        $isSame = true;
        $nbMyParents = count($myParents);
        $nbOtherParents = count($otherParents);
        
        //Code of library
        while ($isSame && $i < $nbMyParents && $i < $nbOtherParents) {
            if (!($myParents[$i]->openingTag === $otherParents[$i]->openingTag)) {
                $isSame = false;
            } else {
                // After a while, the index i-1 must be the last common parent
                $i++;
            }
        }
        
        
        /*New code - fix it before use (adds more nodes than necessary)
        if ($nbMyParents > 0 && $nbOtherParents > 0 && $myParents[0] === $otherParents[0]) {
            while ($isSame && $i < $nbMyParents && $i < $nbOtherParents) {
                if (!($myParents[$i] === $otherParents[$i])) {
                    $isSame = false;
                } else {
                    // After a while, the index i-1 must be the last common parent
                    $i++;
                }
            }
        }
        else {
            while ($isSame && $i < $nbMyParents && $i < $nbOtherParents) {
                if (!($myParents[$i]->openingTag === $otherParents[$i]->openingTag)) {
                    $isSame = false;
                } else {
                    // After a while, the index i-1 must be the last common parent
                    $i++;
                }
            }
        }*/
                

        $result->lastCommonParentDepth = $i - 1;
        $result->parent = $myParents[$i - 1];

        if (!$isSame || $nbMyParents > $nbOtherParents) {
            // Not all tags matched, or all tags matched but
            // there are tags left in this tree
            $result->indexInLastCommonParent = $myParents[$i - 1]->getIndexOf($myParents[$i]);
            $result->splittingNeeded = true;
        } else if ($nbMyParents <= $nbOtherParents) {
            $result->indexInLastCommonParent = $myParents[$i - 1]->getIndexOf($this);
        }
        return $result;
    }

    public function setParent($parent) {
        $this->parent = $parent;
        unset($this->parentTree);
    }

    public function inPre() {
        $tree = $this->getParentTree();
        foreach ($tree as &$ancestor) {
            if ($ancestor->isPre()) {
                return true;
            }
        }
        return false;
    }
}
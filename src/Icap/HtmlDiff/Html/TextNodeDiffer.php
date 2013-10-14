<?php

namespace Icap\HtmlDiff\Html;

class TextNodeDiffer {

    private $textNodes;
    public $bodyNode;

    private $oldTextNodes;
    private $oldBodyNode;

    private $newID = 0;

    private $changedID = 0;

    private $changedIDUsed = false;

    // used to remove the whitespace between a red and green block
    private $whiteAfterLastChangedPart = false;

    private $deletedID = 0;

    function __construct(DomTreeBuilder $tree, DomTreeBuilder $oldTree) {
        $this->textNodes = $tree->textNodes;
        $this->bodyNode = $tree->bodyNode;
        $this->oldTextNodes = $oldTree->textNodes;
        $this->oldBodyNode = $oldTree->bodyNode;
    }

    public function markAsNew($start, $end) {
        if ($end <= $start) {
            return;
        }

        if ($this->whiteAfterLastChangedPart) {
            $this->textNodes[$start]->whiteBefore = false;
        }

        for ($i = $start; $i < $end; ++$i) {
            $mod = new Modification(Modification::ADDED);
            $mod->id = $this->newID;
            $this->textNodes[$i]->modification = $mod;
        }
        if ($start < $end) {
            $this->textNodes[$start]->modification->firstOfID = true;
        }
        ++$this->newID;
    }

    public function handlePossibleChangedPart($leftstart, $leftend, $rightstart, $rightend) {
        $i = $rightstart;
        $j = $leftstart;

        if ($this->changedIDUsed) {
            ++$this->changedID;
            $this->changedIDUsed = false;
        }

        $changes;
        while ($i < $rightend) {
            $acthis = new AncestorComparator($this->textNodes[$i]->getParentTree());
            $acother = new AncestorComparator($this->oldTextNodes[$j]->getParentTree());
            $result = $acthis->getResult($acother);
            unset($acthis, $acother);

            if ( $result ) {
                $mod = new Modification(Modification::CHANGED);
                if (!$this->changedIDUsed) {
                    $mod->firstOfID = true;                 
                } else if (!is_null( $result ) && $result !== $this->changes) {
                    ++$this->changedID;
                    $mod->firstOfID = true;
                }

                $mod->changes = $result;
                $mod->id = $this->changedID;

                $this->textNodes[$i]->modification = $mod;
                $this->changes = $result;
                $this->changedIDUsed = true;
            } else if ($this->changedIDUsed) {
                ++$this->changedID;
                $this->changedIDUsed = false;
            }
            ++$i;
            ++$j;
        }
    }

    public function markAsDeleted($start, $end, $before) {

        if ($end <= $start) {
            return;
        }

        if ($before > 0 && $this->textNodes[$before - 1]->whiteAfter) {
            $this->whiteAfterLastChangedPart = true;
        } else {
            $this->whiteAfterLastChangedPart = false;
        }

        for ($i = $start; $i < $end; ++$i) {
            $mod = new Modification(Modification::REMOVED);
            $mod->id = $this->deletedID;            
            // oldTextNodes is used here because we're going to move its deleted
            // elements to this tree!
            $this->oldTextNodes[$i]->modification = $mod;
        }
        $this->oldTextNodes[$start]->modification->firstOfID = true;

        $root = $this->oldTextNodes[$start]->getLastCommonParent($this->oldTextNodes[$end-1])->parent;

        $junk1 = $junk2 = null;
        $deletedNodes = $root->getMinimalDeletedSet($this->deletedID, $junk1, $junk2);

        HTMLDiffer::diffDebug( "Minimal set of deleted nodes of size " . count($deletedNodes) . "\n" );

        // Set prevLeaf to the leaf after which the old HTML needs to be
        // inserted
        if ($before > 0) {
            $prevLeaf = $this->textNodes[$before - 1];
        }
        // Set nextLeaf to the leaf before which the old HTML needs to be
        // inserted
        if ($before < count($this->textNodes)) {
            $nextLeaf = $this->textNodes[$before];
        }

        while (count($deletedNodes) > 0) {
            if (isset($prevLeaf)) {
                $prevResult = $prevLeaf->getLastCommonParent($deletedNodes[0]);
            } else {
                $prevResult = new LastCommonParentResult();
                $prevResult->parent = $this->bodyNode;
                $prevResult->indexInLastCommonParent = -1;
            }
            if (isset($nextleaf)) {
                $nextResult = $nextLeaf->getLastCommonParent($deletedNodes[count($deletedNodes) - 1]);
            } else {
                $nextResult = new LastCommonParentResult();
                $nextResult->parent = $this->bodyNode;
                $nextResult->indexInLastCommonParent = $this->bodyNode->getNbChildren();
            }

            if ($prevResult->lastCommonParentDepth == $nextResult->lastCommonParentDepth) {
                // We need some metric to choose which way to add-...
                if ($deletedNodes[0]->parent === $deletedNodes[count($deletedNodes) - 1]->parent
                        && $prevResult->parent === $nextResult->parent) {
                    // The difference is not in the parent
                    $prevResult->lastCommonParentDepth = $prevResult->lastCommonParentDepth + 1;
                } else {
                    // The difference is in the parent, so compare them
                    // now THIS is tricky
                    $distancePrev = $deletedNodes[0]->parent->getMatchRatio($prevResult->parent);
                    $distanceNext = $deletedNodes[count($deletedNodes) - 1]->parent->getMatchRatio($nextResult->parent);

                    if ($distancePrev <= $distanceNext) {
                        $prevResult->lastCommonParentDepth = $prevResult->lastCommonParentDepth + 1;
                    } else {
                        $nextResult->lastCommonParentDepth = $nextResult->lastCommonParentDepth + 1;
                    }
                }

            }

            if ($prevResult->lastCommonParentDepth > $nextResult->lastCommonParentDepth) {
                // Inserting at the front
                if ($prevResult->splittingNeeded) {
                    $prevLeaf->parent->splitUntil($prevResult->parent, $prevLeaf, true);
                }
                $prevLeaf = $deletedNodes[0]->copyTree();
                unset($deletedNodes[0]);
                $deletedNodes = array_values($deletedNodes);
                $prevLeaf->setParent($prevResult->parent);
                $prevResult->parent->addChildAbsolute($prevLeaf,$prevResult->indexInLastCommonParent + 1);
            } else if ($prevResult->lastCommonParentDepth < $nextResult->lastCommonParentDepth) {
                // Inserting at the back
                if ($nextResult->splittingNeeded) {
                    $splitOccured = $nextLeaf->parent->splitUntil($nextResult->parent, $nextLeaf, false);
                    if ($splitOccured) {
                        // The place where to insert is shifted one place to the
                        // right
                        $nextResult->indexInLastCommonParent = $nextResult->indexInLastCommonParent + 1;
                    }
                }
                $nextLeaf = $deletedNodes[count(deletedNodes) - 1]->copyTree();
                unset($deletedNodes[count(deletedNodes) - 1]);
                $deletedNodes = array_values($deletedNodes);
                $nextLeaf->setParent($nextResult->parent);
                $nextResult->parent->addChildAbsolute($nextLeaf,$nextResult->indexInLastCommonParent);
            }
        }
        ++$this->deletedID;
    }

    public function expandWhiteSpace() {
        $this->bodyNode->expandWhiteSpace();
    }

    public function lengthNew(){
        return count($this->textNodes);
    }

    public function lengthOld(){
        return count($this->oldTextNodes);
    }
}
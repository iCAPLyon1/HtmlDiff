<?php

/**
 * When detecting the last common parent of two nodes, all results are stored as
 * a LastCommonParentResult.
 */

namespace Icap\HtmlDiff\Html;

class LastCommonParentResult {

    // Parent
    public $parent;

    // Splitting
    public $splittingNeeded = false;

    // Depth
    public $lastCommonParentDepth = -1;

    // Index
    public $indexInLastCommonParent = -1;
}
<?php

/**
 * A comparator used when calculating the difference in ancestry of two Nodes.
 */

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\WikiDiff3;

class AncestorComparator {

    public $ancestors;
    public $ancestorsText;

    function __construct(/*array*/ $ancestors) {
        $this->ancestors = $ancestors;
        $this->ancestorsText = array_map(array('Icap\\HtmlDiff\\Node\\TagNode','toDiffLine'), $ancestors);
    }

    public $compareTxt = "";

    public function getResult(AncestorComparator $other) {

        $diffengine = new WikiDiff3(10000, 1.35);
        $differences = $diffengine->diff_range($other->ancestorsText,$this->ancestorsText);

        if (count($differences) == 0){
            return null;
        }
        $changeTxt = new ChangeTextGenerator($this, $other);

        return $changeTxt->getChanged($differences)->toString();;
    }
}
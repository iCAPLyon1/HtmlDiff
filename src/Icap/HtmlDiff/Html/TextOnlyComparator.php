<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\WikiDiff3;
use Icap\HtmlDiff\Node\TagNode;
use Icap\HtmlDiff\Node\TextNode;

class TextOnlyComparator {

    public $leafs = array();

    function _construct(TagNode $tree) {
        $this->addRecursive($tree);
        $this->leafs = array_map(array('Icap\\HtmlDiff\\Node\\TextNode','toDiffLine'), $this->leafs);
    }

    private function addRecursive(TagNode $tree) {
        foreach ($tree->children as &$child) {
            if ($child instanceof TagNode) {
                $this->addRecursive($child);
            } else if ($child instanceof TextNode) {
                $this->leafs[] = $node;
            }
        }
    }

    public function getMatchRatio(TextOnlyComparator $other) {
        $nbOthers = count($other->leafs);
        $nbThis = count($this->leafs);
        if($nbOthers == 0 || $nbThis == 0){
            return -log(0);
        }

        $diffengine = new WikiDiff3(25000, 1.35);
        $diffengine->diff($this->leafs, $other->leafs);

        $lcsLength = $diffengine->getLcsLength();

        $distanceThis = $nbThis-$lcsLength;

        return (2.0 - $lcsLength/$nbOthers - $lcsLength/$nbThis) / 2.0;
    }
}
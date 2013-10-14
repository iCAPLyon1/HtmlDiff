<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\TagNode;

class ChangeTextGenerator
 {

    private $ancestorComparator;
    private $other;

    private $factory;

    function __construct(AncestorComparator $ancestorComparator, AncestorComparator $other) {
        $this->ancestorComparator = $ancestorComparator;
        $this->other = $other;
        $this->factory = new TagToStringFactory();
    }

    public function getChanged(/*array*/ $differences) {
        $txt = new ChangeText;
        $rootlistopened = false;
        if (count($differences) > 1) {
            $txt->addHtml('<ul class="changelist">');
            $rootlistopened = true;
        }
        $nbDifferences = count($differences);
        for ($j = 0; $j < $nbDifferences; ++$j) {
            $d = $differences[$j];
            $lvl1listopened = false;
            if ($rootlistopened) {
                $txt->addHtml('<li>');
            }
            if ($d->leftlength + $d->rightlength > 1) {
                $txt->addHtml('<ul class="changelist">');
                $lvl1listopened = true;
            }
            // left are the old ones
            for ($i = $d->leftstart; $i < $d->leftend; ++$i) {
                if ($lvl1listopened){
                    $txt->addHtml('<li>');
                }
                // add a bullet for a old tag
                $this->addTagOld($txt, $this->other->ancestors[$i]);
                if ($lvl1listopened){
                    $txt->addHtml('</li>');
                }
            }
            // right are the new ones
            for ($i = $d->rightstart; $i < $d->rightend; ++$i) {
                if ($lvl1listopened){
                    $txt->addHtml('<li>');
                }
                // add a bullet for a new tag
                $this->addTagNew($txt, $this->ancestorComparator->ancestors[$i]);

                if ($lvl1listopened){
                    $txt->addHtml('</li>');
                }
            }
            if ($lvl1listopened) {
                $txt->addHtml('</ul>');
            }
            if ($rootlistopened) {
                $txt->addHtml('</li>');
            }
        }
        if ($rootlistopened) {
            $txt->addHtml('</ul>');
        }
        return $txt;
    }

    private function addTagOld(ChangeText $txt, TagNode $ancestor) {
        $this->factory->create($ancestor)->getRemovedDescription($txt);
    }

    private function addTagNew(ChangeText $txt, TagNode $ancestor) {
        $this->factory->create($ancestor)->getAddedDescription($txt);
    }
}
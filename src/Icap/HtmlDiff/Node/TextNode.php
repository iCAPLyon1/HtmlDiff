<?php

/**
 * Represents a piece of text in the HTML file.
 */

namespace Icap\HtmlDiff\Node;

use Icap\HtmlDiff\Html\Modification;

class TextNode extends Node {

    public $text;

    public $modification;

    function __construct($parent, $text) {
        parent::__construct($parent);
        $this->modification = new Modification(Modification::NONE);
        $this->text = $text;
    }

    public function copyTree() {
        $clone = clone $this;
        $clone->setParent(null);
        return $clone;
    }

    public function getLeftMostChild() {
        return $this;
    }

    public function getRightMostChild() {
        return $this;
    }

    public function getMinimalDeletedSet($id, &$allDeleted, &$somethingDeleted) {
        if ($this->modification->type == Modification::REMOVED
                    && $this->modification->id == $id){
            $somethingDeleted = true;
            $allDeleted = true;
            return array($this);
        }
        return array();
    }

    public function isSameText($other) {
        if (is_null($other) || ! $other instanceof TextNode) {
            return false;
        }
        return str_replace('\n', ' ',$this->text) === str_replace('\n', ' ',$other->text);
    }

    public static function toDiffLine(TextNode $node) {
        return str_replace('\n', ' ',$node->text);
    }
}
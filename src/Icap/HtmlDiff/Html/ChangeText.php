<?php

namespace Icap\HtmlDiff\Html;

class ChangeText {

    private $txt = "";
    private $modifications = array();

    public function addHtml($s) {
        $this->txt .= $s;
    }

    public function toString() {
        return $this->txt;
    }

    public function setModifications($modifications) {
        $this->modifications = $modifications;
    }

    /**
     * @return $modifications, array('added' => int, 'changed' => int, 'removed' => int)
     */
    public function getModifications() {
        return $this->modifications;
    }
}
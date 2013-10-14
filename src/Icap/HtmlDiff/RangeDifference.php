<?php

/**
 * Alternative representation of a set of changes, by the index
 * ranges that are changed.
 * @author Guy Van den Broeck
 */

namespace Icap\HtmlDiff;

class RangeDifference {

    public $leftstart;
    public $leftend;
    public $leftlength;

    public $rightstart;
    public $rightend;
    public $rightlength;

    function __construct($leftstart, $leftend, $rightstart, $rightend){
        $this->leftstart = $leftstart;
        $this->leftend = $leftend;
        $this->leftlength = $leftend - $leftstart;
        $this->rightstart = $rightstart;
        $this->rightend = $rightend;
        $this->rightlength = $rightend - $rightstart;
    }
}
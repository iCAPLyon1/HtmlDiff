<?php
/**
 * 
 */

namespace Icap\HtmlDiff; 

use Icap\HtmlDiff\Html\HTMLDiffer;
use Icap\HtmlDiff\Html\ChangeText;
use Icap\HtmlDiff\Html\DelegatingContentHandler;

class HtmlDiff {
    private $oldText;
    
    private $newText;
    
    private $enableFormattingChanges = false;

    function __construct($oldText, $newText, $enableFormattingChanges) {
        $this->oldText = $oldText;
        $this->newText = $newText;
        $this->enableFormattingChanges = $enableFormattingChanges;
    }

    public function outputDiff () {
        $out = new ChangeText();
        $htmldiffer = new HTMLDiffer(new DelegatingContentHandler($out));
        $htmldiffer->htmlDiff($this->oldText, $this->newText, $this->enableFormattingChanges);

        return $out;
    }
}
<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Xml\Xml;

class DelegatingContentHandler {

    private $delegate;

    function __construct($delegate) {
        $this->delegate = $delegate;
    }

    function startElement($qname, /*array*/ $arguments) {
        $this->delegate->addHtml(Xml::openElement($qname, $arguments));
    }

    function endElement($qname){
        $this->delegate->addHtml(Xml::closeElement($qname));
    }

    function characters($chars){
        $this->delegate->addHtml(htmlspecialchars($chars));
    }

    function html($html){
        $this->delegate->addHtml($html);
    }

    function modifications($modifications){
        $this->delegate->setModifications($modifications);
    }
}
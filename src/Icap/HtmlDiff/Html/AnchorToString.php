<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\TagNode;

class AnchorToString extends TagToString {

    function __construct(TagNode $node, $sem) {
        parent::__construct($node, $sem);
    }

    protected function addAttributes(ChangeText $txt, array $attributes) {
        if (array_key_exists('href', $attributes)) {
            $txt->addHtml(' ' . $this->wfMsgExt( 'diff-withdestination', 'parseinline', htmlspecialchars($attributes['href']) ) );
            unset($attributes['href']);
        }
        parent::addAttributes($txt, $attributes);
    }
}
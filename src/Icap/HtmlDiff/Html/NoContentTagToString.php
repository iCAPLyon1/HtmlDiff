<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\TagNode;

class NoContentTagToString extends TagToString {

    function __construct(TagNode $node, $sem) {
        parent::__construct($node, $sem);
    }

    public function getAddedDescription(ChangeText $txt) {
        $tagDescription = $this->wfMsgExt('diff-' . $this->node->qName, 'parseinline' );
        if( $this->wfEmptyMsg( 'diff-' . $this->node->qName, $tagDescription ) ){
            $tagDescription = "&lt;" . $this->node->qName . "&gt;";
        }
        $txt->addHtml( $this->wfMsgExt('diff-changedto', 'parseinline', $tagDescription ) );
        $this->addAttributes($txt, $this->node->attributes);
        $txt->addHtml('.');
    }

    public function getRemovedDescription(ChangeText $txt) {
        $tagDescription = $this->wfMsgExt('diff-' . $this->node->qName, 'parseinline' );
        if( $this->wfEmptyMsg( 'diff-' . $this->node->qName, $tagDescription ) ){
            $tagDescription = "&lt;" . $this->node->qName . "&gt;";
        }
        $txt->addHtml( $this->wfMsgExt('diff-changedfrom', 'parseinline', $tagDescription ) );
        $this->addAttributes($txt, $this->node->attributes);
        $txt->addHtml('.');
    }
}
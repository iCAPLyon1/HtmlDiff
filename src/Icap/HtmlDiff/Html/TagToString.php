<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\WfObject;
use Icap\HtmlDiff\Node\TagNode;

class TagToString extends WfObject {

    protected $node;

    protected $sem;

    function __construct(TagNode $node, $sem) {
        $this->node = $node;
        $this->sem = $sem;
    }

    public function getRemovedDescription(ChangeText $txt) {
        $tagDescription = $this->wfMsgExt('diff-' . $this->node->qName, 'parseinline' );
        if( $this->wfEmptyMsg( 'diff-' . $this->node->qName, $tagDescription ) ){
            $tagDescription = "&lt;" . $this->node->qName . "&gt;";
        }
        if ($this->sem == TagToStringFactory::MOVED) {
            $txt->addHtml( $this->wfMsgExt( 'diff-movedoutof', 'parseinline', $tagDescription ) );
        } else if ($this->sem == TagToStringFactory::STYLE) {
            $txt->addHtml( $this->wfMsgExt( 'diff-styleremoved' , 'parseinline', $tagDescription ) );
        } else {
            $txt->addHtml( $this->wfMsgExt( 'diff-removed' , 'parseinline', $tagDescription ) );
        }
        $this->addAttributes($txt, $this->node->attributes);
        $txt->addHtml('.');
    }

    public function getAddedDescription(ChangeText $txt) {
        $tagDescription = $this->wfMsgExt('diff-' . $this->node->qName, 'parseinline' );
        if( $this->wfEmptyMsg( 'diff-' . $this->node->qName, $tagDescription ) ){
            $tagDescription = "&lt;" . $this->node->qName . "&gt;";
        }
        if ($this->sem == TagToStringFactory::MOVED) {
            $txt->addHtml( $this->wfMsgExt( 'diff-movedto' , 'parseinline', $tagDescription) );
        } else if ($this->sem == TagToStringFactory::STYLE) {
            $txt->addHtml( $this->wfMsgExt( 'diff-styleadded', 'parseinline', $tagDescription ) );
        } else {
            $txt->addHtml( $this->wfMsgExt( 'diff-added', 'parseinline', $tagDescription ) );
        }
        $this->addAttributes($txt, $this->node->attributes);
        $txt->addHtml('.');
    }

    protected function addAttributes(ChangeText $txt, array $attributes) {
        if (count($attributes) < 1) {
            return;
        }
        $firstOne = true;
        $nbAttributes_min_1 = count($attributes)-1;
        $keys = array_keys($attributes);
        for ($i=0;$i<$nbAttributes_min_1;$i++) {
            $key = $keys[$i];
            $attr = $attributes[$key];
            if($firstOne) {
                $firstOne = false;
                $txt->addHtml( $this->wfMsgExt('diff-with', 'escapenoentities', $this->translateArgument($key), htmlspecialchars($attr) ) );
                continue;
            }
            $txt->addHtml( $this->wfMsgExt( 'comma-separator', 'escapenoentities' ) .
                $this->wfMsgExt( 'diff-with-additional', 'escapenoentities',
                $this->translateArgument( $key ), htmlspecialchars( $attr ) )
            );
        }

        if ($nbAttributes_min_1 > 0) {
            $txt->addHtml( $this->wfMsgExt( 'diff-with-final', 'escapenoentities',
            $this->translateArgument($keys[$nbAttributes_min_1]),
            htmlspecialchars($attributes[$keys[$nbAttributes_min_1]]) ) );
        }
    }

    protected function translateArgument($name) {
        $translation = $this->wfMsgExt('diff-' . $name, 'parseinline' );
        if ( $this->wfEmptyMsg( 'diff-' . $name, $translation ) ) {
            $translation = "&lt;" . $name . "&gt;";;
        }
        return htmlspecialchars( $translation );
    }
}
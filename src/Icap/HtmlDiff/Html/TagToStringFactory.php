<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\TagNode;

class TagToStringFactory {

    private static $containerTags = array('html', 'body', 'p', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'pre', 'div', 'ul', 'ol', 'li',
        'table', 'tbody', 'tr', 'td', 'th', 'br', 'hr', 'code', 'dl',
        'dt', 'dd', 'input', 'form', 'img', 'span', 'a');

    private static $styleTags = array('i', 'b', 'strong', 'em', 'font',
        'big', 'del', 'tt', 'sub', 'sup', 'strike');

    const MOVED = 1;
    const STYLE = 2;
    const UNKNOWN = 4;

    public function create(TagNode $node) {
        $sem = $this->getChangeSemantic($node->qName);
        if (strcasecmp($node->qName,'a') == 0) {
            return new AnchorToString($node, $sem);
        }
        if (strcasecmp($node->qName,'img') == 0) {
            return new NoContentTagToString($node, $sem);
        }
        return new TagToString($node, $sem);
    }

    protected function getChangeSemantic($qname) {
        if (in_array(strtolower($qname),self::$containerTags)) {
            return self::MOVED;
        }
        if (in_array(strtolower($qname),self::$styleTags)) {
            return self::STYLE;
        }
        return self::UNKNOWN;
    }
}
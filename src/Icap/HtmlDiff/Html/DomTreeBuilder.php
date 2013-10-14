<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\BodyNode;
use Icap\HtmlDiff\Node\DummyNode;
use Icap\HtmlDiff\Node\TextNode;
use Icap\HtmlDiff\Node\TagNode;
use Icap\HtmlDiff\Node\ImageNode;

class DomTreeBuilder {

    public $textNodes = array();

    public $bodyNode;

    private $currentParent;

    private $newWord = '';

    protected $bodyStarted = false;

    protected $bodyEnded = false;

    private $whiteSpaceBeforeThis = false;

    private $lastSibling;

    private $notInPre = true;

    function __construct() {
        $this->bodyNode = $this->currentParent = new BodyNode();
        $this->lastSibling = new DummyNode();
    }

    /**
     * Must be called manually
     */
    public function endDocument() {
        $this->endWord();
        HTMLDiffer::diffDebug( count($this->textNodes) . " text nodes in document.\n" );
    }

    public function startElement($parser, $name, /*array*/ $attributes) {
        if (strcasecmp($name, 'body') != 0) {
            HTMLDiffer::diffDebug( "Starting $name node.\n" );
            $this->endWord();

            $newNode = new TagNode($this->currentParent, $name, $attributes);
            $this->currentParent->children[] = $newNode;
            $this->currentParent = $newNode;
            $this->lastSibling = new DummyNode();
            if ($this->whiteSpaceBeforeThis && !in_array(strtolower($this->currentParent->qName),TagNode::$blocks)) {
                $this->currentParent->whiteBefore = true;
            }
            $this->whiteSpaceBeforeThis = false;
            if(strcasecmp($name, 'pre') == 0) {
                $this->notInPre = false;
            }
        }
    }

    public function endElement($parser, $name) {
        if(strcasecmp($name, 'body') != 0) {
            HTMLDiffer::diffDebug( "Ending $name node.\n");
            if (0 == strcasecmp($name,'img')) {
                // Insert a dummy leaf for the image
                $img = new ImageNode($this->currentParent, $this->currentParent->attributes);
                $this->currentParent->children[] = $img;
                $img->whiteBefore = $this->whiteSpaceBeforeThis;
                $this->lastSibling = $img;
                $this->textNodes[] = $img;
            }
            $this->endWord();
            if (!in_array(strtolower($this->currentParent->qName),TagNode::$blocks)) {
                $this->lastSibling = $this->currentParent;
            } else {
                $this->lastSibling = new DummyNode();
            }
            $this->currentParent = $this->currentParent->parent;
            $this->whiteSpaceBeforeThis = false;
            if (!$this->notInPre && strcasecmp($name, 'pre') == 0) {
                $this->notInPre = true;
            }
        } else {
            $this->endDocument();
        }
    }

    const regex = '/([\s\.\,\"\\\'\(\)\?\:\;\!\{\}\-\+\*\=\_\[\]\&\|\$]{1})/';
    const whitespace = '/^[\s]{1}$/';
    const delimiter = '/^[\s\.\,\"\\\'\(\)\?\:\;\!\{\}\-\+\*\=\_\[\]\&\|\$]{1}$/';

    public function characters($parser, $data) {
        $matches = preg_split(self::regex, $data, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach($matches as &$word) {
            if (preg_match(self::whitespace, $word) && $this->notInPre) {
                $this->endWord();
                $this->lastSibling->whiteAfter = true;
                $this->whiteSpaceBeforeThis = true;
            } else if (preg_match(self::delimiter, $word)) {
                $this->endWord();
                $textNode = new TextNode($this->currentParent, $word);
                $this->currentParent->children[] = $textNode;
                $textNode->whiteBefore = $this->whiteSpaceBeforeThis;
                $this->whiteSpaceBeforeThis = false;
                $this->lastSibling = $textNode;
                $this->textNodes[] = $textNode;
            } else {
                $this->newWord .= $word;
            }
        }
    }

    private function endWord() {
        if ($this->newWord !== '') {
            $node = new TextNode($this->currentParent, $this->newWord);
            $this->currentParent->children[] = $node;
            $node->whiteBefore = $this->whiteSpaceBeforeThis;
            $this->whiteSpaceBeforeThis = false;
            $this->lastSibling = $node;
            $this->textNodes[] = $node;
            $this->newWord = "";
        }
    }

    public function getDiffLines() {
        return array_map(array('Icap\\HtmlDiff\\Node\\TextNode','toDiffLine'), $this->textNodes);
    }
}
<?php

/**
 * Takes a branch root and creates an HTML file for it.
 */

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\Node\TagNode;
use Icap\HtmlDiff\Node\TextNode;
use Icap\HtmlDiff\Node\ImageNode;

class HTMLOutput{

    private $prefix;
    private $handler;

    //Modification counters
    private $addedModifications = 0;
    private $removedModifications = 0;
    private $changedModifications = 0;

    function __construct($prefix, $handler) {
        $this->prefix = $prefix;
        $this->handler = $handler;
    }

    public function parse(TagNode $node) {
        $handler = &$this->handler;

        if (strcasecmp($node->qName, 'img') != 0 && strcasecmp($node->qName, 'body') != 0) {
            $handler->startElement($node->qName, $node->attributes);
        }

        $newStarted = false;
        $remStarted = false;
        $changeStarted = false;
        $changeTXT = '';

        foreach ($node->children as &$child) {
            if ($child instanceof TagNode) {
                if ($newStarted) {
                    $handler->endElement('ins');
                    $newStarted = false;
                } else if ($changeStarted) {
                    $handler->endElement('span');
                    $changeStarted = false;
                } else if ($remStarted) {
                    $handler->endElement('del');
                    $remStarted = false;
                }
                $this->parse($child);
            } else if ($child instanceof TextNode) {
                $mod = $child->modification;

                if ($newStarted && ($mod->type != Modification::ADDED || $mod->firstOfID)) {
                    $handler->endElement('ins');
                    $newStarted = false;
                } else if ($changeStarted && ($mod->type != Modification::CHANGED
                        || $mod->changes != $changeTXT || $mod->firstOfID)) {
                    $handler->endElement('span');
                    $changeStarted = false;
                } else if ($remStarted && ($mod->type != Modification::REMOVED || $mod ->firstOfID)) {
                    $handler->endElement('del');
                    $remStarted = false;
                }

                // no else because a removed part can just be closed and a new
                // part can start
                if (!$newStarted && trim($child->text)!='' && $mod->type == Modification::ADDED) {
                    $attrs = array('class' => 'diff-html-added');
                    $this->addedModifications += 1;
                    if ($mod->firstOfID) {
                        $attrs['id'] = "added-{$this->prefix}-{$mod->id}";
                    }
                    $handler->startElement('ins', $attrs);
                    $newStarted = true;
                } else if (!$changeStarted && trim($child->text)!='' && $mod->type == Modification::CHANGED) {
                    $attrs = array('class' => 'diff-html-changed');
                    $this->changedModifications += 1;
                    if ($mod->firstOfID) {
                        $attrs['id'] = "changed-{$this->prefix}-{$mod->id}";
                    }
                    $handler->startElement('span', $attrs);

                    //tooltip -> Disabled for this library 
                    //$handler->startElement('span', array('class' => 'tip'));
                    //$handler->html($mod->changes);
                    //$handler->endElement('span');

                    $changeStarted = true;
                    $changeTXT = $mod->changes;
                } else if (!$remStarted && trim($child->text)!='' && $mod->type == Modification::REMOVED) {
                    $attrs = array('class'=>'diff-html-removed');
                    $this->removedModifications += 1;
                    if ($mod->firstOfID) {
                        $attrs['id'] = "removed-{$this->prefix}-{$mod->id}";
                    }
                    $handler->startElement('del', $attrs);
                    $remStarted = true;
                }

                $chars = $child->text;

                if ($child instanceof ImageNode) {
                    $this->writeImage($child);
                } else {
                    $handler->characters($chars);
                }
            }
        }

        if ($newStarted) {
            $handler->endElement('ins');
            $newStarted = false;
        } else if ($changeStarted) {
            $handler->endElement('span');
            $changeStarted = false;
        } else if ($remStarted) {
            $handler->endElement('del');
            $remStarted = false;
        }

        if (strcasecmp($node->qName, 'img') != 0
                && strcasecmp($node->qName, 'body') != 0) {
            $handler->endElement($node->qName);
        }

        $handler->modifications(
            array(
                'added' => $this->addedModifications, 
                'changed' => $this->changedModifications, 
                'removed' => $this->removedModifications
            )
        );
    }

    private function writeImage(ImageNode  $imgNode) {
        $attrs = $imgNode->attributes;
        $this->handler->startElement('img', $attrs);
        $this->handler->endElement('img');
    }
}

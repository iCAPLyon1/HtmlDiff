<?php

namespace Icap\HtmlDiff\Html;

use Icap\HtmlDiff\WfObject;
use Icap\HtmlDiff\RangeDifference;
use Icap\HtmlDiff\WikiDiff3;
use Icap\HtmlDiff\Sanitizer;
use Icap\HtmlDiff\Node\TagNode;
use Icap\HtmlDiff\Node\TextNode;

class HTMLDiffer extends WfObject {

    private $output;
    private static $debug = '';

    function __construct($output) {
        $this->output = $output;
    }

    function htmlDiff($from, $to, $describe_formatting_changes = true) {
        $this->wfProfileIn( __METHOD__ );
        // Create an XML parser
        $xml_parser = xml_parser_create('');

        $domfrom = new DomTreeBuilder();

        // Set the functions to handle opening and closing tags
        xml_set_element_handler($xml_parser, array($domfrom, "startElement"), array($domfrom, "endElement"));

        // Set the function to handle blocks of character data
        xml_set_character_data_handler($xml_parser, array($domfrom, "characters"));

        HTMLDiffer::diffDebug( "Parsing " . strlen($from) . " characters worth of HTML\n" );
        if (!xml_parse($xml_parser, '<?xml version="1.0" encoding="UTF-8"?>'.Sanitizer::hackDocType().'<body>', false)
                    || !xml_parse($xml_parser, $from, false)
                    || !xml_parse($xml_parser, '</body>', true)){
            $error = xml_error_string(xml_get_error_code($xml_parser));
            $line = xml_get_current_line_number($xml_parser);
            HTMLDiffer::diffDebug( "XML error: $error at line $line\n" );
        }
        xml_parser_free($xml_parser);
        unset($from);

        $xml_parser = xml_parser_create('');

        $domto = new DomTreeBuilder();

        // Set the functions to handle opening and closing tags
        xml_set_element_handler($xml_parser, array($domto, "startElement"), array($domto, "endElement"));

        // Set the function to handle blocks of character data
        xml_set_character_data_handler($xml_parser, array($domto, "characters"));

        HTMLDiffer::diffDebug( "Parsing " . strlen($to) . " characters worth of HTML\n" );
        if (!xml_parse($xml_parser, '<?xml version="1.0" encoding="UTF-8"?>'.Sanitizer::hackDocType().'<body>', false)
        || !xml_parse($xml_parser, $to, false)
        || !xml_parse($xml_parser, '</body>', true)){
            $error = xml_error_string(xml_get_error_code($xml_parser));
            $line = xml_get_current_line_number($xml_parser);
            HTMLDiffer::diffDebug( "XML error: $error at line $line\n" );
        }
        xml_parser_free($xml_parser);
        unset($to);
        $diffengine = new WikiDiff3();
        $differences = $this->preProcess($diffengine->diff_range($domfrom->getDiffLines(), $domto->getDiffLines()));
        unset($xml_parser, $diffengine);
        
        $domdiffer = new TextNodeDiffer($domto, $domfrom);

        $currentIndexLeft = 0;
        $currentIndexRight = 0;
        foreach ($differences as &$d) {
            if ($d->leftstart > $currentIndexLeft && $describe_formatting_changes) {
                $domdiffer->handlePossibleChangedPart($currentIndexLeft, $d->leftstart,
                    $currentIndexRight, $d->rightstart);
            }
            if ($d->leftlength > 0) {
                $domdiffer->markAsDeleted($d->leftstart, $d->leftend, $d->rightstart);
            }
            $domdiffer->markAsNew($d->rightstart, $d->rightend);

            $currentIndexLeft = $d->leftend;
            $currentIndexRight = $d->rightend;
        }
        $oldLength = $domdiffer->lengthOld();
        if ($currentIndexLeft < $oldLength && $describe_formatting_changes) {
            $domdiffer->handlePossibleChangedPart($currentIndexLeft, $oldLength, $currentIndexRight, $domdiffer->lengthNew());
        }
        $domdiffer->expandWhiteSpace();
        $output = new HTMLOutput('htmldiff', $this->output);
        $output->parse($domdiffer->bodyNode);
        $this->wfProfileOut( __METHOD__ );
    }

    private function preProcess(/*array*/ $differences) {
        $newRanges = array();

        $nbDifferences = count($differences);
        for ($i = 0; $i < $nbDifferences; ++$i) {
            $leftStart = $differences[$i]->leftstart;
            $leftEnd = $differences[$i]->leftend;
            $rightStart = $differences[$i]->rightstart;
            $rightEnd = $differences[$i]->rightend;

            $leftLength = $leftEnd - $leftStart;
            $rightLength = $rightEnd - $rightStart;

            while ($i + 1 < $nbDifferences && self::score($leftLength,
                        $differences[$i + 1]->leftlength,
                        $rightLength,
                        $differences[$i + 1]->rightlength)
                    > ($differences[$i + 1]->leftstart - $leftEnd)) {
                $leftEnd = $differences[$i + 1]->leftend;
                $rightEnd = $differences[$i + 1]->rightend;
                $leftLength = $leftEnd - $leftStart;
                $rightLength = $rightEnd - $rightStart;
                ++$i;
            }
            $newRanges[] = new RangeDifference($leftStart, $leftEnd, $rightStart, $rightEnd);
        }
        return $newRanges;
    }

    /**
     * Heuristic to merge differences for readability.
     */
    public static function score($ll, $nll, $rl, $nrl) {
        if (($ll == 0 && $nll == 0)
                || ($rl == 0 && $nrl == 0)) {
            return 0;
        }
        $numbers = array($ll, $nll, $rl, $nrl);
        $d = 0;
        foreach ($numbers as &$number) {
            while ($number > 3) {
                $d += 3;
                $number -= 3;
                $number *= 0.5;
            }
            $d += $number;

        }
        return $d / (1.5 * count($numbers));
    }

    /**
     * Add to debug output
     * @param string $str Debug output
     */
    public static function diffDebug( $str ) {
        self :: $debug .= $str;
    }
    
    /**
     * Get debug output
     * @return string
     */
    public static function getDebugOutput() {
        return self :: $debug;
    }

}
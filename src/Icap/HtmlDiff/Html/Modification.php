<?php

namespace Icap\HtmlDiff\Html;

class Modification {

    const NONE = 1;
    const REMOVED = 2;
    const ADDED = 4;
    const CHANGED = 8;

    public $type;

    public $id = -1;

    public $firstOfID = false;

    public $changes;

    function __construct($type) {
        $this->type = $type;
    }

    public static function typeToString($type) {
        switch($type) {
            case self::NONE: return 'none';
            case self::REMOVED: return 'removed';
            case self::ADDED: return 'added';
            case self::CHANGED: return 'changed';
        }
    }
}
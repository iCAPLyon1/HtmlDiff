<?php

/**
 * Represents an image in HTML. Even though images do not contain any text they
 * are independent visible objects on the page. They are logically a TextNode.
 */

namespace Icap\HtmlDiff\Node;

class ImageNode extends TextNode {

	public $attributes;

	function __construct(TagNode $parent, /*array*/ $attrs) {
		if(!array_key_exists('src', $attrs)) {
			HTMLDiffer::diffDebug( "Image without a source\n" );
			parent::__construct($parent, '<img></img>');
		}else{
			parent::__construct($parent, '<img>' . strtolower($attrs['src']) . '</img>');
		}
		$this->attributes = $attrs;
	}

	public function isSameText($other) {
		if (is_null($other) || ! $other instanceof ImageNode) {
			return false;
		}
		return $this->text === $other->text;
	}

}
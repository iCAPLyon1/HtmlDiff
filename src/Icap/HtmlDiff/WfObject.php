<?php

/**
 * HtmlDiff classes use some MediaWiki functions (which are defined in 
 * http://svn.wikimedia.org/viewvc/mediawiki/trunk/phase3/includes/GlobalFunctions.php)
 * This class replaces these functions with some basic (dummy) ones so that this library works without them.
 *
 */

namespace Icap\HtmlDiff;

class WfObject {
    

    /** 
     * Debugging function, is called at the beginning of some methods
     * @param $method
     */
    function wfProfileIn($method)
    {
        
    }

    /**
     * Debugging function, is called at the end of some methods
     *@param $method
     */
    function wfProfileOut($method)
    {
        
    }

    /**
     * Debugging function
     * @param String $msg
     */
    function wfDebug($msg)
    {
        
    }

    /**
     * Looks up a localised message in MediaWiki
     * @param $key
     * @return $key
     */
    function wfMsg($key)
    {
        return $key;
    }

    /**
     * $msg is $key for wfMsgExt, $wfMsgOut is wfMsgExt($key). Returns true when wfMsgExt could not find a message with that
     * @param String $msg
     * @param $wfMsgOut
     * @return true
     */
    function wfEmptyMsg($msg, $wfMsgOut)
    {
        return true;
    }

    /**
     * Looks up some localised message in MediaWiki
     * The message for the key "diff-movedoutof" is "moved out of $1", for "diff-pre" it is "a preformatted block", for example
     * This is used by the HTMLDiff module to describe the changes in words. As we don’t really use that, we just return
     * some example data here or empty string
     */
    function wfMsgExt($key, $options)
    {
        $args = func_get_args();
        //return "wfMsgExt(".implode(", ", $args).")";
        return "";      
    }

    function wfUrlProtocols()
    {
        return "http:\\/\\/|https:\\/\\/|ftp:\\/\\/|irc:\\/\\/|gopher:\\/\\/|telnet:\\/\\/|nntp:\\/\\/|worldwind:\\/\\/|mailto:|news:|svn:\\/\\/|git:\\/\\/|mms:\\/\\/";
    }

}

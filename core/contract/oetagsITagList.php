<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * oetagsTagCloud set interface
 *
 */
interface oetagsITagList
{

    /**
     * Returns cache id, on which tagcloud should cache content.
     * If null is returned, content will not be cached.
     *
     * @return string
     */
    public function getCacheId();

    /**
     * Loads tagcloud set
     *
     * @return boolean
     */
    public function loadList();

    /**
     * Returns tagcloud set
     *
     * @return oetagsTagSet
     */
    public function get();
}

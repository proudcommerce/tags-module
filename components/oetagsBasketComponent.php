<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Main shopping basket manager. Arranges shopping basket
 * contents, updates amounts, prices, taxes etc.
 *
 * @subpackage oxcmp
 */
class oetagsBasketComponent extends oetagsBasketComponent_parent
{
    /**
     * Parameters which are kept when redirecting after user
     * puts something to basket
     *
     * @var array
     */
    public $aRedirectParams = array('searchtag', // search tag
                                   );

}

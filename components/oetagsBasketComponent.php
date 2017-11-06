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
    public $aRedirectParams = ['cnid', // category id
                               'mnid', // manufacturer id
                               'anid', // active article id
                               'tpl', // spec. template
                               'listtype', // list type
                               'searchcnid', // search category
                               'searchvendor', // search vendor
                               'searchmanufacturer', // search manufacturer
                               'searchtag', // search tag
                               // @deprecated since v5.3 (2016-06-17); Listmania will be moved to an own module.
                               'searchrecomm', // search recomendation
                               'recommid' // recomm. list id
                               // END deprecated
    ];
}

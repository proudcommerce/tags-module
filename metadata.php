<?php
/**

 *
 * @category      module
 * @package       tags
 * @author        OXID eSales AG
 * @link          http://www.oxid-esales.com/
 * @copyright (C) OXID eSales AG 2003-20162016
 */

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oetags_ee',
    'title'       => array(
        'de' => 'OE Tags EE',
        'en' => 'OE Tags EE',
    ),
    'description' => array(
        'de' => 'OE Tags Modul EE Addon',
        'en' => 'OE Tags Module EE Addon',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com/',
    'email'       => '',
    'extend'      => array('shop_cache' => 'oe/oetags_ee/controllers/admin/oetagsShopCache',
                           'oxarticle' => 'oe/oetags_ee/models/oetagsArticleEE',
                           'oxconfig' => 'oe/oetags_ee/core/oetagsConfigEE',
                           'oetagsArticleTagList' => 'oe/oetags_ee/models/oetagsArticleTagListEE',
                          ),
    'files'       => array(),
    'templates'   => array(),
    'blocks'      => array(),
    'settings'    => array(),
    'events'      => array(),
);

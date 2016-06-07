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
    'id'          => 'oetags',
    'title'       => array(
        'de' => '[TR - OE Tags]',
        'en' => 'OE Tags',
    ),
    'description' => array(
        'de' => '[TR - OE Tags Module]',
        'en' => 'OE Tags Module',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com/',
    'email'       => '',
    'extend'      => array(
            ),
    'files'       => array(
        'oetagsmodule' => 'oe/tags/core/oetagsmodule.php',
),
    'templates'   => array(
),
    'blocks'      => array(
            ),
    'settings'    => array(
        ),
    'events'      => array(
        'onActivate'   => 'oeTagsModule::onActivate',
        'onDeactivate' => 'oeTagsModule::onDeactivate',
    ),
);
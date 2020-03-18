<?php
/**
 *
 * @category      module
 * @package       tags
 * @author        OXID eSales AG
 * @link          http://www.oxid-esales.com/
 * @copyright (C) OXID eSales AG 2003-2016
 */

/**
 * Metadata version
 */
$sMetadataVersion = '1.2';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oetags',
    'title'       => array(
        'de' => 'OE Tags',
        'en' => 'OE Tags',
    ),
    'description' => array(
        'de' => 'OE Tags Modul',
        'en' => 'OE Tags Module',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '2.3.1',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com/',
    'email'       => '',
    'extend'      => array(
        'article_seo'         => 'oe/oetags/controllers/admin/oetagsArticleSeo',
        'article_main'        => 'oe/oetags/controllers/admin/oetagsArticleMain',
        'oxadminview'         => 'oe/oetags/controllers/admin/oetagsAdminView',
        'oxseoencoderarticle' => 'oe/oetags/models/oetagsSeoEncoderArticle',
        'details'             => 'oe/oetags/controllers/oetagsArticleDetailsController',
        'oxwarticledetails'   => 'oe/oetags/components/widgets/oetagsArticleDetailsWidget',
        'oxarticlelist'       => 'oe/oetags/models/oetagsArticleList',
        'oxsearch'            => 'oe/oetags/models/oetagsSearch',
        'oxviewconfig'        => 'oe/oetags/core/oetagsViewConfig',
        'oxutilscount'        => 'oe/oetags/core/oetagsUtilsCount',
        'oxlocator'           => 'oe/oetags/components/oetagsLocator',
        'oxarticle'           => 'oe/oetags/models/oetagsArticle',
        'oxcmp_basket'        => 'oe/oetags/components/oetagsBasketComponent',
        'oxcmp_categories'    => 'oe/oetags/components/oetagsCategoriesComponent',
    ),
    'files'       => array(
        'oetagsitaglist'       => 'oe/oetags/core/contract/oetagsITagList.php',
        'oetagsmodule'         => 'oe/oetags/core/oetagsmodule.php',
        'oetagsTag'            => 'oe/oetags/models/oetagsTag.php',
        'oetagsTagCloud'       => 'oe/oetags/models/oetagsTagCloud.php',
        'oetagsTagList'        => 'oe/oetags/models/oetagsTagList.php',
        'oetagsTagSet'         => 'oe/oetags/models/oetagsTagSet.php',
        'oetagsSeoEncoderTag'  => 'oe/oetags/models/oetagsSeoEncoderTag.php',
        'oetagsArticleTagList' => 'oe/oetags/models/oetagsArticleTagList.php',
        'oetagsTagCloudWidget' => 'oe/oetags/components/widgets/oetagsTagCloudWidget.php',
        'oetagsTagController'  => 'oe/oetags/controllers/oetagsTagController.php',
        'oetagsTagsController' => 'oe/oetags/controllers/oetagsTagsController.php',
    ),
    'templates'   => array(
        'widget/sidebar/tags.tpl'       => 'oe/oetags/views/widgets/sidebar/tags.tpl',
        'page/details/inc/tags.tpl'     => 'oe/oetags/views/azure/tpl/page/details/inc/tags.tpl',
        'page/oetagstagscontroller.tpl' => 'oe/oetags/views/tpl/page/oetagstagscontroller.tpl',

    ),
    'blocks'      => array(
        array('template' => 'layout/base.tpl', 'block' => 'base_style', 'file' => 'views/blocks/oetags_css.tpl'),

        //admin
        array(
            'template' => 'article_main.tpl',
            'block'    => 'admin_article_main_extended',
            'file'     => 'views/blocks/article_main_extended.tpl',
        ),
        array(
            'template' => 'article_main.tpl',
            'block'    => 'admin_article_main_extended_errorbox',
            'file'     => 'views/blocks/article_main_extended_errorbox.tpl',
        ),
        array(
            'template' => 'object_seo.tpl',
            'block'    => 'object_seo_extended',
            'file'     => 'views/blocks/object_seo_extended.tpl',
        ),
        array(
            'template' => 'object_seo.tpl',
            'block'    => 'object_seo_custreadonly',
            'file'     => 'views/blocks/object_seo_custreadonly.tpl',
        ),

        //azure
        array(
            'theme'    => 'azure',
            'template' => 'layout/sidebar.tpl',
            'block'    => 'sidebar_tags',
            'file'     => 'views/blocks/azure/sidebar_tags.tpl',
        ),
        array(
            'theme'    => 'azure',
            'template' => 'page/details/inc/tabs.tpl',
            'block'    => 'details_tabs_tags',
            'file'     => 'views/blocks/azure/details_tabs_tags.tpl',
        ),
        array(
            'theme'    => 'azure',
            'template' => 'widget/sidebar/categorytree.tpl',
            'block'    => 'categorytree_extended',
            'file'     => 'views/blocks/azure/categorytree_tagcloud.tpl',
        ),
        array(
            'theme'    => 'azure',
            'template' => 'page/oetagstagscontroller.tpl',
            'block'    => 'page_oetagscontroller',
            'file'     => 'views/blocks/azure/page_oetagscontroller.tpl',
        ),

        //flow
        array(
            'theme'    => 'flow',
            'template' => 'layout/sidebar.tpl',
            'block'    => 'sidebar_tags',
            'file'     => 'views/blocks/flow/sidebar_tags.tpl',
        ),
        array(
            'theme'    => 'flow',
            'template' => 'page/details/inc/tabs.tpl',
            'block'    => 'details_tabs_tags',
            'file'     => 'views/blocks/flow/details_tabs_tags.tpl',
        ),
        array(
            'theme'    => 'flow',
            'template' => 'page/oetagstagscontroller.tpl',
            'block'    => 'page_oetagscontroller',
            'file'     => 'views/blocks/flow/page_oetagscontroller.tpl',
        ),

        //wave
        // no sidebar here
        array(
            'theme'    => 'wave',
            'template' => 'page/details/inc/tabs.tpl',
            'block'    => 'details_tabs_tags',
            'file'     => 'views/blocks/wave/details_tabs_tags.tpl',
        ),
        array(
            'theme'    => 'wave',
            'template' => 'page/oetagstagscontroller.tpl',
            'block'    => 'page_oetagscontroller',
            'file'     => 'views/blocks/wave/page_oetagscontroller.tpl',
        ),

        //custom theme (note: structure needs to be as wave or make own tags module that overrides this module settings)
        array(
            'template' => 'page/details/inc/tabs.tpl',
            'block'    => 'details_tabs_tags',
            'file'     => 'views/blocks/wave/details_tabs_tags.tpl',
        ),
        array(
            'template' => 'page/oetagstagscontroller.tpl',
            'block'    => 'page_oetagscontroller',
            'file'     => 'views/blocks/wave/page_oetagscontroller.tpl',
        ),

    ),
    'settings'    => array(
        array('group' => 'main', 'name' => 'oetagsShowTags', 'type' => 'bool', 'value' => '1'),
        array('group' => 'main', 'name' => 'oetagsSeparator', 'type' => 'str', 'value' => ','),
        array('group' => 'main', 'name' => 'oetagsUri', 'type' => 'str', 'value' => 'tag'),
        //enter seo url name option, how to multi lang?
        array(
            'group' => 'main',
            'name'  => 'oetagsMetaDescription',
            'type'  => 'str',
            'value' => 'Tags Meta Description',
        ),
        array('group' => 'main', 'name' => 'oetagsMetaKeywords', 'type' => 'str', 'value' => 'keywords'),
    ),
    'events'      => array(
        'onActivate'   => 'oeTagsModule::onActivate',
        'onDeactivate' => 'oeTagsModule::onDeactivate',
    ),
);

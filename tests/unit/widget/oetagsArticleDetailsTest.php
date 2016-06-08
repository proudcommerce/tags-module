<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';

use \oxArticle;
use \oxArticleList;
use \oxField;
use \oxDb;
use \oxTestModules;

/**
 * Tests for oxwArticleBox class
 */
class Unit_Widget_ArticleDetailsTest extends \oeTagsTestCase
{
    public function testCanChangeTags_nouser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser');

        $this->assertFalse($oView->canChangeTags());
    }

    public function testCanChangeTags_withuser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(true));

        $this->assertTrue($oView->canChangeTags());
    }

    /**
     * Test get tag cloud after adding new tags.
     *
     * @return null
     */
    public function testGetTagCloudManagerAfterAddTags()
    {
        oxTestModules::addFunction('oetagsSeoEncoderTag', '_saveToDb', '{return null;}');
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
        $this->setRequestParameter('newTags', "newTag");
        $oArt = oxNew('oxArticle');
        $oArt->load('2000');
        $oArt->setId('_testArt');
        $oArt->save();

        $oArticle = oxNew('oxArticle');
        $oArticle->load('_testArt');

        $oDetails = $this->getProxyClass('oxwArticleDetails');
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $this->assertTrue($oDetails->getTagCloudManager() instanceof oetagsTagCloud);
    }

    public function testIsEditableTags()
    {
        $oView = $this->getMock($this->getProxyClassName('oxwArticleDetails'), array('getProduct', 'getUser'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue(true));
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(true));

        $this->assertTrue($oView->isEditableTags());
        $this->assertTrue($oView->getNonPublicVar('_blCanEditTags'));
    }

    /**
     * Test get link type.
     *
     * @return null
     */
    public function testGetLinkType()
    {
        $this->setRequestParameter('listtype', 'vendor');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_VENDOR, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', 'manufacturer');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_MANUFACTURER, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', 'tag');
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_TAG, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', null);
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue(null));
        $this->assertEquals(OXARTICLE_LINKTYPE_CATEGORY, $oDetailsView->getLinkType());

        $oCategory = $this->getMock("oxcategory", array('isPriceCategory'));
        $oCategory->expects($this->once())->method('isPriceCategory')->will($this->returnValue(true));

        $this->setRequestParameter('listtype', "recommlist");
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_RECOMM, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', null);
        $oDetailsView = $this->getMock("oxwArticleDetails", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_PRICECATEGORY, $oDetailsView->getLinkType());
    }

    public function testGetTagSeparator()
    {
        $oConfig = $this->getMock('oxConfig', array('getConfigParam'));
        $oConfig->expects($this->once())->method('getConfigParam')->with($this->equalTo('oetagsSeparator'))->will($this->returnValue('test_separator'));

        $oView = $this->getMock('oxwArticleDetails', array('getConfig'));
        $oView->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertSame('test_separator', $oView->getTagSeparator());
    }



}

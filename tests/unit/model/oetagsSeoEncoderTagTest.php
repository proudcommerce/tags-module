<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Testing oetagsSeoEncoderTag class
 */
class Unit_Model_SeoEncoderTagTest extends \oeTagsTestCase
{
    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');
        oxDb::getDb()->execute('delete from oxseohistory');

        parent::tearDown();
    }

    public function testGetTagUri()
    {
        $oEncoder = $this->getMock('oetagsSeoEncoderTag', array('_getDynamicTagUri', 'getStdTagUri'));
        $oEncoder->expects($this->once())->method('getStdTagUri')->will($this->returnValue('stdTagUri'));
        $oEncoder->expects($this->once())->method('_getDynamicTagUri')->with($this->equalTo('stdTagUri'), "oetagstagcontroller/sTag/", 999)->will($this->returnValue('seoTagUri'));

        $this->assertEquals('seoTagUri', $oEncoder->getTagUri('sTag', 999));
    }

    public function testGetStdTagUri()
    {
        $oEncoder = oxNew('oetagsSeoEncoderTag');
        $this->assertEquals("index.php?cl=oetagstagcontroller&amp;searchtag=sTag", $oEncoder->getStdTagUri('sTag'));
    }

    public function testGetTagUrl()
    {
        $iLang = oxRegistry::getLang()->getBaseLanguage();
        $sTag = 'sTag';

        $oEncoder = $this->getMock('oetagsSeoEncoderTag', array('_getFullUrl', 'getTagUri'));
        $oEncoder->expects($this->once())->method('getTagUri')->with($this->equalTo($sTag), $this->equalTo($iLang))->will($this->returnValue('seoTagUri'));
        $oEncoder->expects($this->once())->method('_getFullUrl')->with($this->equalTo('seoTagUri'), $iLang)->will($this->returnValue('seoTagUrl'));

        $this->assertEquals('seoTagUrl', $oEncoder->getTagUrl($sTag));
    }

    public function testGetTagPageUrl()
    {
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        $sUrl = $this->getConfig()->getShopUrl(oxRegistry::getLang()->getBaseLanguage());

        $sTag = $this->getTestConfig()->getShopEdition() == 'EE' ? 'hingucker' : 'erste';
        $sAltTag = $this->getTestConfig()->getShopEdition() == 'EE' ? 'grilltonne' : 'authentisches';

        $oSeoEncoderTag = oxNew('oetagsSeoEncoderTag');
        $this->assertEquals($sUrl . "oetagstagcontroller/{$sTag}/16/", $oSeoEncoderTag->getTagPageUrl($sTag, 15));
        $this->assertEquals($sUrl . "oetagstagcontroller/{$sTag}/16/", $oSeoEncoderTag->getTagPageUrl($sTag, 15));

        $this->assertEquals($sUrl . "oetagstagcontroller/{$sAltTag}/14/", $oSeoEncoderTag->getTagPageUrl($sAltTag, 13));
    }

    /**
     * oetagsSeoEncoderTag::_getDynamicTagUri() test case
     *
     * @return null
     */
    public function testGetDynamicTagUriExistsInDb()
    {
        $oEncoder = $this->getMock("oetagsSeoEncoderTag", array("_trimUrl", "getDynamicObjectId", "_prepareUri", "_loadFromDb", "_copyToHistory", "_processSeoUrl", "_saveToDb"));
        $oEncoder->expects($this->once())->method('_trimUrl')->with($this->equalTo("testStdUrl"))->will($this->returnValue("testStdUrl"));
        $oEncoder->expects($this->once())->method('getDynamicObjectId')->with($this->equalTo($this->getConfig()->getShopId()), $this->equalTo("testStdUrl"))->will($this->returnValue("testId"));
        $oEncoder->expects($this->once())->method('_prepareUri')->with($this->equalTo("testSeoUrl"))->will($this->returnValue("testSeoUrl"));
        $oEncoder->expects($this->once())->method('_loadFromDb')->with($this->equalTo('dynamic'), $this->equalTo("testId"), $this->equalTo(0))->will($this->returnValue("testSeoUrl"));
        $oEncoder->expects($this->never())->method('_copyToHistory');
        $oEncoder->expects($this->never())->method('_processSeoUrl');
        $oEncoder->expects($this->never())->method('_saveToDb');

        $this->assertEquals("testSeoUrl", $oEncoder->UNITgetDynamicTagUri("testStdUrl", "testSeoUrl", 0, "testId"));
    }

    public function testGetDynamicTagUriCreatingNew()
    {
        $sTag = $this->getTestConfig()->getShopEdition() == 'EE' ? 'schon' : 'zauber';

        $sOxid = "1126";

        $oEncoder = $this->getMock("oetagsSeoEncoderTag", array("_trimUrl", "getDynamicObjectId", "_prepareUri", "_loadFromDb", "_copyToHistory", "_processSeoUrl", "_saveToDb"));
        $oEncoder->expects($this->once())->method('_trimUrl')->with($this->equalTo("testStdUrl"))->will($this->returnValue("testStdUrl"));
        $oEncoder->expects($this->once())->method('getDynamicObjectId')->with($this->equalTo($this->getConfig()->getShopId()), $this->equalTo("testStdUrl"))->will($this->returnValue($sOxid));
        $oEncoder->expects($this->once())->method('_prepareUri')->with($this->equalTo("testSeoUrl"))->will($this->returnValue("testSeoUrl"));
        $oEncoder->expects($this->once())->method('_loadFromDb')->with($this->equalTo('dynamic'), $this->equalTo($sOxid), $this->equalTo(0))->will($this->returnValue("testSeoUrl1"));
        $oEncoder->expects($this->once())->method('_copyToHistory')->with($this->equalTo($sOxid), $this->equalTo($this->getConfig()->getShopId()), $this->equalTo(0), $this->equalTo('dynamic'));
        $oEncoder->expects($this->once())->method('_processSeoUrl')->with($this->equalTo("testSeoUrl"), $this->equalTo($sOxid), $this->equalTo(0))->will($this->returnValue("testSeoUrl"));
        $oEncoder->expects($this->once())->method('_saveToDb')->with($this->equalTo("dynamic"), $this->equalTo($sOxid), $this->equalTo("testStdUrl"), $this->equalTo('testSeoUrl'), $this->equalTo(0), $this->equalTo($this->getConfig()->getShopId()));

        $this->assertEquals("testSeoUrl", $oEncoder->UNITgetDynamicTagUri("testStdUrl", "testSeoUrl", 0));
    }
}

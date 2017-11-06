<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Class TagCloudTest
 */
class Unit_Model_oetagsTagCloudTest extends \oeTagsTestCase
{

    /**
     * Test getting font size for tag
     */
    public function testGetFontSizeCustom()
    {
        $aTestData = array(
            "sTestTag1" => new oetagsTag("sTestTag1"),
            "sTestTag2" => new oetagsTag("sTestTag2"),
            "sTestTag3" => new oetagsTag("sTestTag3"),
            "sTestTag4" => new oetagsTag("sTestTag4"),
            "sTestTag5" => new oetagsTag("sTestTag5"),
            "sTestTag6" => new oetagsTag("sTestTag6"),
            "sTestTag7" => new oetagsTag("sTestTag7")
        );
        $aTestData["sTestTag1"]->setHitCount(20);
        $aTestData["sTestTag2"]->setHitCount(17);
        $aTestData["sTestTag3"]->setHitCount(13);
        $aTestData["sTestTag4"]->setHitCount(15);
        $aTestData["sTestTag5"]->setHitCount(12);
        $aTestData["sTestTag6"]->setHitCount(1);
        $aTestData["sTestTag7"]->setHitCount(5);

        $aTestResults = array(
            "sTestTag1" => 400,
            "sTestTag2" => 300,
            "sTestTag3" => 200,
            "sTestTag4" => 300,
            "sTestTag5" => 200,
            "sTestTag6" => 100,
            "sTestTag7" => 100
        );
        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setCloudArray($aTestData);

        foreach ($aTestResults as $sTag => $iVal) {
            $this->assertEquals($iVal, $oTagCloud->getTagSize($sTag));
        }
    }

    /* Should be tested by testGetFontSizeCustom
    public function testGetFontSize()
    {
        $oTagCloud = $this->getProxyClass('oetagsTagCloud');
        $this->assertEquals(285, $oTagCloud->UNITgetFontSize(10, 15));
        $this->assertEquals(250, $oTagCloud->UNITgetFontSize(10, 18));
        $this->assertEquals(700, $oTagCloud->UNITgetFontSize(18, 10));
    }
    public function testGetFontSizeExceptionalCases()
    {
        $oTagCloud = $this->getProxyClass('oetagsTagCloud');
        $this->assertEquals(OETAGCLOUD_MINFONT, $oTagCloud->UNITgetFontSize(15, 2));
        $this->assertEquals(OETAGCLOUD_MINFONT, $oTagCloud->UNITgetFontSize(15, 0));
        $this->assertEquals(OETAGCLOUD_MINFONT, $oTagCloud->UNITgetFontSize(15, 1));
        $this->assertEquals(OETAGCLOUD_MINFONT, $oTagCloud->UNITgetFontSize(-1, 10));
    }*/

    /**
     * Test formation of tagCloud array
     *
     * @return null
     */
    public function testGetCloudArray()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->set("tag1,tag2");

        $oTagList = $this->getMock('oetagstaglist', array('getCacheId', 'loadList', 'get'));
        $oTagList->expects($this->any())->method('getCacheId')->will($this->returnValue(null));
        $oTagList->expects($this->any())->method('loadList')->will($this->returnValue(true));
        $oTagList->expects($this->any())->method('get')->will($this->returnValue($oTagSet));

        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        $aTags = array(
            "tag1" => new oetagsTag("tag1"),
            "tag2" => new oetagsTag("tag2"),
        );

        $this->assertEquals($aTags, $oTagCloud->getCloudArray());
    }

    /**
     * Test setting of extended mode
     */
    public function testSettingExtendedMode()
    {
        $oTagCloud = oxNew('oetagsTagCloud');

        $oTagCloud->setExtendedMode(true);
        $this->assertTrue($oTagCloud->isExtended());

        $oTagCloud->setExtendedMode(false);
        $this->assertFalse($oTagCloud->isExtended());
    }

    /**
     * Test tags cloud generating when extended mode is enabled or disabled
     */
    public function testGetCloudArrayInExtendedMode()
    {
        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setExtendedMode(true);
        $iMaxAmount = $oTagCloud->GetMaxAmount() + 10;

        $oTagSet = oxNew('oetagsTagSet');
        for ($i = 1; $i < $iMaxAmount; $i++) {
            $oTagSet->addTag('tag' . $i);
        }

        $oTagList = $this->getMock('oetagstaglist', array('getCacheId', 'loadList', 'get'));
        $oTagList->expects($this->any())->method('getCacheId')->will($this->returnValue(null));
        $oTagList->expects($this->any())->method('loadList')->will($this->returnValue(true));
        $oTagList->expects($this->any())->method('get')->will($this->returnValue($oTagSet));

        $oTagCloud->setTagList($oTagList);

        // should be taken from db
        $this->assertEquals($oTagCloud->GetMaxAmount(), count($oTagCloud->getCloudArray()));

        $oTagCloud->setExtendedMode(false);

        $this->assertEquals($oTagCloud->GetMaxAmount(), count($oTagCloud->getCloudArray()));
    }

    /**
     * Test getting max articles amount
     */
    public function testGetMaxAmount()
    {
        $oTagCloud = oxNew('oetagsTagCloud');

        $oTagCloud->setExtendedMode(true);
        $this->assertEquals(OETAGCLOUD_EXTENDEDCOUNT, $oTagCloud->GetMaxAmount());

        $oTagCloud->setExtendedMode(false);
        $this->assertEquals(OETAGCLOUD_STARTPAGECOUNT, $oTagCloud->GetMaxAmount());
    }

    /**
     * Test setting and resetting cache of tagCloudArray
     */
    public function testTagCache()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->add('tag1,tag2');

        $oTagList = $this->getMock('oetagstaglist', array('getCacheId', 'loadList', 'get'));
        $oTagList->expects($this->any())->method('getCacheId')->will($this->returnValue("cacheId_1"));
        // Load list should be called first time and after reset
        $oTagList->expects($this->exactly(2))->method('loadList')->will($this->returnValue(true));
        $oTagList->expects($this->any())->method('get')->will($this->returnValue($oTagSet));

        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        $aTags = array(
            "tag1" => new oetagsTag("tag1"),
            "tag2" => new oetagsTag("tag2"),
        );

        // should be taken from db
        $this->assertEquals($aTags, $oTagCloud->getCloudArray());

        // Set new oetagsTagCloud, to reset the local class caching
        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        // should be taken from cache, loadList should not be called
        $this->assertEquals($aTags, $oTagCloud->getCloudArray());

        $oTagCloud->resetCache();

        // Set new oetagsTagCloud, to reset the local class caching
        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        // should be taken from db again, because we resetted cache.
        $this->assertEquals($aTags, $oTagCloud->getCloudArray());
    }

    /**
     * Test not using cache when cacheId is null
     */
    public function testTagCacheWithCacheIdNull()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->add('tag1,tag2');

        $oTagList = $this->getMock('oetagsTagList', array('getCacheId', 'loadList', 'get'));
        $oTagList->expects($this->any())->method('getCacheId')->will($this->returnValue(null));
        $oTagList->expects($this->any())->method('get')->will($this->returnValue($oTagSet));
        // Load list should be called all times, cache should not be used
        $oTagList->expects($this->exactly(2))->method('loadList')->will($this->returnValue(true));

        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        $aTags = array(
            "tag1" => new oetagsTag("tag1"),
            "tag2" => new oetagsTag("tag2"),
        );

        // should be taken from db
        $this->assertEquals($aTags, $oTagCloud->getCloudArray());

        // Set new oetagsTagCloud, to reset the local class caching
        $oTagCloud = oxNew('oetagsTagCloud');
        $oTagCloud->setTagList($oTagList);

        // should be taken from cache, loadList should not be called
        $this->assertEquals($aTags, $oTagCloud->getCloudArray());
    }
}

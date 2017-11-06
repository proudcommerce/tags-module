<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Class TaglistTest
 *
 */
class Unit_Model_oetagsTaglistTest extends \oeTagsTestCase
{

    /**
     * Test loading articles list and returning it
     */
    public function testLoadingAndGettingTagSet()
    {
        $oTagList = oxNew('oetagsTagList');
        $this->assertTrue($oTagList->loadList());
        $oTagSet = $oTagList->get();
        $aTags = $oTagSet->get();

        if ('CE' !== $this->getTestConfig()->getShopEdition()) {
           $this->markTestIncomplete('Tested for CE only');
        } else {
            $keyToCheck = 'anzug';
            $tagsCount = 299;
        }

        $this->assertEquals($tagsCount, count($aTags));
        $this->assertTrue(array_key_exists($keyToCheck, $aTags));
    }

    /**
     * Test adding tags to list
     */
    public function testAddingTagsToList()
    {
        $oTagList = oxNew('oetagsTagList');
        $oTagList->addTag('testTag');
        $oTagSet = $oTagList->get();
        $aExpResult = array('testtag' => new oetagsTag('testTag'));

        $this->assertEquals($aExpResult, $oTagSet->get());
    }

    /**
     * Test usage of english language
     */
    public function testGetTagsEn()
    {
        $oTagList = oxNew('oetagsTagList');
        $oTagList->setLanguage(1);
        $oTagList->loadList();
        $oTagSet = $oTagList->get();

        if ('CE' !== $this->getTestConfig()->getShopEdition()) {
            $this->markTestIncomplete('Tested for CE only');
        } else {
            $iExpt = 181;
        }

        $this->assertEquals($iExpt, count($oTagSet->get()));
    }

    /**
     * Tests cache formation
     */
    public function testgetCacheId()
    {
        $oTagList = oxNew('oetagsTagList');
        $oTagList->setLanguage(1);
        $this->assertEquals('tag_list_1', $oTagList->getCacheId());
        $oTagList->setLanguage(2);
        $this->assertEquals('tag_list_2', $oTagList->getCacheId());
    }

}

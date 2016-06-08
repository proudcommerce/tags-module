<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';


/**
 * Class TagsetTest
 *
 */
class Unit_Model_oetagsTagsetTest extends \oeTagsTestCase
{
    /**
     * Test setting and getting separator
     */
    public function testSetGetSeparator()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->setSeparator("|");
        $this->assertEquals("|", $oTagSet->getSeparator());
    }

    /**
     * Test if not default separator is used for tags generation
     */
    public function testNotDefaultSeparator()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->setSeparator("|");

        $oTag1 = oxNew('oetagsTag');
        $oTag1->set("test1,test2");
        $oTag2 = oxNew('oetagsTag');
        $oTag2->set("test3,test4");

        $aTags = array(
            "test1,test2" => $oTag1,
            "test3,test4" => $oTag2,
        );

        $oTagSet->set($oTag1 . "|" . $oTag2);
        $this->assertEquals("test1,test2|test3,test4", $oTagSet->formString());
        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test setting and getting of tags set.
     *
     * @return null
     */
    public function testSetGet()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1,test2,test3");

        $aTags = array(
            "test1" => new oetagsTag("test1"),
            "test2" => new oetagsTag("test2"),
            "test3" => new oetagsTag("test3"),
        );

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test adding of multiple tags
     *
     * @return null
     */
    public function testAdd()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->add("test1,test2");
        $oTagSet->add("test3,test4");

        $aTags = array(
            "test1" => new oetagsTag("test1"),
            "test2" => new oetagsTag("test2"),
            "test3" => new oetagsTag("test3"),
            "test4" => new oetagsTag("test4"),
        );

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test adding of single tags
     *
     * @return null
     */
    public function testAddTag()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->addTag("test1");
        $oTagSet->addTag("test2");

        $aTags = array(
            "test1" => new oetagsTag("test1"),
            "test2" => new oetagsTag("test2"),
        );

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test adding of single not valid tags and checking if they get to invalid tags list
     *
     * @return null
     */
    public function testAddTagNotValid()
    {
        $oTagSet = oxNew('oetagsTagSet');

        // empty strings should not be stored in invalid tags list
        $oTagSet->addTag("");
        $oTagSet->addTag("admin");
        $oTagSet->addTag("validtag");

        $aTags = array("validtag" => new oetagsTag("validtag"));
        $aInvalidTags = array("admin" => new oetagsTag("admin"));

        $this->assertEquals($aTags, $oTagSet->get());
        $this->assertEquals($aInvalidTags, $oTagSet->getInvalidTags());
    }

    /**
     * Getting invalid tags when all tags were valid
     *
     * @return null
     */
    public function testInvalidTagsWithAllValidTags()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->addTag("validtag");
        $this->assertEquals(array(), $oTagSet->getInvalidTags());
    }

    /**
     * Test adding tag as oetagsTag object
     *
     * @return null
     */
    public function testAddTagObject()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTag1 = new oetagsTag("test1");
        $oTag2 = new oetagsTag("test2");

        $oTagSet->addTag($oTag1);
        $oTagSet->addTag($oTag2);

        $aTags = array(
            "test1" => $oTag1,
            "test2" => $oTag2,
        );

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test adding multiple tags with repeating value
     *
     * @return null
     */
    public function testAddRepeatingTags()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1,  test2  ,test1 ");
        $oTagSet->add("test2, test2 , test2");
        $oTagSet->addTag(" test1 ");
        $oTag1 = new oetagsTag("test1");
        $oTag1->setHitCount(3);
        $oTag2 = new oetagsTag("test2");
        $oTag2->setHitCount(4);
        $aTags = array("test1" => $oTag1, "test2" => $oTag2);

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test adding of invalid tags
     *
     * @return null
     */
    public function testAddInvalidTags()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("");
        $oTagSet->add(",,,,");
        $oTagSet->addTag("");

        $this->assertEquals(array(), $oTagSet->get());
    }

    /**
     * Test clearing of tags in set
     *
     * @return null
     */
    public function testClear()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1,test2");
        $this->assertEquals("test1,test2", $oTagSet->formString());
        $oTagSet->clear();
        $this->assertEquals(array(), $oTagSet->get());
    }

    /**
     * Using tags set object as string should work
     *
     * @return null
     */
    public function testFormingTagsString()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1, test2, test2  , test1 ");
        $this->assertEquals("test1,test1,test2,test2", $oTagSet->formString());
    }

    /**
     * Using tags set object as string should work
     *
     * @return null
     */
    public function testTagSetUsingAsString()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1, test2, test2  , test1 ");
        $this->assertEquals("Result: test1,test1,test2,test2", 'Result: ' . $oTagSet);
    }

    /**
     * Testing tagset slicing
     *
     * @return null
     */
    public function testTagSetSlicing()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("test1, test2, test2  , test1, test3 ");
        $oTagSet->slice(0, 2);

        $oTag1 = new oetagsTag("test1");
        $oTag1->setHitCount(2);
        $oTag2 = new oetagsTag("test2");
        $oTag2->setHitCount(2);
        $aTags = array("test1" => $oTag1, "test2" => $oTag2);

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Testing tagset sorting
     *
     * @return null
     */
    public function testTagSetSort()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("btag,ctag,atag,1tag");
        $oTagSet->sort();

        $aTags = array(
            "1tag" => new oetagsTag('1tag'),
            "atag" => new oetagsTag('atag'),
            "btag" => new oetagsTag('btag'),
            "ctag" => new oetagsTag('ctag'),
        );

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Testing tagset sorting by tags hitcount
     *
     * @return null
     */
    public function testTagSetSortByHitCount()
    {
        $oTagSet = oxNew('oetagsTagSet');

        $oTagSet->set("atag,ctag,ctag,ctag,dtag,dtag,dtag,dtag,btag,btag");
        $oTagSet->sortByHitCount();

        $aTags = array(
            "dtag" => new oetagsTag('dtag'),
            "ctag" => new oetagsTag('ctag'),
            "btag" => new oetagsTag("btag"),
            "atag" => new oetagsTag("atag"),
        );
        $aTags["dtag"]->setHitCount(4);
        $aTags["ctag"]->setHitCount(3);
        $aTags["btag"]->setHitCount(2);
        $aTags["atag"]->setHitCount(1);

        $this->assertEquals($aTags, $oTagSet->get());
    }

    /**
     * Test implementation of ArrayAccess on oetagsTagSet
     */
    public function testIterator()
    {
        $oTagSet = oxNew('oetagsTagSet');
        $oTagSet->set("test1,test2");

        $aTags = array();
        foreach ($oTagSet as $iKey => $oTag) {
            $aTags[$iKey] = $oTag;
        }

        $this->assertEquals($aTags, $oTagSet->get());
    }
}

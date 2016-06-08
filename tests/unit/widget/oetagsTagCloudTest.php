<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';

/**
 * Tests for oxwTagCloud class
 */
class Unit_Widget_TagCloudTest extends \oeTagsTestCase
{

    /**
     * Testing oxwTagCloud::getTagCloudManager()
     *
     * @return null
     */
    public function testGetTagCloudManager()
    {
        $tagCloud = oxNew('oetagsTagCloudWidget');
        $this->assertTrue($tagCloud->getTagCloudManager() instanceof oetagsTagCloud);
    }

    /**
     * Testing oxwTagCloud::render()
     *
     * @return null
     */
    public function testRender()
    {
        $tagCloud = oxNew('oetagsTagCloudWidget');
        $this->assertEquals('widget/sidebar/tags.tpl', $tagCloud->render());
    }

    /**
     * Testing oxwTagCloud::displayInBox()
     *
     * @return null
     */
    public function testDisplayInBox()
    {
        $tagCloud = oxNew('oetagsTagCloudWidget');
        $tagCloud->setViewParameters(array("blShowBox" => 1));
        $this->assertTrue($tagCloud->displayInBox());
    }

    /**
     * Testing oxwTagCloud::isMoreTagsVisible()
     *
     * @return null
     */
    public function testIsMoreTagsVisible()
    {
        $tagCloud = oxNew('oetagsTagCloudWidget');
        $this->assertTrue($tagCloud->isMoreTagsVisible());
    }

}

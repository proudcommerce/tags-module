<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';


class Unit_Controller_oetagsTagsControllerTest extends \oeTagsTestCase
{

    /**
     * Testing Tags::render()
     *
     * @return null
     */
    public function testRender()
    {
        $view = oxNew('oetagsTagsController');
        $view->init();
        $this->assertEquals('page/oetagstagscontroller.tpl', $view->render());
    }

    /**
     * Testing Tags::getTagCloudManager()
     *
     * @return null
     */
    public function testGetTagCloudManager()
    {
        $view = oxNew('oetagsTagsController');
        $view->init();
        $this->assertTrue($view->getTagCloudManager() instanceof oetagsTagCloud);
    }

    /**
     * Testing Tags::getTitleSuffix()
     *
     * @return null
     */
    public function testGetTitleSuffix()
    {
        $view = oxNew('oetagsTagsController');
        $view->init();
        $this->assertNull($view->getTitleSuffix());
    }

    /**
     * Testing Tags::getTitlePageSuffix()
     *
     * @return null
     */
    public function testGetTitlePageSuffix()
    {
        $view = $this->getMock("oetagsTagsController", array("getActPage"));
        $view->expects($this->once())->method('getActPage')->will($this->returnValue(1));
        $this->assertEquals(oxRegistry::getLang()->translateString('PAGE') . " " . 2, $view->getTitlePageSuffix());
    }

    /**
     * Testing tags::getBreadCrumb()
     *
     * @return null
     */
    public function testGetBreadCrumb()
    {
        $oTags = oxNew('oetagsTagsController');

        $this->assertEquals(1, count($oTags->getBreadCrumb()));
    }
}

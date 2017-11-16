<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

class oetagsBasketComponentTest extends \OxidTestCase
{
    /**
     * Verify that redirect url contains the searchtag parameter.
     */
    public function testGetRedirectUrl()
    {
        foreach (array(
                     'cnid', // category id
                     'mnid', // manufacturer id
                     'anid', // active article id
                     'tpl', // spec. template
                     'listtype', // list type
                     'searchcnid', // search category
                     'searchvendor', // search vendor
                     'searchmanufacturer', // search manufacturer
                     'searchtag', // search tag
                     'searchrecomm', // search recomendation
                     'recommid' // recomm. list id
                 ) as $key) {
            $this->setRequestParameter($key, 'value:' . $key . ":v");
        }

        $this->setRequestParameter('cl', 'cla');
        $this->setRequestParameter('searchparam', 'search&&a');
        $this->setRequestParameter('pgNr', 123);


        $oCfg = $this->getMock('stdclass', array('getConfigParam'));
        $oCfg->expects($this->at(0))->method('getConfigParam')->with($this->equalTo('iNewBasketItemMessage'))->will($this->returnValue(0));
        $oCfg->expects($this->at(1))->method('getConfigParam')->with($this->equalTo('iNewBasketItemMessage'))->will($this->returnValue(0));
        $oCfg->expects($this->at(2))->method('getConfigParam')->with($this->equalTo('iNewBasketItemMessage'))->will($this->returnValue(3));

        $o = $this->getMock('oxcmp_basket', array('getConfig'));
        $o->expects($this->exactly(3))->method('getConfig')->will($this->returnValue($oCfg));

        $this->assertEquals('cla?cnid=value:cnid:v&mnid=value:mnid:v&anid=value:anid:v&tpl=value:tpl:v&listtype=value:listtype:v&searchcnid=value:searchcnid:v&searchvendor=value:searchvendor:v&searchmanufacturer=value:searchmanufacturer:v&searchtag=value:searchtag:v&searchrecomm=value:searchrecomm:v&recommid=value:recommid:v&searchparam=search%26%26a&pgNr=123&', $o->UNITgetRedirectUrl());

        $this->setRequestParameter('cl', null);
        $this->setRequestParameter('pgNr', 'a123');
        $this->assertEquals('start?cnid=value:cnid:v&mnid=value:mnid:v&anid=value:anid:v&tpl=value:tpl:v&listtype=value:listtype:v&searchcnid=value:searchcnid:v&searchvendor=value:searchvendor:v&searchmanufacturer=value:searchmanufacturer:v&searchtag=value:searchtag:v&searchrecomm=value:searchrecomm:v&recommid=value:recommid:v&searchparam=search%26%26a&', $o->UNITgetRedirectUrl());

        $this->assertEquals(null, oxRegistry::getSession()->getVariable('_backtoshop'));

        $this->setRequestParameter('pgNr', '0');
        $this->assertEquals('basket?cnid=value:cnid:v&mnid=value:mnid:v&anid=value:anid:v&tpl=value:tpl:v&listtype=value:listtype:v&searchcnid=value:searchcnid:v&searchvendor=value:searchvendor:v&searchmanufacturer=value:searchmanufacturer:v&searchtag=value:searchtag:v&searchrecomm=value:searchrecomm:v&recommid=value:recommid:v&searchparam=search%26%26a&', $o->UNITgetRedirectUrl());
        $this->assertEquals('start?cnid=value:cnid:v&mnid=value:mnid:v&anid=value:anid:v&tpl=value:tpl:v&listtype=value:listtype:v&searchcnid=value:searchcnid:v&searchvendor=value:searchvendor:v&searchmanufacturer=value:searchmanufacturer:v&searchtag=value:searchtag:v&searchrecomm=value:searchrecomm:v&recommid=value:recommid:v&searchparam=search%26%26a&', oxRegistry::getSession()->getVariable('_backtoshop'));
    }
}

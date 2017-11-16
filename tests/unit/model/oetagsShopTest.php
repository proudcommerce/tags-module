<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Class TaglistTest
 *
 */
class Unit_Model_oetagsShopTest extends \oeTagsTestCase
{

    public function testGetViewSelect()
    {
        $shop = oxNew('oxShop');
        $shop->load($this->shopId);

        $table = 'oxartextends';

        $select = $shop->UNITgetViewSelect($table, 0);
        $expect = 'oxartextends.oxid as oxid,oxartextends.oxlongdesc as oxlongdesc,oxartextends.oxtimestamp as oxtimestamp,oxartextends.oetags as oetags';
        $this->assertEquals(strtolower($expect), strtolower($select));

        $select = $shop->UNITgetViewSelect($table, 1);
        $expect = 'oxartextends.oxid as oxid,oxartextends.oxlongdesc_1 as oxlongdesc,oxartextends.oxtimestamp as oxtimestamp,oxartextends.oetags_1 as oetags';
        $this->assertEquals(strtolower($expect), strtolower($select));
    }
}

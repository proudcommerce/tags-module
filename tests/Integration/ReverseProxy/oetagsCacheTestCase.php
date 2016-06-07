<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\EshopEnterprise\Tests\Integration\Cache\ReverseProxy\Cache\ReverseProxyTestCase;


abstract class oetagsCacheTestCase extends ReverseProxyTestCase
{
    /**
     * Fixture set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getConfig()->setConfigParam('blOnModuleEventDoNotFlushCache', false);

        $this->installModule('oetags');
        $this->installModule('oetags_ee');

        $dbMetadataHandler = oxNew('oxDbMetaDataHandler');
        $dbMetadataHandler->updateViews();

        $serviceCaller = new \OxidEsales\TestingLibrary\ServiceCaller();
        $serviceCaller->setParameter('importSql', '@' . dirname(__FILE__) . '/../../testdata/EE/testdata.sql');
        $serviceCaller->callService('ShopPreparation', 1);

        $serviceCaller->setParameter('importSql', '@' . dirname(__FILE__) . '/../../testdata/EE/oxartextends.sql');
        $serviceCaller->callService('ShopPreparation', 1);

        oxRegistry::getConfig()->setConfigParam('oetagsShowTags', true);
        oxRegistry::getConfig()->setConfigParam('oetagsSeparator', ',');
        $this->updateSearchColumns(true);
    }

    /**
     * Fixture set up.
     */
    protected function tearDown()
    {
        $this->updateSearchColumns(false);
        $this->removeKeys();
        $this->removeColumns();

        $dbMetadataHandler = oxNew('oxDbMetaDataHandler');
        $dbMetadataHandler->updateViews();

        parent::tearDown();
    }

    /**
     * Add/removes tags column fromsearch columns.
     *
     * @param bool $addTags default true
     */
    public function updateSearchColumns($addTags = true)
    {
        $searchColumns = oxRegistry::getConfig()->getConfigParam('aSearchCols');
        $searchColumns = is_array($searchColumns)? $searchColumns : array();
        $check = array_combine($searchColumns, $searchColumns);

        if ($addTags) {
            $check['oetags'] = 'oetags';
        } else {
            unset($check['oetags']);

        }

        oxRegistry::getConfig()->setConfigParam('aSearchCols', array_values($check));
    }

    /**
     * Add tags columns
     *
     */
    public function addColumns()
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if (!$metaDataHandler->fieldExists('OETAGS', 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` " .
                     " ADD COLUMN `OETAGS` varchar(255) NOT NULL COMMENT 'Tags (multilanguage)'," .
                     " ADD COLUMN `OETAGS_1` varchar(255) NOT NULL," .
                     " ADD COLUMN `OETAGS_2` varchar(255) NOT NULL," .
                     " ADD COLUMN `OETAGS_3` varchar(255) NOT NULL";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Add tags columns
     *
     */
    public function addKeys()
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if (!$metaDataHandler->hasIndex('OETAGS', 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` " .
                     " ADD KEY `OETAGS`   (`OETAGS`)," .
                     " ADD KEY `OETAGS_1` (`OETAGS_1`)," .
                     " ADD KEY `OETAGS_2` (`OETAGS_2`)," .
                     " ADD KEY `OETAGS_3` (`OETAGS_3`)";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Database cleaning helper.
     */
    public function removeKeys()
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if ($metaDataHandler->hasIndex('OETAGS', 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` " .
                     " DROP KEY `OETAGS`, " .
                     " DROP KEY `OETAGS_1`, " .
                     " DROP KEY `OETAGS_2`, " .
                     " DROP KEY `OETAGS_3` ";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Remove tags columns
     *
     */
    public function removeColumns()
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if ($metaDataHandler->fieldExists('OETAGS', 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` " .
                     " DROP COLUMN `OETAGS`, " .
                     " DROP COLUMN `OETAGS_1`, " .
                     " DROP COLUMN `OETAGS_2`, " .
                     " DROP COLUMN `OETAGS_3` ";

            oxDb::getDb()->execute($query);
        }
    }


    /**
     * install given module in shop
     *
     * @param string $name
     * @param bool   $activate
     */
    private function installModule($name, $activate = true)
    {
        $module = oxNew('oxModule');
        $module->load($name);
        $moduleCache = oxNew('oxModuleCache', $module);
        $moduleInstaller = oxNew('oxModuleInstaller', $moduleCache);
        $moduleInstaller->deactivate($module);

        if ($activate) {
            $moduleInstaller->activate($module);
        }

        $moduleList = oxNew("oxModuleList");
        $moduleList->getModulesFromDir($this->getConfig()->getModulesDir());

    }
}

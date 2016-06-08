<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

class Unit_Core_oeTagsModuleTest extends \OxidTestCase
{

    /**
     * @var OxidEsales\Eshop\Core\DbMetaDataHandler The database meta data handler we use to get meta information from the database.
     */
    protected $dbMetaDataHandler = null;

    public function __construct()
    {
        parent::__construct();

        $this->dbMetaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');
    }

    /**
     * Test onActivate event.
     */
    public function testOnActivate()
    {
        $this->removeDatabaseModuleThings();

        oeTagsModule::onActivate();

        $this->assertMultiLanguageColumnsExistAndAreKeys();

        $this->removeDatabaseModuleThings();

        $this->assertTrue(in_array('oetags', oxRegistry::getConfig()->getConfigParam('aSearchCols')));

        $seoEncoder = oxNew('oxSeoEncoder');
        $this->assertNotEmpty($seoEncoder->getStaticUrl('index.php?cl=oetagstagscontroller', 0));
        $this->assertNotEmpty($seoEncoder->getStaticUrl('index.php?cl=oetagstagscontroller', 1));
    }

    /**
     * Test onActivate event a second time.
     */
    public function testOnActivateSecondTime()
    {
        $this->removeDatabaseModuleThings();
        oeTagsModule::onActivate();

        oeTagsModule::onActivate();

        $this->assertMultiLanguageColumnsExistAndAreKeys();

        $this->removeDatabaseModuleThings();

        $this->assertTrue(in_array('oetags', oxRegistry::getConfig()->getConfigParam('aSearchCols')));
    }

    /**
     * Test onDeactivate event.
     */
    public function testOnDeactivate()
    {
        $this->testOnActivateSecondTime();

        oeTagsModule::onActivate();
        oeTagsModule::onDeactivate();

        $this->assertMultiLanguageColumnsExistAndAreKeys();

        $this->removeDatabaseModuleThings();

        $this->assertFalse(in_array('oetags', oxRegistry::getConfig()->getConfigParam('aSearchCols')));

        $query = "SELECT count(*) from oxseo where oxstdurl like '%oetags%'";
        $this->assertEquals(0, oxDb::getDb()->getOne($query));
    }

    /**
     * Assure, that the module multi language columns exists and are keys.
     */
    protected function assertMultiLanguageColumnsExistAndAreKeys()
    {
        foreach ($this->createMultiLanguageColumnNames() as $columnName) {
            $this->assertDbColumnExists('oxartextends', $columnName);
            $this->assertColumnHasIndex('oxartextends', $columnName);
        }
    }

    /**
     * Create an array of all multi column names.
     *
     * @return array All multi column names for our test setup.
     */
    protected function createMultiLanguageColumnNames()
    {
        $max = $this->dbMetaDataHandler->getCurrentMaxLangId();

        $prefix = 'OETAGS';
        $columnNames = [$prefix];

        for ($index = 1; $index <= $max; $index++) {
            $columnNames[] = $prefix . '_' . $index;
        }

        return $columnNames;
    }

    /**
     * Assure, that the given column exists in the given table.
     *
     * @param string $tableName  The table we want to check.
     * @param string $columnName The name of the column, that should be present in the given table.
     */
    public function assertDbColumnExists($tableName, $columnName)
    {
        $this->assertTrue(
            $this->dbMetaDataHandler->fieldExists($columnName, $tableName),
            "Expected the column '$columnName' to exist in the table '$tableName'"
        );
    }

    /**
     * Assure, that the given column does not exist in the given table.
     *
     * @param string $tableName  The table we want to check.
     * @param string $columnName The name of the column, that should not be present in the given table.
     */
    public function assertDbColumnNotExists($tableName, $columnName)
    {
        $this->assertFalse(
            $this->dbMetaDataHandler->fieldExists($columnName, $tableName),
            "Expected the table '$tableName' to have no column '$columnName'"
        );
    }

    /**
     * Assure, that the given column has an index.
     *
     * @param string $tableName  The table we want to check.
     * @param string $columnName The name of the key, which should be an index.
     */
    protected function assertColumnHasIndex($tableName, $columnName)
    {
        $index = $this->dbMetaDataHandler->getIndexByName($columnName, $tableName);

        $this->assertEquals(
            'BTREE',
            $index['Index_type'],
            "Expected the column '$columnName' to be an index in the table '$tableName'"
        );
    }

    /**
     * Remove everything the module adds to the database.
     */
    protected function removeDatabaseModuleThings()
    {
        $this->dropKey('OETAGS');
        $this->dropKey('OETAGS_1');
        $this->dropKey('OETAGS_2');
        $this->dropKey('OETAGS_3');

        $this->dropColumn('OETAGS');
        $this->dropColumn('OETAGS_1');
        $this->dropColumn('OETAGS_2');
        $this->dropColumn('OETAGS_3');
    }

    /**
     * Drop the given key with the given name.
     *
     * @param string $keyName The name of the column key.
     */
    protected function dropKey($keyName)
    {
        if ($this->dbMetaDataHandler->hasIndex($keyName, 'oxartextends')) {
            oxDb::getDb()->execute("ALTER TABLE `oxartextends` DROP KEY `$keyName`;");
        }
    }

    /**
     * Drop the given column.
     *
     * @param string $columnName The name of the column to drop.
     */
    protected function dropColumn($columnName)
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if ($metaDataHandler->fieldExists($columnName, 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` DROP COLUMN `$columnName`";

            oxDb::getDb()->execute($query);
        }
    }

}

<?php
/**

 *
 * @category      module
 * @package       tags
 * @author        OXID eSales AG
 * @link          http://www.oxid-esales.com/
 * @copyright (C) OXID eSales AG 2003-2016
 */

/**
 * Class oeTagsModule
 * Handles module setup, provides additional tools and module related helpers.
 *
 * @codeCoverageIgnore
 */
class oeTagsModule extends oxModule
{
    const OETAGS_STATIC_SEO = 'index.php?cl=oetagstagscontroller';

    /**
     * @var OxidEsales\Eshop\Core\DbMetaDataHandler The database meta data handler we to use get meta information from the database.
     */
    protected $dbMetaDataHandler = null;

    /**
     * Class constructor.
     * Sets current module main data and loads the rest module info.
     */
    function __construct()
    {
        $sModuleId = 'oetags';

        $this->setModuleData(
            array(
                'id'          => $sModuleId,
                'title'       => 'OE Tags',
                'description' => 'OE Tags Module',
            )
        );

        $this->load($sModuleId);

        oxRegistry::set('oeTagsModule', $this);
    }


    /**
     * Module activation script.
     *
     * @todo: add note to user to update views or a direct call to update the view(s) (maybe oxartextends is enough to update)
     */
    public static function onActivate()
    {
        self::addColumn('OETAGS', true);
        self::addColumn('OETAGS_1');

        self::addKey('OETAGS');
        self::addKey('OETAGS_1');

        $dbMetaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');
        $dbMetaDataHandler->ensureAllMultiLanguageFields('oxartextends');

        self::updateSearchColumns(true);
        self::handleStaticSeoUrl('install.sql.tpl');
        self::clearTmp();
    }

    /**
     * Module deactivation script.
     *
     * For now an empty method, not sure, if later things are added :)
     */
    public static function onDeactivate()
    {
        self::updateSearchColumns(false);
        self::handleStaticSeoUrl('uninstall.sql.tpl');
        self::clearTmp();
    }

    /**
     * Add the key to the given column of the oxartextends table.
     *
     * @param string $columnName The name of the column, which will be indexed. Also name of the index.
     */
    public static function addKey($columnName)
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if (!$metaDataHandler->hasIndex($columnName, 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` ADD KEY `$columnName` (`$columnName`);";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Drop the key to the given column of the oxartextends table.
     *
     * @param string $columnName The name of the column, which will be no longer anindex. Also name of the index.
     */
    public static function dropKey($columnName)
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if (!$metaDataHandler->hasIndex($columnName, 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` DROP KEY `$columnName`;";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Add a column to the table 'oxartextends'.
     *
     * @param string $columnName The name of the column to add.
     * @param bool   $isFirstMultiLanguageColumn Is this the base column?
     */
    public static function addColumn($columnName, $isFirstMultiLanguageColumn = false)
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if (!$metaDataHandler->fieldExists($columnName, 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` ADD COLUMN `$columnName` varchar(255) NOT NULL";

            if ($isFirstMultiLanguageColumn) {
                $query .= " COMMENT 'Tags (multilanguage)'";
            }

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Remove a column to the table 'oxartextends'.
     *
     * @param string $columnName The name of the column to remove.
     */
    public static function dropColumn($columnName)
    {
        $metaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');

        if ($metaDataHandler->fieldExists($columnName, 'oxartextends')) {
            $query = "ALTER TABLE `oxartextends` DROP COLUMN `$columnName`";

            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Clean temp folder content.
     *
     * @param string $sClearFolderPath Sub-folder path to delete from. Should be a full, valid path inside temp folder.
     *
     * @return boolean
     */
    public static function clearTmp($sClearFolderPath = '')
    {
        $sFolderPath = self::_getFolderToClear($sClearFolderPath);
        $hDirHandler = opendir($sFolderPath);

        if (!empty($hDirHandler)) {
            while (false !== ($sFileName = readdir($hDirHandler))) {
                $sFilePath = $sFolderPath . DIRECTORY_SEPARATOR . $sFileName;
                self::_clear($sFileName, $sFilePath);
            }

            closedir($hDirHandler);
        }

        return true;
    }

    /**
     * Add/removes tags column fromsearch columns.
     *
     * @param bool $addTags default true
     */
    public static function updateSearchColumns($addTags = true)
    {
        $searchColumns = oxRegistry::getConfig()->getConfigParam('aSearchCols');
        $searchColumns = is_array($searchColumns)? $searchColumns : array();
        $check = array_combine($searchColumns, $searchColumns);

        if ($addTags) {
            $check['oetags'] = 'oetags';
        } else {
            unset($check['oetags']);

        }

        oxRegistry::getConfig()->saveShopConfVar('arr', 'aSearchCols', array_values($check));
    }

    /**
     * Add/remove static seo url for tags to table oxseo.
     *
     * @param string $template install.sql.tpl or uninstall.sql.tpl
     */
    public static function handleStaticSeoUrl($template)
    {
        $shopId = oxRegistry::getConfig()->getShopId();
        $seoEncoder = oxNew('oxSeoEncoder');

        $parameters = array('shopid'   => $shopId,
                            'objectid' => $seoEncoder->getDynamicObjectId($shopId, self::OETAGS_STATIC_SEO) );

        $query = self::getQuery($template, $parameters);
        if (!empty($query)){
            oxDb::getDb()->execute($query);
        }
    }

    /**
     * Get translated string by the translation code.
     *
     * @param string  $sCode
     * @param boolean $blUseModulePrefix If True - adds the module translations prefix, if False - not.
     *
     * @return string
     */
    public function translate($sCode, $blUseModulePrefix = true)
    {
        if ($blUseModulePrefix) {
            $sCode = 'OE_TAGS_' . $sCode;
        }

        return oxRegistry::getLang()->translateString($sCode, oxRegistry::getLang()->getBaseLanguage(), false);
    }

    /**
     * Get CMS snippet content by identified ID.
     *
     * @param string $sIdentifier
     * @param bool   $blNoHtml
     *
     * @return string
     */
    public function getCmsContent($sIdentifier, $blNoHtml = true)
    {
        $sValue = '';

        /** @var oxContent|oxI18n $oContent */
        $oContent = oxNew('oxContent');
        $oContent->loadByIdent(trim((string) $sIdentifier));

        if ($oContent->oxcontents__oxcontent instanceof oxField) {
            $sValue = (string) $oContent->oxcontents__oxcontent->getRawValue();
            $sValue = (empty($blNoHtml) ? $sValue : nl2br(strip_tags($sValue)));
        }

        return $sValue;
    }

    /**
     * Get module setting value.
     *
     * @param string  $sModuleSettingName Module setting parameter name (key).
     * @param boolean $blUseModulePrefix  If True - adds the module settings prefix, if False - not.
     *
     * @return mixed
     */
    public function getSetting($sModuleSettingName, $blUseModulePrefix = true)
    {
        if ($blUseModulePrefix) {
            $sModuleSettingName = 'oeTags' . (string) $sModuleSettingName;
        }

        return oxRegistry::getConfig()->getConfigParam((string) $sModuleSettingName);
    }

    /**
     * Get module path.
     *
     * @return string Full path to the module directory.
     */
    public function getPath()
    {
        return oxRegistry::getConfig()->getModulesDir() . 'oe/oetags/';
    }

    /**
     * Get content from file
     *
     * File must be placed in docs directory. If file with your given name + .tpl exist then
     * it will be processed with Smarty with publicly available object of $oModule with
     * this object instance
     *
     * @param string $filename
     * @param array $parameters
     *
     * @return string
     */
    public static function getQuery($filename, $parameters)
    {
        $filePath = dirname(__FILE__) . '/../docs/' . (string) $filename;
        $result = '';
        if (file_exists($filePath)) {
            $smarty = oxRegistry::get('oxUtilsView')->getSmarty();
            foreach ($parameters as $key => $value) {
                $smarty->assign($key, $value);
            }
            $result = $smarty->fetch($filePath);
        }
        return $result;
    }


    /**
     * Install/uninstall event.
     * Executes SQL queries form a file.
     *
     * @param string $sSqlFile      SQL file located in module docs folder (usually install.sql or uninstall.sql).
     * @param string $sFailureError An error message to show on failure.
     *
     * @return bool
     */
    protected static function _dbEvent($sSqlFile, $sFailureError = 'Operation failed: ')
    {
        try {
            $oDb = oxDb::getDb();
            $sSql = file_get_contents(dirname(__FILE__) . '/../docs/' . (string) $sSqlFile);
            $aSql = (array) explode(';', $sSql);

            foreach ($aSql as $sQuery) {
                $sQuery = trim($sQuery, " \n");
                if (!empty($sQuery)) {
                    $oDb->execute($sQuery);
                }
            }
        } catch (Exception $ex) {
            error_log($sFailureError . $ex->getMessage()); die();
        }


        self::clearTmp();

        return true;
    }

    /**
     * Check if provided path is inside eShop `tpm/` folder or use the `tmp/` folder path.
     *
     * @param string $sClearFolderPath
     *
     * @return string
     */
    protected static function _getFolderToClear($sClearFolderPath = '')
    {
        $sTempFolderPath = (string) oxRegistry::getConfig()->getConfigParam('sCompileDir');

        if (!empty($sClearFolderPath) and (strpos($sClearFolderPath, $sTempFolderPath) !== false)) {
            $sFolderPath = $sClearFolderPath;
        } else {
            $sFolderPath = $sTempFolderPath;
        }

        return $sFolderPath;
    }

    /**
     * Check if resource could be deleted, then delete it's a file or
     * call recursive folder deletion if it's a directory.
     *
     * @param string $sFileName
     * @param string $sFilePath
     */
    protected static function _clear($sFileName, $sFilePath)
    {
        if (!in_array($sFileName, array('.', '..', '.gitkeep', '.htaccess'))) {
            if (is_file($sFilePath)) {
                @unlink($sFilePath);
            } else {
                self::clearTmp($sFilePath);
            }
        }
    }
}

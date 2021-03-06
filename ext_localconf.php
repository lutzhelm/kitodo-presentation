<?php

/**
 * (c) Kitodo. Key to digital objects e.V. <contact@kitodo.org>
 *
 * This file is part of the Kitodo and TYPO3 projects.
 *
 * @license GNU General Public License version 3 or later.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
// Define constants.
if (!defined('DEVLOG_SEVERITY_OK')) {
    define('DEVLOG_SEVERITY_OK', -1);
}
if (!defined('DEVLOG_SEVERITY_INFO')) {
    define('DEVLOG_SEVERITY_INFO', 0);
}
if (!defined('DEVLOG_SEVERITY_NOTICE')) {
    define('DEVLOG_SEVERITY_NOTICE', 1);
}
if (!defined('DEVLOG_SEVERITY_WARNING')) {
    define('DEVLOG_SEVERITY_WARNING', 2);
}
if (!defined('DEVLOG_SEVERITY_ERROR')) {
    define('DEVLOG_SEVERITY_ERROR', 3);
}
// Register plugins.
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\AudioPlayer::class, '_audioplayer', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Basket::class, '_basket', 'list_type', false);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Calendar::class, '_calendar', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Collection::class, '_collection', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Feeds::class, '_feeds', 'list_type', false);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\ListView::class, '_listview', 'list_type', false);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Metadata::class, '_metadata', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Navigation::class, '_navigation', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\OaiPmh::class, '_oaipmh', 'list_type', false);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\PageGrid::class, '_pagegrid', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\PageView::class, '_pageview', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Search::class, '_search', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Statistics::class, '_statistics', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\TableOfContents::class, '_tableofcontents', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Toolbox::class, '_toolbox', 'list_type', true);
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Validator::class, '_validator', 'list_type', false);
// Register tools for toolbox plugin.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'] = [];
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\FulltextTool::class, '_fulltexttool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_fulltexttool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.fulltexttool';
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\AnnotationTool::class, '_annotationtool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_annotationtool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.annotationtool';
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\ImageDownloadTool::class, '_imagedownloadtool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_imagedownloadtool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.imagedownloadtool';
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\ImageManipulationTool::class, '_imagemanipulationtool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_imagemanipulationtool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.imagemanipulationtool';
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\PdfDownloadTool::class, '_pdfdownloadtool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_pdfdownloadtool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.pdfdownloadtool';
\Kitodo\Dlf\Hooks\ExtensionManagementUtility::addPItoST43($_EXTKEY, \Kitodo\Dlf\Plugin\Tools\SearchInDocumentTool::class, '_searchindocumenttool', '', true);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Plugin/Toolbox.php']['tools'][\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN($_EXTKEY) . '_searchindocumenttool'] = 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_toolbox.searchindocumenttool';
// Register hooks.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Kitodo\Dlf\Hooks\DataHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \Kitodo\Dlf\Hooks\DataHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['dlf/Classes/Common/MetsDocument.php']['hookClass'][] = \Kitodo\Dlf\Hooks\KitodoProductionHacks::class;
// Register AJAX eID handlers.
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_dlf_search_suggest'] = \Kitodo\Dlf\Plugin\Eid\SearchSuggest::class . '::main';
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_dlf_search_in_document'] = \Kitodo\Dlf\Plugin\Eid\SearchInDocument::class . '::main';
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_dlf_pageview_proxy'] = \Kitodo\Dlf\Plugin\Eid\PageViewProxy::class . '::main';
// Use Caching Framework for Solr queries
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend';
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr']['options']['defaultLifeTime'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_dlf_solr']['options']['defaultLifeTime'] = 87600; // 87600 seconds = 1 day
}
// Register Typoscript user function.
if (\TYPO3_MODE === 'FE') {
    /**
     * docTypeCheck user function to use in Typoscript
     * @example [userFunc = user_dlf_docTypeCheck($type, $pid)]
     *
     * @access public
     *
     * @param string $type The document type string to test for
     * @param int $pid The PID for the metadata definitions
     *
     * @return bool true if document type matches, false if not
     */
    function user_dlf_docTypeCheck(string $type, int $pid): bool
    {
        $hook = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Kitodo\Dlf\Hooks\UserFunc::class);
        return ($hook->getDocumentType($pid) === $type);
    }
}

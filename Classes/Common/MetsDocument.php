<?php
namespace Kitodo\Dlf\Common;

use Ubl\Iiif\Tools\IiifHelper;
use Ubl\Iiif\Services\AbstractImageService;

/**
 * (c) Kitodo. Key to digital objects e.V. <contact@kitodo.org>
 *
 * This file is part of the Kitodo and TYPO3 projects.
 *
 * @license GNU General Public License version 3 or later.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * MetsDocument class for the 'dlf' extension.
 *
 * @author	Sebastian Meyer <sebastian.meyer@slub-dresden.de>
 * @author	Henrik Lochmann <dev@mentalmotive.com>
 * @package	TYPO3
 * @subpackage	tx_dlf
 * @access	public
 */
final class MetsDocument extends Document
{
    /**
     * This holds the whole XML file as string for serialization purposes
     * @see __sleep() / __wakeup()
     *
     * @var string
     * @access protected
     */
    protected $asXML = '';

    /**
     * This holds the XML file's dmdSec parts with their IDs as array key
     *
     * @var array
     * @access protected
     */
    protected $dmdSec = [];

    /**
     * Are the METS file's dmdSecs loaded?
     * @see $dmdSec
     *
     * @var boolean
     * @access protected
     */
    protected $dmdSecLoaded = FALSE;

    /**
     * The extension key
     *
     * @var	string
     * @access public
     */
    public static $extKey = 'dlf';

    /**
     * This holds the file ID -> USE concordance
     * @see _getFileGrps()
     *
     * @var array
     * @access protected
     */
    protected $fileGrps = [];

    /**
     * Are the file groups loaded?
     * @see $fileGrps
     *
     * @var boolean
     * @access protected
     */
    protected $fileGrpsLoaded = FALSE;

    /**
     * Are the available metadata formats loaded?
     * @see $formats
     *
     * @var boolean
     * @access protected
     */
    protected $formatsLoaded = FALSE;
    
    /**
     * This holds the XML file's METS part as \SimpleXMLElement object
     *
     * @var \SimpleXMLElement
     * @access protected
     */
    protected $mets;
    
    /**
     * This holds the whole XML file as \SimpleXMLElement object
     *
     * @var \SimpleXMLElement
     * @access protected
     */
    protected $xml;
    
    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::establishRecordId()
     */
    protected function establishRecordId($pid) {
        // Check for METS object @ID.
        if (!empty($this->mets['OBJID'])) {
            $this->recordId = (string) $this->mets['OBJID'];
        }
        // Get hook objects.
        $hookObjects = Helper::getHookObjects('Classes/Common/MetsDocument.php');
        // Apply hooks.
        foreach ($hookObjects as $hookObj) {
            if (method_exists($hookObj, 'construct_postProcessRecordId')) {
                $hookObj->construct_postProcessRecordId($this->xml, $this->recordId);
            }
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getDownloadLocation()
     */
    public function getDownloadLocation($id) {
        $fileMimeType = $this->getFileMimeType($id);
        $fileLocation = $this->getFileLocation($id);
        if ($fileMimeType == "application/vnd.kitodo.iiif") {
            $fileLocation = strrpos($fileLocation, "info.json") == strlen($fileLocation) - 9 ? $fileLocation :
            strrpos($fileLocation, "/") == strlen($fileLocation) ? $fileLocation."info.json" : $fileLocation."/info.json";
            $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
            IiifHelper::setUrlReader(IiifUrlReader::getInstance());
            IiifHelper::setMaxThumbnailHeight($conf['iiifThumbnailHeight']);
            IiifHelper::setMaxThumbnailWidth($conf['iiifThumbnailWidth']);
            $service = IiifHelper::loadIiifResource($fileLocation);
            if ($service != null && $service instanceof AbstractImageService) {
                return $service->getImageUrl();
            }
        } elseif ($fileMimeType = "application/vnd.netfpx") {
            $baseURL = $fileLocation.(strpos($fileLocation, "?") === false ? "?" : "");
            // TODO CVT is an optional IIP server capability; in theory, capabilities should be determined in the object request with '&obj=IIP-server'
            return $baseURL."&CVT=jpeg";
        }
        return $fileLocation;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getFileLocation()
     */
    public function getFileLocation($id) {
        if (!empty($id)
            && ($location = $this->mets->xpath('./mets:fileSec/mets:fileGrp/mets:file[@ID="'.$id.'"]/mets:FLocat[@LOCTYPE="URL"]'))) {
                return (string) $location[0]->attributes('http://www.w3.org/1999/xlink')->href;
        } else {
            Helper::devLog('There is no file node with @ID "'.$id.'"', DEVLOG_SEVERITY_WARNING);
            return '';
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getFileMimeType()
     */
    public function getFileMimeType($id) {
        if (!empty($id)
            && ($mimetype = $this->mets->xpath('./mets:fileSec/mets:fileGrp/mets:file[@ID="'.$id.'"]/@MIMETYPE'))) {
                return (string) $mimetype[0];
        } else {
            Helper::devLog('There is no file node with @ID "'.$id.'" or no MIME type specified', DEVLOG_SEVERITY_WARNING);
            return '';
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getLogicalStructure()
     */
    public function getLogicalStructure($id, $recursive = FALSE) {
        $details = [];
        // Is the requested logical unit already loaded?
        if (!$recursive
            && !empty($this->logicalUnits[$id])) {
            // Yes. Return it.
            return $this->logicalUnits[$id];
        } elseif (!empty($id)) {
            // Get specified logical unit.
            $divs = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="'.$id.'"]');
        } else {
            // Get all logical units at top level.
            $divs = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]/mets:div');
        }
        if (!empty($divs)) {
            if (!$recursive) {
                // Get the details for the first xpath hit.
                $details = $this->getLogicalStructureInfo($divs[0]);
            } else {
                // Walk the logical structure recursively and fill the whole table of contents.
                foreach ($divs as $div) {
                    $this->tableOfContents[] = $this->getLogicalStructureInfo($div, TRUE);
                }
            }
        }
        return $details;
    }

    /**
     * This gets details about a logical structure element
     *
     * @access protected
     *
     * @param \SimpleXMLElement $structure: The logical structure node
     * @param boolean $recursive: Whether to include the child elements
     *
     * @return array Array of the element's id, label, type and physical page indexes/mptr link
     */
    protected function getLogicalStructureInfo(\SimpleXMLElement $structure, $recursive = FALSE) {
        // Get attributes.
        foreach ($structure->attributes() as $attribute => $value) {
            $attributes[$attribute] = (string) $value;
        }
        // Load plugin configuration.
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
        // Extract identity information.
        $details = [];
        $details['id'] = $attributes['ID'];
        $details['dmdId'] = (isset($attributes['DMDID']) ? $attributes['DMDID'] : '');
        $details['label'] = (isset($attributes['LABEL']) ? $attributes['LABEL'] : '');
        $details['orderlabel'] = (isset($attributes['ORDERLABEL']) ? $attributes['ORDERLABEL'] : '');
        $details['contentIds'] = (isset($attributes['CONTENTIDS']) ? $attributes['CONTENTIDS'] : '');
        $details['volume'] = '';
        // Set volume information only if no label is set and this is the toplevel structure element.
        if (empty($details['label'])
            && $details['id'] == $this->_getToplevelId()) {
            $metadata = $this->getMetadata($details['id']);
            if (!empty($metadata['volume'][0])) {
                $details['volume'] = $metadata['volume'][0];
            }
        }
        $details['pagination'] = '';
        $details['type'] = $attributes['TYPE'];
        $details['thumbnailId'] = '';
        // Load smLinks.
        $this->_getSmLinks();
        // Load physical structure.
        $this->_getPhysicalStructure();
        // Get the physical page or external file this structure element is pointing at.
        $details['points'] = '';
        // Is there a mptr node?
        if (count($structure->children('http://www.loc.gov/METS/')->mptr)) {
            // Yes. Get the file reference.
            $details['points'] = (string) $structure->children('http://www.loc.gov/METS/')->mptr[0]->attributes('http://www.w3.org/1999/xlink')->href;
        } elseif (!empty($this->physicalStructure)
            && array_key_exists($details['id'], $this->smLinks['l2p'])) { // Are there any physical elements and is this logical unit linked to at least one of them?
            $details['points'] = max(intval(array_search($this->smLinks['l2p'][$details['id']][0], $this->physicalStructure, TRUE)), 1);
            if (!empty($this->physicalStructureInfo[$this->smLinks['l2p'][$details['id']][0]]['files'][$extConf['fileGrpThumbs']])) {
                $details['thumbnailId'] = $this->physicalStructureInfo[$this->smLinks['l2p'][$details['id']][0]]['files'][$extConf['fileGrpThumbs']];
            }
            // Get page/track number of the first page/track related to this structure element.
            $details['pagination'] = $this->physicalStructureInfo[$this->smLinks['l2p'][$details['id']][0]]['orderlabel'];
        } elseif ($details['id'] == $this->_getToplevelId()) { // Is this the toplevel structure element?
            // Yes. Point to itself.
            $details['points'] = 1;
            if (!empty($this->physicalStructure)
            && !empty($this->physicalStructureInfo[$this->physicalStructure[1]]['files'][$extConf['fileGrpThumbs']])) {
                $details['thumbnailId'] = $this->physicalStructureInfo[$this->physicalStructure[1]]['files'][$extConf['fileGrpThumbs']];
            }
        }
        // Get the files this structure element is pointing at.
        $details['files'] = [];
        $fileUse = $this->_getFileGrps();
        // Get the file representations from fileSec node.
        foreach ($structure->children('http://www.loc.gov/METS/')->fptr as $fptr) {
            // Check if file has valid @USE attribute.
            if (!empty($fileUse[(string) $fptr->attributes()->FILEID])) {
                $details['files'][$fileUse[(string) $fptr->attributes()->FILEID]] = (string) $fptr->attributes()->FILEID;
            }
        }
        // Keep for later usage.
        $this->logicalUnits[$details['id']] = $details;
        // Walk the structure recursively? And are there any children of the current element?
        if ($recursive
            && count($structure->children('http://www.loc.gov/METS/')->div)) {
            $details['children'] = [];
            foreach ($structure->children('http://www.loc.gov/METS/')->div as $child) {
                // Repeat for all children.
                $details['children'][] = $this->getLogicalStructureInfo($child, TRUE);
            }
        }
        return $details;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getMetadata()
     */
    public function getMetadata($id, $cPid = 0) {
        // Make sure $cPid is a non-negative integer.
        $cPid = max(intval($cPid), 0);
        // If $cPid is not given, try to get it elsewhere.
        if (!$cPid
            && ($this->cPid || $this->pid)) {
            // Retain current PID.
            $cPid = ($this->cPid ? $this->cPid : $this->pid);
        } elseif (!$cPid) {
            Helper::devLog('Invalid PID '.$cPid.' for metadata definitions', DEVLOG_SEVERITY_WARNING);
            return [];
        }
        // Get metadata from parsed metadata array if available.
        if (!empty($this->metadataArray[$id])
            && $this->metadataArray[0] == $cPid) {
            return $this->metadataArray[$id];
        }
        // Initialize metadata array with empty values.
        $metadata = [
            'title' => [],
            'title_sorting' => [],
            'author' => [],
            'place' => [],
            'year' => [],
            'prod_id' => [],
            'record_id' => [],
            'opac_id' => [],
            'union_id' => [],
            'urn' => [],
            'purl' => [],
            'type' => [],
            'volume' => [],
            'volume_sorting' => [],
            'collection' => [],
            'owner' => [],
            'document_format' => [],
        ];
        $metadata['document_format'][] = 'METS';
        // Get the logical structure node's DMDID.
        if (!empty($this->logicalUnits[$id])) {
            $dmdId = $this->logicalUnits[$id]['dmdId'];
        } else {
            $dmdId = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="'.$id.'"]/@DMDID');
            $dmdId = (string) $dmdId[0];
        }
        if (!empty($dmdId)) {
            // Load available metadata formats and dmdSecs.
            $this->loadFormats();
            $this->_getDmdSec();
            // Is this metadata format supported?
            if (!empty($this->formats[$this->dmdSec[$dmdId]['type']])) {
                if (!empty($this->formats[$this->dmdSec[$dmdId]['type']]['class'])) {
                    $class = $this->formats[$this->dmdSec[$dmdId]['type']]['class'];
                    // Get the metadata from class.
                    if (class_exists($class)
                        && ($obj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class)) instanceof MetadataInterface) {
                        $obj->extractMetadata($this->dmdSec[$dmdId]['xml'], $metadata);
                    } else {
                        Helper::devLog('Invalid class/method "'.$class.'->extractMetadata()" for metadata format "'.$this->dmdSec[$dmdId]['type'].'"', DEVLOG_SEVERITY_WARNING);
                    }
                }
            } else {
                Helper::devLog('Unsupported metadata format "'.$this->dmdSec[$dmdId]['type'].'" in dmdSec with @ID "'.$dmdId.'"', DEVLOG_SEVERITY_WARNING);
                return [];
            }
            // Get the structure's type.
            if (!empty($this->logicalUnits[$id])) {
                $metadata['type'] = [$this->logicalUnits[$id]['type']];
            } else {
                $struct = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="'.$id.'"]/@TYPE');
                $metadata['type'] = [(string) $struct[0]];
            }
            // Get the additional metadata from database.
            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'tx_dlf_metadata.index_name AS index_name,tx_dlf_metadataformat.metadataquery AS metadataquery,tx_dlf_metadataformat.metadataquery_sorting AS metadataquery_sorting,tx_dlf_metadata.is_sortable AS is_sortable,tx_dlf_metadata.default_value AS default_value,tx_dlf_metadata.format AS format',
                'tx_dlf_metadata,tx_dlf_metadataformat,tx_dlf_formats',
                'tx_dlf_metadata.pid='.$cPid
                .' AND tx_dlf_metadataformat.pid='.$cPid
                .' AND ((tx_dlf_metadata.uid=tx_dlf_metadataformat.parent_id AND tx_dlf_metadataformat.encoded=tx_dlf_formats.uid AND tx_dlf_formats.type='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->dmdSec[$dmdId]['type'], 'tx_dlf_formats').') OR tx_dlf_metadata.format=0)'
                .Helper::whereClause('tx_dlf_metadata', TRUE)
                .Helper::whereClause('tx_dlf_metadataformat')
                .Helper::whereClause('tx_dlf_formats'),
                '',
                '',
                ''
                );
            // We need a \DOMDocument here, because SimpleXML doesn't support XPath functions properly.
            $domNode = dom_import_simplexml($this->dmdSec[$dmdId]['xml']);
            $domXPath = new \DOMXPath($domNode->ownerDocument);
            $this->registerNamespaces($domXPath);
            // OK, now make the XPath queries.
            while ($resArray = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                // Set metadata field's value(s).
                if ($resArray['format'] > 0
                    && !empty($resArray['metadataquery'])
                    && ($values = $domXPath->evaluate($resArray['metadataquery'], $domNode))) {
                    if ($values instanceof \DOMNodeList
                        && $values->length > 0) {
                        $metadata[$resArray['index_name']] = [];
                        foreach ($values as $value) {
                            $metadata[$resArray['index_name']][] = trim((string) $value->nodeValue);
                        }
                    } elseif (!($values instanceof \DOMNodeList)) {
                        $metadata[$resArray['index_name']] = [trim((string) $values)];
                    }
                }
                // Set default value if applicable.
                if (empty($metadata[$resArray['index_name']][0])
                    && strlen($resArray['default_value']) > 0) {
                    $metadata[$resArray['index_name']] = [$resArray['default_value']];
                }
                // Set sorting value if applicable.
                if (!empty($metadata[$resArray['index_name']])
                    && $resArray['is_sortable']) {
                    if ($resArray['format'] > 0
                        && !empty($resArray['metadataquery_sorting'])
                        && ($values = $domXPath->evaluate($resArray['metadataquery_sorting'], $domNode))) {
                        if ($values instanceof \DOMNodeList
                            && $values->length > 0) {
                            $metadata[$resArray['index_name'].'_sorting'][0] = trim((string) $values->item(0)->nodeValue);
                        } elseif (!($values instanceof \DOMNodeList)) {
                            $metadata[$resArray['index_name'].'_sorting'][0] = trim((string) $values);
                        }
                    }
                    if (empty($metadata[$resArray['index_name'].'_sorting'][0])) {
                        $metadata[$resArray['index_name'].'_sorting'][0] = $metadata[$resArray['index_name']][0];
                    }
                }
            }
            // Set title to empty string if not present.
            if (empty($metadata['title'][0])) {
                $metadata['title'][0] = '';
                $metadata['title_sorting'][0] = '';
            }
            // Add collections from database to toplevel element if document is already saved.
            if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($this->uid)
                && $id == $this->_getToplevelId()) {
                $result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                    'tx_dlf_collections.index_name AS index_name',
                    'tx_dlf_documents',
                    'tx_dlf_relations',
                    'tx_dlf_collections',
                    'AND tx_dlf_collections.pid='.intval($cPid)
                    .' AND tx_dlf_documents.uid='.intval($this->uid)
                    .' AND tx_dlf_relations.ident='.$GLOBALS['TYPO3_DB']->fullQuoteStr('docs_colls', 'tx_dlf_relations')
                    .' AND tx_dlf_collections.sys_language_uid IN (-1,0)'
                    .Helper::whereClause('tx_dlf_documents')
                    .Helper::whereClause('tx_dlf_collections'),
                    'tx_dlf_collections.index_name',
                    '',
                    ''
                    );
                while ($resArray = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
                    if (!in_array($resArray['index_name'], $metadata['collection'])) {
                        $metadata['collection'][] = $resArray['index_name'];
                    }
                }
            }
        } else {
            // There is no dmdSec for this structure node.
            return [];
        }
        return $metadata;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::getRawText()
     */
    public function getRawText($id) {
        $rawText = '';
        // Get text from raw text array if available.
        if (!empty($this->rawTextArray[$id])) {
            return $this->rawTextArray[$id];
        }
        // Load fileGrps and check for fulltext files.
        $this->_getFileGrps();
        if ($this->hasFulltext) {
            $rawText = $this->getRawTextFromXml($id);
        }
        return $rawText;
    }

    /**
     * {@inheritDoc}
     * @see Document::getStructureDepth()
     */
    public function getStructureDepth($logId)
    {
        return count($this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="'.$logId.'"]/ancestor::*'));
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::init()
     */
    protected function init() {
        // Get METS node from XML file.
        $this->registerNamespaces($this->xml);
        $mets = $this->xml->xpath('//mets:mets');
        if ($mets) {
            $this->mets = $mets[0];
            // Register namespaces.
            $this->registerNamespaces($this->mets);
        } else {
            Helper::devLog('No METS part found in document with UID '.$this->uid, DEVLOG_SEVERITY_ERROR);
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::loadLocation()
     */
    protected function loadLocation($location) {
        // Turn off libxml's error logging.
        $libxmlErrors = libxml_use_internal_errors(TRUE);
        // Disables the functionality to allow external entities to be loaded when parsing the XML, must be kept
        $previousValueOfEntityLoader = libxml_disable_entity_loader(TRUE);
        // Load XML from file.
        $xml = simplexml_load_string(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($location));
        // reset entity loader setting
        libxml_disable_entity_loader($previousValueOfEntityLoader);
        // Reset libxml's error logging.
        libxml_use_internal_errors($libxmlErrors);
        // Set some basic properties.
        if ($xml !== FALSE) {
            $this->xml = $xml;
            return TRUE;
        } else {
            Helper::devLog('Could not load XML file from "'.$location.'"', DEVLOG_SEVERITY_ERROR);
        }
    }

    protected function ensureHasFulltextIsSet() {
        // Are the fileGrps already loaded?
        if (!$this->fileGrpsLoaded) {
            $this->_getFileGrps();
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see Document::getParentDocumentUid()
     */
    protected function getParentDocumentUidForSaving()
    {
        $partof = 0;
        // Get the closest ancestor of the current document which has a MPTR child.
        $parentMptr = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@ID="'.$this->_getToplevelId().'"]/ancestor::mets:div[./mets:mptr][1]/mets:mptr');
        if (!empty($parentMptr[0])) {
            $parentLocation = (string) $parentMptr[0]->attributes('http://www.w3.org/1999/xlink')->href;
            if ($parentLocation != $this->location) {
                $parentDoc = self::getInstance($parentLocation, $pid);
                if ($parentDoc->ready) {
                    if ($parentDoc->pid != $pid) {
                        $parentDoc->save($pid, $core);
                    }
                    $partof = $parentDoc->uid;
                }
            }
        }
        return $partof;
    }

    /**
     *
     * {@inheritDoc}
     * @see Document::setPreloadedDocument()
     */
    protected function setPreloadedDocument($preloadedDocument) {
        
        if ($preloadedDocument instanceof \SimpleXMLElement) {
            $this->xml = $preloadedDocument;
            return true;
        }
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see Document::getDocument()
     */
    protected function getDocument() {
        return $this->mets;
    }

    /**
     * This returns $this->cPid via __get()
     *
     * @access protected
     *
     * @return integer The PID of the metadata definitions
     */
    protected function _getCPid() {
        return $this->cPid;
    }

    /**
     * This builds an array of the document's dmdSecs
     *
     * @access protected
     *
     * @return array Array of dmdSecs with their IDs as array key
     */
    protected function _getDmdSec() {
        if (!$this->dmdSecLoaded) {
            // Get available data formats.
            $this->loadFormats();
            // Get dmdSec nodes from METS.
            $dmdIds = $this->mets->xpath('./mets:dmdSec/@ID');
            foreach ($dmdIds as $dmdId) {
                if ($type = $this->mets->xpath('./mets:dmdSec[@ID="'.(string) $dmdId.'"]/mets:mdWrap[not(@MDTYPE="OTHER")]/@MDTYPE')) {
                    if (!empty($this->formats[(string) $type[0]])) {
                        $type = (string) $type[0];
                        $xml = $this->mets->xpath('./mets:dmdSec[@ID="'.(string) $dmdId.'"]/mets:mdWrap[@MDTYPE="'.$type.'"]/mets:xmlData/'.strtolower($type).':'.$this->formats[$type]['rootElement']);
                    }
                } elseif ($type = $this->mets->xpath('./mets:dmdSec[@ID="'.(string) $dmdId.'"]/mets:mdWrap[@MDTYPE="OTHER"]/@OTHERMDTYPE')) {
                    if (!empty($this->formats[(string) $type[0]])) {
                        $type = (string) $type[0];
                        $xml = $this->mets->xpath('./mets:dmdSec[@ID="'.(string) $dmdId.'"]/mets:mdWrap[@MDTYPE="OTHER"][@OTHERMDTYPE="'.$type.'"]/mets:xmlData/'.strtolower($type).':'.$this->formats[$type]['rootElement']);
                    }
                }
                if ($xml) {
                    $this->dmdSec[(string) $dmdId]['type'] = $type;
                    $this->dmdSec[(string) $dmdId]['xml'] = $xml[0];
                    $this->registerNamespaces($this->dmdSec[(string) $dmdId]['xml']);
                }
            }
            $this->dmdSecLoaded = TRUE;
        }
        return $this->dmdSec;
    }

    /**
     * This builds the file ID -> USE concordance
     *
     * @access protected
     *
     * @return array Array of file use groups with file IDs
     */
    protected function _getFileGrps() {
        if (!$this->fileGrpsLoaded) {
            // Get configured USE attributes.
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
            $useGrps = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $extConf['fileGrps']);
            if (!empty($extConf['fileGrpThumbs'])) {
                $useGrps[] = $extConf['fileGrpThumbs'];
            }
            if (!empty($extConf['fileGrpDownload'])) {
                $useGrps[] = $extConf['fileGrpDownload'];
            }
            if (!empty($extConf['fileGrpFulltext'])) {
                $useGrps[] = $extConf['fileGrpFulltext'];
            }
            if (!empty($extConf['fileGrpAudio'])) {
                $useGrps[] = $extConf['fileGrpAudio'];
            }
            // Get all file groups.
            $fileGrps = $this->mets->xpath('./mets:fileSec/mets:fileGrp');
            // Build concordance for configured USE attributes.
            foreach ($fileGrps as $fileGrp) {
                if (in_array((string) $fileGrp['USE'], $useGrps)) {
                    foreach ($fileGrp->children('http://www.loc.gov/METS/')->file as $file) {
                        $this->fileGrps[(string) $file->attributes()->ID] = (string) $fileGrp['USE'];
                    }
                }
            }
            // Are there any fulltext files available?
            if (!empty($extConf['fileGrpFulltext'])
                && in_array($extConf['fileGrpFulltext'], $this->fileGrps)) {
                    $this->hasFulltext = TRUE;
                }
                $this->fileGrpsLoaded = TRUE;
        }
        return $this->fileGrps;
    }

    protected function prepareMetadataArray($cPid) {
        // Get all logical structure nodes with metadata.
        if (($ids = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@DMDID]/@ID'))) {
            foreach ($ids as $id) {
                $this->metadataArray[(string) $id] = $this->getMetadata((string) $id, $cPid);
            }
        }
        // Set current PID for metadata definitions.
    }
    
    /**
     * This returns $this->mets via __get()
     *
     * @access protected
     *
     * @return \SimpleXMLElement The XML's METS part as \SimpleXMLElement object
     */
    protected function _getMets() {
        return $this->mets;
    }

    protected function _getPhysicalStructure() {
        // Is there no physical structure array yet?
        if (!$this->physicalStructureLoaded) {
            // Does the document have a structMap node of type "PHYSICAL"?
            $elementNodes = $this->mets->xpath('./mets:structMap[@TYPE="PHYSICAL"]/mets:div[@TYPE="physSequence"]/mets:div');
            if ($elementNodes) {
                // Get file groups.
                $fileUse = $this->_getFileGrps();
                // Get the physical sequence's metadata.
                $physNode = $this->mets->xpath('./mets:structMap[@TYPE="PHYSICAL"]/mets:div[@TYPE="physSequence"]');
                $physSeq[0] = (string) $physNode[0]['ID'];
                $this->physicalStructureInfo[$physSeq[0]]['id'] = (string) $physNode[0]['ID'];
                $this->physicalStructureInfo[$physSeq[0]]['dmdId'] = (isset($physNode[0]['DMDID']) ? (string) $physNode[0]['DMDID'] : '');
                $this->physicalStructureInfo[$physSeq[0]]['label'] = (isset($physNode[0]['LABEL']) ? (string) $physNode[0]['LABEL'] : '');
                $this->physicalStructureInfo[$physSeq[0]]['orderlabel'] = (isset($physNode[0]['ORDERLABEL']) ? (string) $physNode[0]['ORDERLABEL'] : '');
                $this->physicalStructureInfo[$physSeq[0]]['type'] = (string) $physNode[0]['TYPE'];
                $this->physicalStructureInfo[$physSeq[0]]['contentIds'] = (isset($physNode[0]['CONTENTIDS']) ? (string) $physNode[0]['CONTENTIDS'] : '');
                // Get the file representations from fileSec node.
                foreach ($physNode[0]->children('http://www.loc.gov/METS/')->fptr as $fptr) {
                    // Check if file has valid @USE attribute.
                    if (!empty($fileUse[(string) $fptr->attributes()->FILEID])) {
                        $this->physicalStructureInfo[$physSeq[0]]['files'][$fileUse[(string) $fptr->attributes()->FILEID]] = (string) $fptr->attributes()->FILEID;
                    }
                }
                // Build the physical elements' array from the physical structMap node.
                foreach ($elementNodes as $elementNode) {
                    $elements[(int) $elementNode['ORDER']] = (string) $elementNode['ID'];
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['id'] = (string) $elementNode['ID'];
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['dmdId'] = (isset($elementNode['DMDID']) ? (string) $elementNode['DMDID'] : '');
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['label'] = (isset($elementNode['LABEL']) ? (string) $elementNode['LABEL'] : '');
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['orderlabel'] = (isset($elementNode['ORDERLABEL']) ? (string) $elementNode['ORDERLABEL'] : '');
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['type'] = (string) $elementNode['TYPE'];
                    $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['contentIds'] = (isset($elementNode['CONTENTIDS']) ? (string) $elementNode['CONTENTIDS'] : '');
                    // Get the file representations from fileSec node.
                    foreach ($elementNode->children('http://www.loc.gov/METS/')->fptr as $fptr) {
                        // Check if file has valid @USE attribute.
                        if (!empty($fileUse[(string) $fptr->attributes()->FILEID])) {
                            $this->physicalStructureInfo[$elements[(int) $elementNode['ORDER']]]['files'][$fileUse[(string) $fptr->attributes()->FILEID]] = (string) $fptr->attributes()->FILEID;
                        }
                    }
                }
                // Sort array by keys (= @ORDER).
                if (ksort($elements)) {
                    // Set total number of pages/tracks.
                    $this->numPages = count($elements);
                    // Merge and re-index the array to get nice numeric indexes.
                    $this->physicalStructure = array_merge($physSeq, $elements);
                }
            }
            $this->physicalStructureLoaded = TRUE;
        }
        return $this->physicalStructure;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::_getSmLinks()
     */
    protected function _getSmLinks() {
        if (!$this->smLinksLoaded) {
            $smLinks = $this->mets->xpath('./mets:structLink/mets:smLink');
            foreach ($smLinks as $smLink) {
                $this->smLinks['l2p'][(string) $smLink->attributes('http://www.w3.org/1999/xlink')->from][] = (string) $smLink->attributes('http://www.w3.org/1999/xlink')->to;
                $this->smLinks['p2l'][(string) $smLink->attributes('http://www.w3.org/1999/xlink')->to][] = (string) $smLink->attributes('http://www.w3.org/1999/xlink')->from;
            }
            $this->smLinksLoaded = TRUE;
        }
        return $this->smLinks;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Kitodo\Dlf\Common\Document::_getToplevelId()
     */
    protected function _getToplevelId() {
        if (empty($this->toplevelId)) {
            // Get all logical structure nodes with metadata, but without associated METS-Pointers.
            if (($divs = $this->mets->xpath('./mets:structMap[@TYPE="LOGICAL"]//mets:div[@DMDID and not(./mets:mptr)]'))) {
                // Load smLinks.
                $this->_getSmLinks();
                foreach ($divs as $div) {
                    $id = (string) $div['ID'];
                    // Are there physical structure nodes for this logical structure?
                    if (array_key_exists($id, $this->smLinks['l2p'])) {
                        // Yes. That's what we're looking for.
                        $this->toplevelId = $id;
                        break;
                    } elseif (empty($this->toplevelId)) {
                        // No. Remember this anyway, but keep looking for a better one.
                        $this->toplevelId = $id;
                    }
                }
            }
        }
        return $this->toplevelId;
    }

    /**
     * This magic method is executed prior to any serialization of the object
     * @see __wakeup()
     *
     * @access public
     *
     * @return array Properties to be serialized
     */
    public function __sleep() {
        // \SimpleXMLElement objects can't be serialized, thus save the XML as string for serialization
        $this->asXML = $this->xml->asXML();
        return ['uid', 'pid', 'recordId', 'parentId', 'asXML'];
    }
    
    /**
     * This magic method is used for setting a string value for the object
     *
     * @access public
     *
     * @return string String representing the METS object
     */
    public function __toString() {
        $xml = new \DOMDocument('1.0', 'utf-8');
        $xml->appendChild($xml->importNode(dom_import_simplexml($this->mets), TRUE));
        $xml->formatOutput = TRUE;
        return $xml->saveXML();
    }
    
    /**
     * This magic method is executed after the object is deserialized
     * @see __sleep()
     *
     * @access public
     *
     * @return void
     */
    public function __wakeup() {
        // Turn off libxml's error logging.
        $libxmlErrors = libxml_use_internal_errors(TRUE);
        // Reload XML from string.
        $xml = @simplexml_load_string($this->asXML);
        // Reset libxml's error logging.
        libxml_use_internal_errors($libxmlErrors);
        if ($xml !== FALSE) {
            $this->asXML = '';
            $this->xml = $xml;
            // Rebuild the unserializable properties.
            $this->init();
        } else {
            Helper::devLog('Could not load XML after deserialization', DEVLOG_SEVERITY_ERROR);
        }
    }
}

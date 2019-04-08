<?php
namespace Kitodo\Dlf\Hooks;

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
 * Hooks and hacks for Kitodo.Production
 *
 * @author Sebastian Meyer <sebastian.meyer@slub-dresden.de>
 * @package TYPO3
 * @subpackage dlf
 * @access public
 */
class KitodoProductionHacks {
    /**
     * Hook for \Kitodo\Dlf\Common\MetsDocument::establishRecordId()
     * When using Kitodo.Production the record identifier is saved only in MODS, but not
     * in METS. To get it anyway, we have to do some magic.
     *
     * @access public
     *
     * @param \SimpleXMLElement &$xml: The XML object
     * @param mixed $record_id: The record identifier
     *
     * @return void
     */
    public function construct_postProcessRecordId(\SimpleXMLElement &$xml, &$record_id) {
        if (!$record_id) {
            $xml->registerXPathNamespace('mods', 'http://www.loc.gov/mods/v3');
            // Get all logical structure nodes with metadata, but without associated METS-Pointers.
            if (($divs = $xml->xpath('//mets:structMap[@TYPE="LOGICAL"]//mets:div[@DMDID and not(./mets:mptr)]'))) {
                $smLinks = $xml->xpath('//mets:structLink/mets:smLink');
                if ($smLinks) {
                    foreach ($smLinks as $smLink) {
                        $links[(string) $smLink->attributes('http://www.w3.org/1999/xlink')->from][] = (string) $smLink->attributes('http://www.w3.org/1999/xlink')->to;
                    }
                    foreach ($divs as $div) {
                        if (!empty($links[(string) $div['ID']])) {
                            $id = (string) $div['DMDID'];
                            break;
                        }
                    }
                }
                if (empty($id)) {
                    $id = (string) $divs[0]['DMDID'];
                }
                $recordIds = $xml->xpath('//mets:dmdSec[@ID="'.$id.'"]//mods:mods/mods:recordInfo/mods:recordIdentifier');
                if (!empty($recordIds[0])) {
                    $record_id = (string) $recordIds[0];
                }
            }
        }
    }
}

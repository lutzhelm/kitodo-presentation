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

namespace Kitodo\Dlf\Plugin;

use Kitodo\Dlf\Common\DocumentList;
use Kitodo\Dlf\Common\Helper;

/**
 * Plugin 'Navigation' for the 'dlf' extension
 *
 * @author Sebastian Meyer <sebastian.meyer@slub-dresden.de>
 * @package TYPO3
 * @subpackage dlf
 * @access public
 */
class Navigation extends \Kitodo\Dlf\Common\AbstractPlugin
{
    public $scriptRelPath = 'Classes/Plugin/Navigation.php';

    /**
     * Display a link to the list view
     *
     * @access protected
     *
     * @return string Link to the list view ready to output
     */
    protected function getLinkToListview()
    {
        if (!empty($this->conf['targetPid'])) {
            // Load the list.
            $list = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(DocumentList::class);
            if (count($list) > 0) {
                // Build typolink configuration array.
                $conf = [
                    'useCacheHash' => 1,
                    'parameter' => $this->conf['targetPid'],
                    'title' => $this->pi_getLL('linkToList', '', true)
                ];
                return $this->cObj->typoLink($this->pi_getLL('linkToList', '', true), $conf);
            }
        }
        return '';
    }

    /**
     * Display the page selector for the page view
     *
     * @access protected
     *
     * @return string Page selector ready to output
     */
    protected function getPageSelector()
    {
        // Configure @action URL for form.
        $linkConf = [
            'parameter' => $GLOBALS['TSFE']->id
        ];
        $output = '<form action="' . $this->cObj->typoLink_URL($linkConf) . '" method="get"><div><input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '" />';
        // Add plugin variables.
        foreach ($this->piVars as $piVar => $value) {
            if ($piVar != 'page' && $piVar != 'DATA' && !empty($value)) {
                $output .= '<input type="hidden" name="' . $this->prefixId . '[' . $piVar . ']" value="' . $value . '" />';
            }
        }
        // Add page selector.
        $uniqId = uniqid(Helper::getUnqualifiedClassName(get_class($this)) . '-');
        $output .= '<label for="' . $uniqId . '">' . $this->pi_getLL('selectPage', '', true) . '</label><select id="' . $uniqId . '" name="' . $this->prefixId . '[page]" onchange="javascript:this.form.submit();"' . ($this->doc->numPages < 1 ? ' disabled="disabled"' : '') . '>';
        for ($i = 1; $i <= $this->doc->numPages; $i++) {
            $output .= '<option value="' . $i . '"' . ($this->piVars['page'] == $i ? ' selected="selected"' : '') . '>[' . $i . ']' . ($this->doc->physicalStructureInfo[$this->doc->physicalStructure[$i]]['orderlabel'] ? ' - ' . htmlspecialchars($this->doc->physicalStructureInfo[$this->doc->physicalStructure[$i]]['orderlabel']) : '') . '</option>';
        }
        $output .= '</select></div></form>';
        return $output;
    }

    /**
     * The main method of the PlugIn
     *
     * @access public
     *
     * @param string $content: The PlugIn content
     * @param array $conf: The PlugIn configuration
     *
     * @return string The content that is displayed on the website
     */
    public function main($content, $conf)
    {
        $this->init($conf);
        // Turn cache on.
        $this->setCache(true);
        // Load current document.
        $this->loadDocument();
        if ($this->doc === null) {
            // Quit without doing anything if required variables are not set.
            return $content;
        } else {
            // Set default values if not set.
            if ($this->doc->numPages > 0) {
                if (!empty($this->piVars['logicalPage'])) {
                    $this->piVars['page'] = $this->doc->getPhysicalPage($this->piVars['logicalPage']);
                    // The logical page parameter should not appear
                    unset($this->piVars['logicalPage']);
                }
                // Set default values if not set.
                // $this->piVars['page'] may be integer or string (physical structure @ID)
                if (
                    (int) $this->piVars['page'] > 0
                    || empty($this->piVars['page'])
                ) {
                    $this->piVars['page'] = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange((int) $this->piVars['page'], 1, $this->doc->numPages, 1);
                } else {
                    $this->piVars['page'] = array_search($this->piVars['page'], $this->doc->physicalStructure);
                }
                $this->piVars['double'] = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($this->piVars['double'], 0, 1, 0);
            } else {
                $this->piVars['page'] = 0;
                $this->piVars['double'] = 0;
            }
        }
        // Load template file.
        $this->getTemplate();
        // Steps for X pages backward / forward. Double page view uses double steps.
        $pageSteps = $this->conf['pageStep'] * ($this->piVars['double'] + 1);
        // Link to first page.
        if ($this->piVars['page'] > 1) {
            $markerArray['###FIRST###'] = $this->makeLink($this->pi_getLL('firstPage', '', true), ['page' => 1]);
        } else {
            $markerArray['###FIRST###'] = '<span title="' . $this->pi_getLL('firstPage', '', true) . '">' . $this->pi_getLL('firstPage', '', true) . '</span>';
        }
        // Link back X pages.
        if ($this->piVars['page'] > $pageSteps) {
            $markerArray['###BACK###'] = $this->makeLink(sprintf($this->pi_getLL('backXPages', '', true), $pageSteps), ['page' => $this->piVars['page'] - $pageSteps]);
        } else {
            $markerArray['###BACK###'] = '<span title="' . sprintf($this->pi_getLL('backXPages', '', true), $pageSteps) . '">' . sprintf($this->pi_getLL('backXPages', '', true), $pageSteps) . '</span>';
        }
        // Link to previous page.
        if ($this->piVars['page'] > (1 + $this->piVars['double'])) {
            $markerArray['###PREVIOUS###'] = $this->makeLink($this->pi_getLL('prevPage', '', true), ['page' => $this->piVars['page'] - (1 + $this->piVars['double'])]);
        } else {
            $markerArray['###PREVIOUS###'] = '<span title="' . $this->pi_getLL('prevPage', '', true) . '">' . $this->pi_getLL('prevPage', '', true) . '</span>';
        }
        // Link to next page.
        if ($this->piVars['page'] < ($this->doc->numPages - $this->piVars['double'])) {
            $markerArray['###NEXT###'] = $this->makeLink($this->pi_getLL('nextPage', '', true), ['page' => $this->piVars['page'] + (1 + $this->piVars['double'])]);
        } else {
            $markerArray['###NEXT###'] = '<span title="' . $this->pi_getLL('nextPage', '', true) . '">' . $this->pi_getLL('nextPage', '', true) . '</span>';
        }
        // Link forward X pages.
        if ($this->piVars['page'] <= ($this->doc->numPages - $pageSteps)) {
            $markerArray['###FORWARD###'] = $this->makeLink(sprintf($this->pi_getLL('forwardXPages', '', true), $pageSteps), ['page' => $this->piVars['page'] + $pageSteps]);
        } else {
            $markerArray['###FORWARD###'] = '<span title="' . sprintf($this->pi_getLL('forwardXPages', '', true), $pageSteps) . '">' . sprintf($this->pi_getLL('forwardXPages', '', true), $pageSteps) . '</span>';
        }
        // Link to last page.
        if ($this->piVars['page'] < $this->doc->numPages) {
            $markerArray['###LAST###'] = $this->makeLink($this->pi_getLL('lastPage', '', true), ['page' => $this->doc->numPages]);
        } else {
            $markerArray['###LAST###'] = '<span title="' . $this->pi_getLL('lastPage', '', true) . '">' . $this->pi_getLL('lastPage', '', true) . '</span>';
        }
        // Add double page switcher.
        if ($this->doc->numPages > 0) {
            if (!$this->piVars['double']) {
                $markerArray['###DOUBLEPAGE###'] = $this->makeLink($this->pi_getLL('doublePageOn', '', true), ['double' => 1], 'class="tx-dlf-navigation-doubleOn" title="' . $this->pi_getLL('doublePageOn', '', true) . '"');
            } else {
                $markerArray['###DOUBLEPAGE###'] = $this->makeLink($this->pi_getLL('doublePageOff', '', true), ['double' => 0], 'class="tx-dlf-navigation-doubleOff" title="' . $this->pi_getLL('doublePageOff', '', true) . '"');
            }
            if ($this->piVars['double'] && $this->piVars['page'] < $this->doc->numPages) {
                $markerArray['###DOUBLEPAGE+1###'] = $this->makeLink($this->pi_getLL('doublePage+1', '', true), ['page' => $this->piVars['page'] + 1], 'title="' . $this->pi_getLL('doublePage+1', '', true) . '"');
            } else {
                $markerArray['###DOUBLEPAGE+1###'] = '<span title="' . $this->pi_getLL('doublePage+1', '', true) . '">' . $this->pi_getLL('doublePage+1', '', true) . '</span>';
            }
        } else {
            $markerArray['###DOUBLEPAGE###'] = '<span title="' . $this->pi_getLL('doublePageOn', '', true) . '">' . $this->pi_getLL('doublePageOn', '', true) . '</span>';
            $markerArray['###DOUBLEPAGE+1###'] = '<span title="' . $this->pi_getLL('doublePage+1', '', true) . '">' . $this->pi_getLL('doublePage+1', '', true) . '</span>';
        }
        // Add page selector.
        $markerArray['###PAGESELECT###'] = $this->getPageSelector();
        // Add link to listview if applicable.
        $markerArray['###LINKLISTVIEW###'] = $this->getLinkToListview();
        // Fill some language labels if available.
        $markerArray['###ZOOM_IN###'] = $this->pi_getLL('zoom-in', '', true);
        $markerArray['###ZOOM_OUT###'] = $this->pi_getLL('zoom-out', '', true);
        $markerArray['###ZOOM_FULLSCREEN###'] = $this->pi_getLL('zoom-fullscreen', '', true);
        $markerArray['###ROTATE_LEFT###'] = $this->pi_getLL('rotate-left', '', true);
        $markerArray['###ROTATE_RIGHT###'] = $this->pi_getLL('rotate-right', '', true);
        $markerArray['###ROTATE_RESET###'] = $this->pi_getLL('rotate-reset', '', true);
        $content .= $this->templateService->substituteMarkerArray($this->template, $markerArray);
        return $this->pi_wrapInBaseClass($content);
    }

    /**
     * Generates a navigation link
     *
     * @access protected
     *
     * @param string $label: The link's text
     * @param array $overrulePIvars: The new set of plugin variables
     * @paramstring $aTagParams: Additional HTML attributes for link tag
     *
     * @return string Typolink ready to output
     */
    protected function makeLink($label, array $overrulePIvars = [], $aTagParams = '')
    {
        // Merge plugin variables with new set of values.
        if (is_array($this->piVars)) {
            $piVars = $this->piVars;
            unset($piVars['DATA']);
            $overrulePIvars = Helper::mergeRecursiveWithOverrule($piVars, $overrulePIvars);
        }
        // Build typolink configuration array.
        $conf = [
            'useCacheHash' => 1,
            'parameter' => $GLOBALS['TSFE']->id,
            'ATagParams' => $aTagParams,
            'additionalParams' => \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl($this->prefixId, $overrulePIvars, '', true, false),
            'title' => $label
        ];
        return $this->cObj->typoLink($label, $conf);
    }
}

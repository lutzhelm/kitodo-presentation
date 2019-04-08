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

return [
    'ctrl' => [
        'title'     => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents',
        'label'     => 'title',
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title_sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:dlf/Resources/Public/Icons/txdlfdocuments.png',
        'rootLevel' => 0,
        'dividers2tabs' => 2,
        'searchFields' => 'title,volume,author,year,place,uid,prod_id,location,oai_id,opac_id,union_id,urn',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => '',
    ],
    'interface' => [
        'showRecordFieldList' => 'title,volume,author,year,place,uid,prod_id,location,oai_id,opac_id,union_id,urn,document_format',
        'maxDBListItems' => 25,
        'maxSingleDBListItems' => 50,
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'max' => '20',
                'eval' => 'datetime',
                'default' => '0',
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'max' => '20',
                'eval' => 'datetime',
                'default' => '0',
            ],
        ],
        'fe_group' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--'],
                ],
                'foreign_table' => 'fe_groups',
                'size' => 5,
                'autoSizeMax' => 15,
                'minitems' => 0,
                'maxitems' => 20,
                'exclusiveKeys' => '-1,-2',
            ],
        ],
        'prod_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.prod_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace',
            ],
        ],
        'location' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.location',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 1024,
                'eval' => 'required,uniqueInPid',
            ],
        ],
        'record_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.record_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace,uniqueInPid',
            ],
        ],
        'opac_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.opac_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace',
            ],
        ],
        'union_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.union_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace',
            ],
        ],
        'urn' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.urn',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace',
            ],
        ],
        'purl' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.purl',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'nospace',
            ],
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 1024,
                'eval' => 'trim',
            ],
        ],
        'title_sorting' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.title_sorting',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 1024,
                'eval' => 'trim',
            ],
        ],
        'author' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.author',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'year' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.year',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'place' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.place',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'thumbnail' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.thumbnail',
            'config' => [
                'type' => 'user',
                'userFunc' => \Kitodo\Dlf\Hooks\FormEngine::class.'->displayThumbnail',
            ],
        ],
        'metadata' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'metadata_sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'structure' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.structure',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_dlf_structures',
                'foreign_table_where' => 'AND tx_dlf_structures.pid=###CURRENT_PID### AND tx_dlf_structures.sys_language_uid IN (-1,0) AND tx_dlf_structures.toplevel=1 ORDER BY tx_dlf_structures.label',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'partof' => [
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.partof',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_dlf_documents',
                'prepend_tname' => 0,
                'size' => 1,
                'selectedListStyle' => 'width:400px;',
                'minitems' => 0,
                'maxitems' => 1,
                'disable_controls' => 'browser,delete',
                'default' => 0,
                'readOnly' => 1,
            ],
        ],
        'volume' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.volume',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'volume_sorting' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.volume_sorting',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
            ],
        ],
        'collections' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.collections',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_dlf_collections',
                'foreign_table_where' => 'AND tx_dlf_collections.pid=###CURRENT_PID### AND tx_dlf_collections.sys_language_uid IN (-1,0) ORDER BY tx_dlf_collections.label',
                'size' => 5,
                'autoSizeMax' => 15,
                'minitems' => 1,
                'maxitems' => 1024,
                'MM' => 'tx_dlf_relations',
                'MM_match_fields' => [
                    'ident' => 'docs_colls',
                ],
            ],
        ],
        'owner' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.owner',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_dlf_libraries',
                'foreign_table_where' => 'AND tx_dlf_libraries.sys_language_uid IN (-1,0) ORDER BY tx_dlf_libraries.label',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'solrcore' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'status' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.status.default', 0],
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'default' => 0,
            ],
        ],
        'document_format' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.document_format',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.document_format.mets', 'METS'],
                    ['LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.document_format.iiif', 'IIIF'],
                ],
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => '--div--;LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.tab1, title,--palette--;;1;;1-1-1, author, year, place, structure, document_format,--palette--;;2;;2-2-2, collections;;;;3-3-3, --div--;LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.tab2, location;;;;1-1-1, record_id, prod_id;;;;2-2-2, oai_id;;;;3-3-3, opac_id, union_id, urn, purl;;;;4-4-4, --div--;LLL:EXT:dlf/Resources/Private/Language/Labels.xml:tx_dlf_documents.tab3, hidden,--palette--;;3;;1-1-1, fe_group;;;;2-2-2, status;;;;3-3-3, owner;;;;4-4-4'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'title_sorting', 'canNotCollapse' => 1],
        '2' => ['showitem' => 'partof, thumbnail, --linebreak--, volume, volume_sorting', 'canNotCollapse' => 1],
        '3' => ['showitem' => 'starttime, endtime', 'canNotCollapse' => 1],
    ]
];

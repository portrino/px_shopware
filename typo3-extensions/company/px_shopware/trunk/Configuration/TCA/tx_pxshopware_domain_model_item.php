<?php
defined('TYPO3_MODE') || die();

return array(
    'ctrl' => array(
        'title'    => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tx_pxshopware_domain_model_item',
        'label' => 'cache_identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'sortby' => 'last_update',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'typeicon_classes' => array(
            'default' => 'px-shopware-tx-pxshopware-domain-model-item'
        ),
        'searchFields' => 'cache_identifier,last_update,result,',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('px_shopware') . 'Resources/Public/Icons/tx_pxshopware_domain_model_item.png'
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden, cache_identifier, last_update, result',
    ),
    'types' => array(
        '1' => array('showitem' => 'hidden;;1, cache_identifier, last_update, result,'),
    ),
    'palettes' => array(
        '1' => array(
            'showitem' => ''
        )
    ),
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'cache_identifier' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tx_pxshopware_domain_model_item.cache_identifier',
            'config' => array(
                'type' => 'text',
                'size' => 50,
                'eval' => 'trim,required'
            ),
        ),
        'last_update' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tx_pxshopware_domain_model_item.last_update',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ),
        ),
        'result' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tx_pxshopware_domain_model_item.result',
            'config' => array(
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            )
        ),
    ),
);

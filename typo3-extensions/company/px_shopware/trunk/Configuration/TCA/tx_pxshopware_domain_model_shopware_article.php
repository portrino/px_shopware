<?php
defined('TYPO3_MODE') || die();

return array(
    'ctrl' => array(
        'title'    => '',
        'label' => 'title',
        'typeicon_classes' => array(
            'default' => 'px-shopware-tx-pxshopware-domain-model-item'
        ),
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('px_shopware') . 'Resources/Public/Icons/tx_pxshopware_domain_model_item.png'
    ),
    'interface' => array(
        'showRecordFieldList' => 'title',
    ),
    'types' => array(
        '1' => array('showitem' => 'title,'),
    ),
    'palettes' => array(
        '1' => array(
            'showitem' => ''
        )
    ),
    'columns' => array(
        'title' => array(
            'exclude' => 0,
            'label' => '',
            'config' => array(
                'type' => 'text',
                'size' => 255,
            ),
        )
    ),
);

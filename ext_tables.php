<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Shopware Integration');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxshopware_domain_model_item');

    /**
     * PxShopware (Article)
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Portrino.' . $_EXTKEY,
        'Pi1',
        'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tt_content.list_type.pxshopware_pi1'
    );

};

$boot($_EXTKEY);
unset($boot);
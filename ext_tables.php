<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Shopware Integration');

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook'][] = Portrino\PxShopware\Hooks\WizardItems::class;

    /**
     * PxShopware (Article)
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Portrino.' . $_EXTKEY,
        'Pi1',
        'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tt_content.CType.pxshopware_pi1'
    );

//    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
//    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pi1.xml');

};

$boot($_EXTKEY);
unset($boot);
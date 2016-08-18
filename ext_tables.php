<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Shopware Integration');

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook'][] = Portrino\PxShopware\Backend\Hooks\WizardItems::class;
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessFetchRecordsForIndexQueueItem'][] = Portrino\PxShopware\Service\Solr\Hooks\Queue::class . '->postProcessFetchRecordsForIndexQueueItem';

};

$boot($_EXTKEY);
unset($boot);
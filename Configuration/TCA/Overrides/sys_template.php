<?php
defined('TYPO3_MODE') || die();

(function () {

    /**
     * Temporary variables
     */
    $extensionKey = 'px_shopware';

    /**
     * Default TypoScript for SitePackage
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Shopware Integration'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/Solr',
        'Shopware Integration (Solr Indexing Example)'
    );

})();

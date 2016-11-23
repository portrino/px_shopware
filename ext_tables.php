<?php
defined('TYPO3_MODE') || die();

call_user_func(function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Shopware Integration');
}, 'px_shopware');
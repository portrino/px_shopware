<?php
defined('TYPO3_MODE') || die();

call_user_func(function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $_EXTKEY,
        'Configuration/TypoScript',
        'Shopware Integration'
    );
}, 'px_shopware');

if (TYPO3_MODE == "BE") {
    $GLOBALS['TBE_STYLES']['skins'][$_EXTKEY] = [
        'name' => $_EXTKEY,
        'stylesheetDirectories' => [
            'structure' => '', //removes structure stylesheet
            'visual' => 'EXT:' . $_EXTKEY . '/Resources/Public/Css' // changes default directory
        ]
    ];
}
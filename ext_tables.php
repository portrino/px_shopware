<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $_EXTKEY,
        'Configuration/TypoScript',
        'Shopware Integration'
    );

    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TBE_STYLES']['skins'][$_EXTKEY] = [
            'name' => $_EXTKEY,
            'stylesheetDirectories' => [
                'structure' => '', //removes structure stylesheet
                'visual' => 'EXT:' . $_EXTKEY . '/Resources/Public/Css' // changes default directory
            ]
        ];
    }
};

$boot($_EXTKEY);
unset($boot);

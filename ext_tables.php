<?php

defined('TYPO3') || die();

(function () {
    $extensionKey = 'px_shopware';

    $GLOBALS['TBE_STYLES']['skins'][$extensionKey] = [
        'name' => $extensionKey,
        'stylesheetDirectories' => [
            'structure' => '', //removes structure stylesheet
            'visual' => 'EXT:' . $extensionKey . '/Resources/Public/Css', // changes default directory
        ],
    ];
})();

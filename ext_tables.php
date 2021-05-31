<?php
defined('TYPO3_MODE') || die();

(function () {
    $extensionKey = 'px_shopware';

    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TBE_STYLES']['skins'][$extensionKey] = [
            'name' => $extensionKey,
            'stylesheetDirectories' => [
                'structure' => '', //removes structure stylesheet
                'visual' => 'EXT:' . $extensionKey . '/Resources/Public/Css' // changes default directory
            ]
        ];
    }

})();

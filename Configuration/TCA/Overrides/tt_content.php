<?php
defined('TYPO3_MODE') or die();

$boot = function () {
    $extKey = 'px_shopware';
    $languageFilePrefix = 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:';
    $frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            $languageFilePrefix . 'tt_content.CType.div.px_shopware',
            '--div--',
        ]
    );

    /**
     * register icons for each plugin
     */
    $pluginSignatures = array(
        0 => str_replace('_', '', $extKey) . '_pi1',
        1 => str_replace('_', '', $extKey) . '_pi2'
    );
    foreach ($pluginSignatures as $pluginSignature) {
        
        if (\Portrino\PxShopware\Backend\Utility\ExtensionConfigurationMatcher::isFeatureEnabled(array(0 => 'plugin.', 1 => 'fetchAllItems'))) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/FetchAllItems/'. $pluginSignature .'.xml',
                $pluginSignature
            );
        } else {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/'. $pluginSignature .'.xml',
                $pluginSignature
            );
        }
        
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $languageFilePrefix . 'tt_content.CType.' . $pluginSignature,
                $pluginSignature,
                str_replace('_', '-', $pluginSignature)
            ]
        );
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['default'] = $pluginSignature;

        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$pluginSignature] = str_replace('_', '-', $pluginSignature);

        $GLOBALS['TCA']['tt_content']['types'][$pluginSignature] = [
            'showitem' => '
                --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
                --palette--;'. $frontendLanguageFilePrefix . 'header;header,rowDescription,
            --div--;' . $frontendLanguageFilePrefix . 'tabs.plugin,
                pi_flexform,
            --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
                hidden;' . $frontendLanguageFilePrefix . 'field.default.hidden,
                --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,
            --div--;' . $frontendLanguageFilePrefix . 'tabs.extended
        '
        ];

//        $GLOBALS['TCA']['tt_content']['types'][$pluginSignature] = $GLOBALS['TCA']['tt_content']['types']['list'];

        // Add category tab when categories column exits
        if (!empty($GLOBALS['TCA']['tt_content']['columns']['categories'])) {
            $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] .=
                ',--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,
                categories';
        }
    }
};

$boot();
unset($boot);

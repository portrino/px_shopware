<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Portrino.' . $_EXTKEY,
        'Pi1',
        array(
            'Article' => 'list',
        ),
        // non-cacheable actions
        array(
            'Article' => 'list',
        )
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Portrino.' . $_EXTKEY,
        'Pi2',
        array(
            'Category' => 'list',
        ),
        // non-cacheable actions
        array(
            'Category' => 'list',
        )
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup',
    '[GLOBAL]
    tt_content.pxshopware_pi1 = COA
    tt_content.pxshopware_pi1 {
        10 = < lib.stdheader
        20 >
        20 = < plugin.tx_pxshopware_pi1
    }
    ', TRUE);


    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup',
    '[GLOBAL]
    tt_content.pxshopware_pi2 = COA
    tt_content.pxshopware_pi2 {
        10 = < lib.stdheader
        20 >
        20 = < plugin.tx_pxshopware_pi2
    }
    ', TRUE);

    /** @var array $version */
    $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(TYPO3_version);

    /**
     * For TYPO3 Versions newer than 7.2.x
     */
    if ($version['version_main'] >= 7) {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

        $iconRegistry->registerIcon(
            'px-shopware',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            array(
                'source' => 'EXT:' . $_EXTKEY . '/ext_icon.svg'
            )
        );

        /**
         * register icons for each plugin
         */
        $pluginSignatures = array(
            0 => str_replace('_', '', $_EXTKEY) . '_pi1',
            1 => str_replace('_', '', $_EXTKEY) . '_pi2'
        );
        foreach ($pluginSignatures as $pluginSignature) {
            $iconRegistry->registerIcon(
                str_replace('_', '-', $pluginSignature),
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                array(
                    'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/' . $pluginSignature . '.svg'
                )
            );
        }

    }

    /**
     * create one cache for each endpoint
     */
    $endpoints = array('articles', 'categories', 'media');
    foreach ($endpoints as $endpoint) {
        if (FALSE === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_' . $endpoint])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_' . $endpoint] = array(
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\StringFrontend::class,
                'options' => array(
                    'defaultLifetime' => 3600 // 1 hour cache lifetime
                ),
                'groups' => array('pages', 'all')
            );
        }
    }

};

$boot($_EXTKEY);
unset($boot);
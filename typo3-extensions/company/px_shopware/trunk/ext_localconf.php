<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    switch (TYPO3_MODE) {
        case 'FE':

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

            /**
             * add TS for each plugin
             */
            $pluginSignatures = array(
                0 => str_replace('_', '', $_EXTKEY) . '_pi1',
                1 => str_replace('_', '', $_EXTKEY) . '_pi2'
            );
            foreach ($pluginSignatures as $pluginSignature) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup',
                                                                                  '[GLOBAL]
                tt_content.' . $pluginSignature . ' = COA
                tt_content.' . $pluginSignature . ' {
                    10 = < lib.stdheader
                    20 >
                    20 = < plugin.tx_' . $pluginSignature . '
                }
                ', TRUE);
            }

            break;
        case 'BE':

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

                $iconRegistry->registerIcon(
                    'px-shopware-toolbar-icon',
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    array(
                        'source' => 'EXT:px_shopware/Resources/Public/Icons/toolbar_item.svg'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-shop-connected',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'chain'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-shop-disconnected',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'chain-broken'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-shop-version',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'cog'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-shop-revision',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'cogs'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-shop-shop',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'shopping-cart'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-clear-cache',
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    array(
                        'source' => 'EXT:px_shopware/Resources/Public/Icons/clear_cache.svg'
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

            // Hooks
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
                'tx_pxshopware::clearCache',
                'Portrino\\PxShopware\\Backend\\Hooks\\Ajax->clearCache',
                FALSE
            );

            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] =
                \Portrino\PxShopware\Backend\Toolbar\ClearCacheMenu::class;


            $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433105] =
                \Portrino\PxShopware\Backend\ToolbarItems\ShopwareConnectorInformationToolbarItem::class;

            /**
             * log all PxShopware errors into separate file
             */
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['Portrino']['PxShopware'] = array(
                \TYPO3\CMS\Core\Log\LogLevel::ERROR => array(
                    // add a FileWriter
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => array(
                        // configuration for the writer
                        'logFile' => 'typo3temp/logs/px_shopware.log'
                    )
                )
            );

            break;
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
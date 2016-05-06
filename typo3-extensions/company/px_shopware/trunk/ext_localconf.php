<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['px_shopware']);

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
                    'px-shopware-cache-level',
                    \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                    array(
                        'name' => 'arrow-circle-right'
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

                $iconRegistry->registerIcon(
                    'px-shopware-article',
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    array(
                        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/article.svg'
                    )
                );

                $iconRegistry->registerIcon(
                    'px-shopware-category',
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    array(
                        'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/category.svg'
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

                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:px_shopware/Configuration/PageTSconfig/pxshopware_pi1.ts">');
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:px_shopware/Configuration/PageTSconfig/pxshopware_pi2.ts">');

            }

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
//
//            $GLOBALS['TBE_MODULES']['_configuration'][$_EXTKEY] = array (
//                'jsFiles' => array (
//                    'EXT:' . $_EXTKEY . '/Resources/Public/Javascript/Backend/FormEngineSuggest.js',
//                ),
//            );

            break;
    }

    /**
     * if caching was not disabled create one cache for each endpoint
     */
    if ((boolean)$extConf['caching.']['disable'] != TRUE) {
        if (FALSE === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_level1'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_level1'] = array(
                'backend' => \TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\StringFrontend::class,
                'groups' => array('px_shopware')
            );
        }

        if (FALSE === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware'] = array(
                'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\StringFrontend::class,
                'options' => array(
                    'defaultLifetime' => (int)$extConf['caching.']['lifetime'] > 0 ? (int)$extConf['caching.']['lifetime'] : 3600
                ),
                'groups' => array('px_shopware')
            );
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['px_shopware']['cache_chain'] = array(
            0 => 'px_shopware_level1',
            1 => 'px_shopware',
        );
    }


};

$boot($_EXTKEY);
unset($boot);
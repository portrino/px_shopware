<?php
defined('TYPO3_MODE') || die();

(function () {
    $extensionKey = 'px_shopware';

    /** @var array $extConf */
    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get($extensionKey);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:px_shopware/Configuration/PageTSconfig/" extension="ts">'
    );


    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('solr')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessFetchRecordsForIndexQueueItem'][] =
            Portrino\PxShopware\Service\Solr\Hooks\Queue::class . '->postProcessFetchRecordsForIndexQueueItem';

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\ApacheSolrForTypo3\Solr\IndexQueue\Queue::class] = [
            'className' => \Portrino\PxShopware\Xclass\Solr\IndexQueue\Queue::class
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['shopware_article'] =
        Portrino\PxShopware\LinkResolver\ArticleLinkResolver::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['shopware_category'] =
        Portrino\PxShopware\LinkResolver\CategoryLinkResolver::class;

    switch (TYPO3_MODE) {
        case 'FE':

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'Portrino.' . $extensionKey,
                'Pi1',
                [
                    'Article' => 'list, listByCategories'
                ],
                []
            );

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'Portrino.' . $extensionKey,
                'Pi2',
                [
                    'Category' => 'list'
                ],
                []
            );

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                'Portrino.' . $extensionKey,
                'Notification',
                [
                    'Notification' => 'index'
                ],
                [
                    'Notification' => 'index'
                ]
            );

            /**
             * add TS for each plugin
             */
            $pluginSignatures = [
                0 => str_replace('_', '', $extensionKey) . '_pi1',
                1 => str_replace('_', '', $extensionKey) . '_pi2'
            ];
            foreach ($pluginSignatures as $pluginSignature) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($extensionKey, 'setup',
                    '[GLOBAL]
                tt_content.' . $pluginSignature . ' = COA
                tt_content.' . $pluginSignature . ' {
                    10 = < lib.stdheader
                    20 >
                    20 = < plugin.tx_' . $pluginSignature . '
                }
                ', true);
            }

            break;
        case 'BE':

            /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

            $iconRegistry->registerIcon(
                'px-shopware',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:' . $extensionKey . '/ext_icon.svg'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-toolbar-icon',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:px_shopware/Resources/Public/Icons/toolbar_item.svg'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-shop-connected',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'chain'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-shop-disconnected',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'chain-broken'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-shop-version',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'cog'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-shop-revision',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'cogs'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-cache-level',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'arrow-circle-right'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-shop-shop',
                \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
                [
                    'name' => 'shopping-cart'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-clear-cache',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/clear_cache.svg'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-article',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/article.svg'
                ]
            );

            $iconRegistry->registerIcon(
                'px-shopware-category',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/category.svg'
                ]
            );

            /**
             * register icons for each plugin
             */
            $pluginSignatures = [
                0 => str_replace('_', '', $extensionKey) . '_pi1',
                1 => str_replace('_', '', $extensionKey) . '_pi2'
            ];
            foreach ($pluginSignatures as $pluginSignature) {
                $iconRegistry->registerIcon(
                    str_replace('_', '-', $pluginSignature),
                    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                    [
                        'source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/' . $pluginSignature . '.svg'
                    ]
                );
            }

            // add new Shopware Suggest
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1552909662] = [
                'nodeName' => 'suggestWizardControl',
                'priority' => 30,
                'class' => \Portrino\PxShopware\Backend\FormEngine\FieldControl\SuggestWizardControl::class
            ];

            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] =
                \Portrino\PxShopware\Backend\Toolbar\ClearCacheMenu::class;


            $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433105] =
                \Portrino\PxShopware\Backend\ToolbarItems\ShopwareConnectorInformationToolbarItem::class;

            /**
             * hook for content element preview in backend
             */
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][] =
                \Portrino\PxShopware\Backend\Hooks\PageLayoutViewDraw::class;


            /**
             * log all PxShopware errors into separate file
             */
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['Portrino']['PxShopware'] = [
                \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
                    // add a FileWriter
                    \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                        // configuration for the writer
                        'logFile' => 'typo3temp/logs/px_shopware.log'
                    ]
                ]
            ];

            break;
    }

    /**
     * if caching was not disabled create one cache for each endpoint
     */
    if ((bool)$extConf['caching']['disable'] === false) {
        if (false === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_level1'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_level1'] = [
                'backend' => \TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'groups' => [$extensionKey]
            ];
        }

        if (false === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$extensionKey])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$extensionKey] = [
                'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'options' => [
                    'defaultLifetime' => (int)$extConf['caching']['lifetime'] > 0 ? (int)$extConf['caching']['lifetime'] : 3600
                ],
                'groups' => [$extensionKey]
            ];
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXT'][$extensionKey]['cache_chain'] = [
            0 => 'px_shopware_level1',
            1 => 'px_shopware',
        ];
    }

})();

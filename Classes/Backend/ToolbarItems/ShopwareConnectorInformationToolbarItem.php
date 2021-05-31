<?php
namespace Portrino\PxShopware\Backend\ToolbarItems;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Portrino\PxShopware\Cache\CacheChainFactory;
use Portrino\PxShopware\Domain\Model\Version;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Portrino\PxShopware\Service\Shopware\ShopClientInterface;
use Portrino\PxShopware\Service\Shopware\VersionClientInterface;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class ShopwareConnectorInformationToolbarItem
 *
 * @package Portrino\PxShopware\Backend\ToolbarItems
 */
class ShopwareConnectorInformationToolbarItem implements ToolbarItemInterface
{

    /**
     * @var StandaloneView
     */
    protected $standaloneView;

    /**
     * Template file for the dropdown menu
     */
    const TOOLBAR_MENU_TEMPLATE = 'ShopwareConnectorInformation.html';

    /**
     * The CSS class for the badge
     *
     * @var string
     */
    protected $severityBadgeClass = '';

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $extensionInformation = [];

    /**
     * @var array
     */
    protected $shopInformation = [];

    /**
     * @var array
     */
    protected $cacheInformation = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var VersionClientInterface
     */
    protected $versionClient;

    /**
     * @var ShopClientInterface
     */
    protected $shopClient;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var string Key of the extension
     */
    protected $extensionKey = 'px_shopware';

    /**
     * @var string Name of the extension
     */
    protected $extensionName = 'PxShopware';

    /**
     * @var string language prefix to prevent long expressions
     */
    protected $languagePrefix = 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:';

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!$this->checkAccess()) {
            return;
        }
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');

        if (!array_key_exists('api', $this->settings)) {
            $this->messages[] = [
                'status' => InformationStatus::STATUS_WARNING,
                'text' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.configuration.status.missing')
            ];
        }

        $this->versionClient = $this->objectManager->get(VersionClientInterface::class);
        $this->shopClient = $this->objectManager->get(ShopClientInterface::class);

        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $extPath = ExtensionManagementUtility::extPath('px_shopware');
        /* @var $view StandaloneView */
        $this->standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $this->standaloneView->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/Backend/ToolbarMenu/' . static::TOOLBAR_MENU_TEMPLATE);
    }

    /**
     * Collect the information for the menu
     *
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     * @throws \ReflectionException
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    protected function collectInformation()
    {

        $this->getExtensionInformation();

        $this->getShopStatus();

        $this->getShops();

        $this->getShopVersionAndRevision();

        $this->getCacheStatus();

        $this->severityBadgeClass = InformationStatus::STATUS_OK;

    }

    /**
     * Gets the connected Shops
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Package\Exception
     */
    protected function getExtensionInformation()
    {
        $this->extensionInformation['version'] = ExtensionManagementUtility::getExtensionVersion($this->extensionKey);
    }

    /**
     * Gets the connected Shops
     *
     * @return void
     */
    protected function getShops()
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Shop> $shops */
        $shops = $this->shopClient->findAll(false);

        $shopString = '';
        foreach ($shops as $shop) {
            $shopString .= $shop->getName() . '<br>';
        }

        if ($shops) {
            $this->shopInformation[] = [
                'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.shops'),
                'value' => $shopString,
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-shop', Icon::SIZE_SMALL)->render()
            ];
        }
    }

    /**
     * Gets the shop status
     *
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    protected function getShopStatus()
    {
        $status = $this->versionClient->getStatus();

        if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_FULL) {
            $status = InformationStatus::STATUS_OK;
            $value = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.connected_full');
            $icon = 'px-shopware-shop-connected';
            $messageText = LocalizationUtility::translate(
                $this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.connected_full.message',
                $this->extensionName,
                [
                    1 => $this->settings['emails']['portrino_support'],
                    2 => $this->settings['urls']['portrino_website']
                ]
            );
        } else {
            if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL) {
                $status = InformationStatus::STATUS_WARNING;
                $value = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.connected_trial');
                $icon = 'px-shopware-shop-connected';
                $messageText = LocalizationUtility::translate(
                    $this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.connected_trial.message',
                    $this->extensionName,
                    [
                        1 => $this->settings['urls']['shopware_plugin_repository'],
                        2 => $this->settings['emails']['portrino_support'],
                        3 => $this->settings['urls']['portrino_website']
                    ]
                );
            } else {
                $status = InformationStatus::STATUS_ERROR;
                $value = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.disconnected');
                $icon = 'px-shopware-shop-disconnected';
                $messageText = LocalizationUtility::translate(
                    $this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status.disconnected.message',
                    $this->extensionName,
                    [
                        1 => $this->settings['urls']['typo3_documentation'],
                        2 => $this->settings['emails']['portrino_support'],
                        3 => $this->settings['urls']['portrino_website']
                    ]
                );
            }
        }

        $this->shopInformation[] = [
            'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.status'),
            'value' => $value,
            'status' => $status,
            'icon' => $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render()
        ];

        $this->messages[] = [
            'status' => $status,
            'text' => $messageText
        ];
    }

    /**
     * Gets the Shop Version and Revision
     *
     * @return void
     */
    protected function getShopVersionAndRevision()
    {
        /** @var Version $version */
        $version = $this->versionClient->find();

        if ($version) {
            $this->shopInformation[] = [
                'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.version'),
                'value' => $version->getVersion(),
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-version', Icon::SIZE_SMALL)->render()
            ];

            $this->shopInformation[] = [
                'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.shop.revision'),
                'value' => $version->getRevision(),
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-revision', Icon::SIZE_SMALL)->render()
            ];
        }
    }

    /**
     * Gets the Cache Status
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function getCacheStatus()
    {
        $status = InformationStatus::STATUS_WARNING;
        /** @var CacheChainFactory $cacheChainFactory */
        $cacheChainFactory = GeneralUtility::makeInstance(CacheChainFactory::class);
        $cache = $cacheChainFactory->create();

        if ($cache->isActive()) {
            $status = InformationStatus::STATUS_OK;
            $value = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.cache.status.active');
        } else {
            $status = InformationStatus::STATUS_WARNING;
            $value = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.cache.status.inactive');
        }

        $this->cacheInformation[] = [
            'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.cache.status'),
            'value' => $value,
            'status' => $status,
            'icon' => $this->iconFactory->getIcon('information-database', Icon::SIZE_SMALL)->render()
        ];

        /**
         * detailed cache information retrieval
         */
        if ($cache->isActive()) {
            foreach ($cache->getBackends() as $priority => $backend) {
                $backendReflection = new \ReflectionClass($backend);
                $this->cacheInformation[] = [
                    'title' => $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.cache.level.' . $priority),
                    'value' => $backendReflection->getShortName(),
                    'icon' => $this->iconFactory->getIcon('px-shopware-cache-level', Icon::SIZE_SMALL)->render()
                ];

                if ($backend instanceof Typo3DatabaseBackend) {
                    $cacheTables = '';
                    foreach ($cache->getCacheTables() as $cacheTable) {
                        /** @var QueryBuilder $queryBuilder */
                        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($cacheTable);
                        $numberOfRecords = $queryBuilder
                            ->count('*')
                            ->from($cacheTable)
                            ->execute()
                            ->fetchColumn(0);

                        $cacheTables .= $cacheTable . '<br>(' . $numberOfRecords . ' ' . $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information.cache.caches.entries') . ') <br>';

                        if ($cacheTables !== '') {
                            $this->cacheInformation[] = [
                                'value' => $cacheTables,
                            ];
                        }
                    }
                }
            }

        }


    }

    /**
     * Checks whether the user has access to this toolbar item
     *
     * @return bool TRUE if user has access, FALSE if not
     */
    public function checkAccess()
    {
        return $this->getBackendUserAuthentication()->isAdmin();
    }

    /**
     * Render system information dropdown
     *
     * @return string Icon HTML
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function getItem()
    {
        $title = $this->getLanguageService()->sL($this->languagePrefix . 'toolbar_items.shopware_connector_information');
        $icon = $this->iconFactory->getIcon('px-shopware-toolbar-icon', Icon::SIZE_SMALL)->render('inline');

        $status = $this->versionClient->getStatus();

        if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_FULL) {
            $badgeClass = InformationStatus::STATUS_OK;
            $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-connected', Icon::SIZE_SMALL)->getMarkup();
        } else {
            if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL) {
                $badgeClass = InformationStatus::STATUS_WARNING;
                $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-connected', Icon::SIZE_SMALL)->getMarkup();
            } else {
                $badgeClass = InformationStatus::STATUS_ERROR;
                $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-disconnected', Icon::SIZE_SMALL)->getMarkup();
            }
        }

        return '<span title="' . $title . '">' . $icon . '<span style="display: block;" class="badge badge-' . $badgeClass . '">' . $badgeIcon . '</span></span>';
    }

    /**
     * Render drop down
     *
     * @return string Drop down HTML
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function getDropDown()
    {
        if (!$this->checkAccess()) {
            return '';
        }

        $this->collectInformation();

        $request = $this->standaloneView->getRequest();
        $request->setControllerExtensionName('px_shopware');

        $this->standaloneView->assignMultiple([
            'extensionInformation' => $this->extensionInformation,
            'shopInformation' => $this->shopInformation,
            'cacheInformation' => $this->cacheInformation,
            'messages' => $this->messages,
            'severityBadgeClass' => $this->severityBadgeClass,
        ]);

        return $this->standaloneView->render();
    }

    /**
     * No additional attributes needed.
     *
     * @return array
     */
    public function getAdditionalAttributes()
    {
        return [];
    }

    /**
     * This item has a drop down
     *
     * @return bool
     */
    public function hasDropDown()
    {
        return true;
    }

    /**
     * Position relative to others
     *
     * @return int
     */
    public function getIndex()
    {
        return 10;
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns current PageRenderer
     *
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }

    /**
     * Returns LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}

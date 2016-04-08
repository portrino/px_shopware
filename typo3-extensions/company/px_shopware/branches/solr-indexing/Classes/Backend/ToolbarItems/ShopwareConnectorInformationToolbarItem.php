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

use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ShopwareConnectorInformationToolbarItem
 *
 * @package Portrino\PxShopware\Backend\ToolbarItems
 */
class ShopwareConnectorInformationToolbarItem implements ToolbarItemInterface {

    /**
     * @var StandaloneView
     */
    protected $standaloneView = NULL;

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
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher = NULL;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var array
     */
    protected $shopInformation = array();

    /**
     * @var array
     */
    protected $cacheInformation = array();

    /**
     * @var \Portrino\PxShopware\Service\Shopware\VersionClientInterface
     */
    protected $versionClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ShopClientInterface
     */
    protected $shopClient;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var string Key of the extension
     */
    protected $extensionKey = 'px_shopware';


    /**
     * Constructor
     */
    public function __construct() {
        if (!$this->checkAccess()) {
            return;
        }
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->configurationManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class);
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');

        if ($this->settings === NULL) {
            $this->messages[] = array(
                'status' => InformationStatus::STATUS_WARNING,
                'text' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.configuration.status.missing', FALSE)
            );
        }

        $this->versionClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\VersionClientInterface::class);
        $this->shopClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\ShopClientInterface::class);


        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $extPath = ExtensionManagementUtility::extPath('px_shopware');
        /* @var $view StandaloneView */
        $this->standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $this->standaloneView->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/Backend/ToolbarMenu/' . static::TOOLBAR_MENU_TEMPLATE);
    }

    /**
     * Collect the information for the menu
     */
    protected function collectInformation() {

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
     */
    protected function getShops() {
        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Shop> $shops */
        $shops = $this->shopClient->findAll();

        $shopString = '';
        foreach ($shops as $shop) {
            $shopString .= $shop->getName() . '<br>';
        }

        if ($shops) {
            $this->shopInformation[] = array(
                'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.shops', TRUE),
                'value' => $shopString,
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-shop', Icon::SIZE_SMALL)->render()
            );
        }
    }

    /**
     * Gets the Shop Status
     *
     * @return void
     */
    protected function getShopStatus() {
        $status = $this->versionClient->getStatus();

        if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_FULL) {
            $status = InformationStatus::STATUS_OK;
            $value = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.connected_full', TRUE);
            $icon = 'px-shopware-shop-connected';
            $messageText = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.connected_full.message', FALSE);
        } else if  ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL) {
            $status = InformationStatus::STATUS_WARNING;
            $value = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.connected_trial', TRUE);
            $icon = 'px-shopware-shop-connected';
            $messageText = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.connected_trial.message', FALSE);
        } else {
            $status = InformationStatus::STATUS_ERROR;
            $value = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.disconnected', TRUE);
            $icon = 'px-shopware-shop-disconnected';
            $messageText = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status.disconnected.message', FALSE);
        }

        $this->shopInformation[] = array(
            'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.status', TRUE),
            'value' => $value,
            'status' => $status,
            'icon' => $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render()
        );

        $this->messages[] = array(
            'status' => $status,
            'text' => $messageText
        );
    }

    /**
     * Gets the Shop Version and Revision
     *
     * @return void
     */
    protected function getShopVersionAndRevision() {
        /** @var \Portrino\PxShopware\Domain\Model\Version $version */
        $version = $this->versionClient->find();

        if ($version) {
            $this->shopInformation[] = array(
                'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.version', TRUE),
                'value' => $version->getVersion(),
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-version', Icon::SIZE_SMALL)->render()
            );

            $this->shopInformation[] = array(
                'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.shop.revision', TRUE),
                'value' => $version->getRevision(),
                'icon' => $this->iconFactory->getIcon('px-shopware-shop-revision', Icon::SIZE_SMALL)->render()
            );
        }
    }

    /**
     * Gets the Cache Status
     *
     * @return void
     */
    protected function getCacheStatus() {

        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $caches = '';

        $status = InformationStatus::STATUS_WARNING;

        /**
         * create one cache for each endpoint
         */
        $endpoints = array('articles', 'categories', 'media', 'shops');
        foreach ($endpoints as $endpoint) {
            if ($cacheManager->hasCache($this->extensionKey . '_' . $endpoint)) {
                $cache = $cacheManager->getCache($this->extensionKey . '_' . $endpoint);
                $backend = $cache->getBackend();

                if ($backend instanceof \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend) {

                    $caches .= $backend->getCacheTable() . '<br>(' . (string) $this->getDatabaseConnection()->exec_SELECTcountRows('*', $backend->getCacheTable()) . ' ' .  $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.cache.caches.entries', TRUE) .') <br>';
                }
                $status = InformationStatus::STATUS_OK;
                $value = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.cache.status.active', TRUE);
            } else {
                $status = InformationStatus::STATUS_WARNING;
                $value = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.cache.status.inactive', TRUE);
            }
        }



        $this->cacheInformation[] = array(
            'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information.cache.status', TRUE),
            'value' => $value,
            'status' => $status,
            'icon' => $this->iconFactory->getIcon('sysinfo-database', Icon::SIZE_SMALL)->render()
        );

        if ($status ===  InformationStatus::STATUS_OK) {
            $this->cacheInformation[] = array(
                'value' => $caches,
            );
        }
    }

    /**
     * Checks whether the user has access to this toolbar item
     *
     * @return bool TRUE if user has access, FALSE if not
     */
    public function checkAccess() {
        return $this->getBackendUserAuthentication()->isAdmin();
    }

    /**
     * Render system information dropdown
     *
     * @return string Icon HTML
     */
    public function getItem() {
        $title = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:toolbar_items.shopware_connector_information', TRUE);
        $icon = $this->iconFactory->getIcon('px-shopware-toolbar-icon', Icon::SIZE_SMALL)->render('inline');

        $status = $this->versionClient->getStatus();

        if ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_FULL) {
            $badgeClass = InformationStatus::STATUS_OK;
            $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-connected', Icon::SIZE_SMALL)->getMarkup();
        } else if  ($status === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL) {
            $badgeClass = InformationStatus::STATUS_WARNING;
            $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-connected', Icon::SIZE_SMALL)->getMarkup();
        } else {
            $badgeClass = InformationStatus::STATUS_ERROR;
            $badgeIcon = $this->iconFactory->getIcon('px-shopware-shop-disconnected', Icon::SIZE_SMALL)->getMarkup();
        }

        return '<span title="' . $title . '">' . $icon . '<span style="display: block;" class="badge badge-' . $badgeClass . '">' . $badgeIcon . '</span></span>';
    }

    /**
     * Render drop down
     *
     * @return string Drop down HTML
     */
    public function getDropDown() {
        if (!$this->checkAccess()) {
            return '';
        }

        $this->collectInformation();

        $request = $this->standaloneView->getRequest();
        $request->setControllerExtensionName('px_shopware');

        $this->standaloneView->assignMultiple(array(
            'shopInformation' => $this->shopInformation,
            'cacheInformation' => $this->cacheInformation,
            'messages' => $this->messages,
            'severityBadgeClass' => $this->severityBadgeClass,
        ));

        return $this->standaloneView->render();
    }

    /**
     * No additional attributes needed.
     *
     * @return array
     */
    public function getAdditionalAttributes() {
        return array();
    }

    /**
     * This item has a drop down
     *
     * @return bool
     */
    public function hasDropDown() {
        return TRUE;
    }

    /**
     * Position relative to others
     *
     * @return int
     */
    public function getIndex() {
        return 10;
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication() {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns DatabaseConnection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection() {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Returns current PageRenderer
     *
     * @return PageRenderer
     */
    protected function getPageRenderer() {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService() {
        return $GLOBALS['LANG'];
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher() {
        if (!isset($this->signalSlotDispatcher)) {
            $this->signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)
                                                        ->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        }
        return $this->signalSlotDispatcher;
    }
}

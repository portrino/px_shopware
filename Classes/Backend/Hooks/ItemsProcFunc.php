<?php
namespace Portrino\PxShopware\Backend\Hooks;

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

use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\LocaleToShopMappingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class ItemsProcFunc
 *
 * @package Portrino\PxShopware\Backend\Hooks
 */
class ItemsProcFunc {

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var LocaleToShopMappingService
     */
    protected $localeToShopMappingService;

    /**
     * ItemsProcFunc constructor.
     *
     */
    public function __construct() {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->localeToShopMappingService = $this->objectManager->get(LocaleToShopMappingService::class);
    }

    /**
     * @param array $config
     * @param string $key
     */
    public function getItemsSelected(array &$config, $key) {
        $params = isset($config['config']['itemsProcFunc_params']) ? $config['config']['itemsProcFunc_params'] : array();
        $endpoint = isset($params['type']) ? $params['type'] : '';
        /**
         * check if the responsible shopwareApiClient interface and class exists for the given flexform type configuration
         */
        $shopwareApiClientInterface = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'ClientInterface';
        $shopwareApiClientClass = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'Client';
        if (!interface_exists($shopwareApiClientInterface)) {
            throw new ShopwareApiClientConfigurationException('The Interface:"' . $shopwareApiClientInterface . '" does not exist. Please check your type configuration in flexform config!', 1460126052);
        }

        if (!class_exists($shopwareApiClientClass)) {
            throw new ShopwareApiClientConfigurationException('The Class:"' . $shopwareApiClientClass . '" does not exist. Please check your type configuration in flexform config!', 1460126052);
        }

        /** @var AbstractShopwareApiClientInterface $shopwareApiClient */
        $shopwareApiClient = $this->objectManager->get($shopwareApiClientClass);

        $language = isset($config['flexParentDatabaseRow']['sys_language_uid']) ? $config['flexParentDatabaseRow']['sys_language_uid'] : 0;
        $shopId = $this->localeToShopMappingService->getShopIdBySysLanguageUid($language);

        /** @var array $selectedItems */
        $selectedItems = isset($config['row']['settings.items']) ? GeneralUtility::trimExplode(',', $config['row']['settings.items'], TRUE) : array();
        foreach ($selectedItems as $item) {
            /** @var ItemEntryInterface $selectedItem */
            $selectedItem = $shopwareApiClient->findById($item, FALSE, array('language' => $shopId));
            if ($selectedItem) {
                $selectedItemOption = array(
                    $selectedItem->getSelectItemLabel(),
                    $selectedItem->getSelectItemId()
                );
                array_push($config['items'], $selectedItemOption);
            }
        }
    }

    /**
     * @param array $config
     * @param string $key
     */
    public function getAllItems(array &$config, $key) {

        $params = isset($config['config']['itemsProcFunc_params']) ? $config['config']['itemsProcFunc_params'] : array();
        $endpoint = isset($params['type']) ? $params['type'] : '';
        /**
         * check if the responsible shopwareApiClient interface and class exists for the given flexform type configuration
         */
        $shopwareApiClientInterface = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'ClientInterface';
        $shopwareApiClientClass = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'Client';
        if (!interface_exists($shopwareApiClientInterface)) {
            throw new ShopwareApiClientConfigurationException('The Interface:"' . $shopwareApiClientInterface . '" does not exist. Please check your type configuration in flexform config!', 1460126052);
        }

        if (!class_exists($shopwareApiClientClass)) {
            throw new ShopwareApiClientConfigurationException('The Class:"' . $shopwareApiClientClass . '" does not exist. Please check your type configuration in flexform config!', 1460126052);
        }

        /** @var AbstractShopwareApiClientInterface $shopwareApiClient */
        $shopwareApiClient = $this->objectManager->get($shopwareApiClientClass);

        $language = isset($config['flexParentDatabaseRow']['sys_language_uid']) ? $config['flexParentDatabaseRow']['sys_language_uid'] : 0;
        $shopId = $this->localeToShopMappingService->getShopIdBySysLanguageUid($language);
        $items = $shopwareApiClient->findAll(TRUE, array('language' => $shopId));

        $i = 0;
        /** @var ItemEntryInterface $item */
        foreach ($items as $item) {
            $option = array(
                $item->getSelectItemLabel(),
                $item->getSelectItemId()
            );
            array_push($config['items'], $option);
            $i++;

            if ($i == 10) {
                break;
            }
        }
    }

}


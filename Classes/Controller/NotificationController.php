<?php

namespace Portrino\PxShopware\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\RecordMonitor\Helper\RootPageResolver;
use ApacheSolrForTypo3\Solr\GarbageCollector;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use Portrino\PxShopware\Service\Shopware\ConfigurationService;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\MediaClientInterface;
use Portrino\PxShopware\Service\Shopware\ShopClientInterface;
use Portrino\PxShopware\Service\Shopware\VersionClientInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class NotificationController extends ActionController
{
    const COMMAND_CREATE = 'create';

    const COMMAND_UPDATE = 'update';

    const COMMAND_DELETE = 'delete';

    const TYPE_ARTICLE = 'article';

    const TYPE_CATEGORY = 'category';

    const TYPE_MEDIA = 'media';

    const TYPE_SHOP = 'shop';

    const TYPE_VERSION = 'version';

    const SOLR_ITEM_TYPE_ARTICLE = 'Portrino_PxShopware_Domain_Model_Article';

    const SOLR_ITEM_TYPE_CATEGORY = 'Portrino_PxShopware_Domain_Model_Category';

    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var Queue
     */
    protected $indexQueue;

    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * @var ArticleClientInterface
     */
    protected $articleClient;

    /**
     * @var int
     */
    protected $currentTimeStamp;

    public function injectConfigurationService(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function injectArticleClient(ArticleClientInterface $articleClient)
    {
        $this->articleClient = $articleClient;
    }

    protected function resolveActionMethodName()
    {
        $actionMethodName = parent::resolveActionMethodName();
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];

        preg_match('/SW-TOKEN apikey="(?P<apikey>\w+)"/', $authorizationHeader, $matches);

        try {
            if (!in_array('apikey', $matches) && $matches['apikey'] !== $this->configurationService->getApiKey()) {
                $actionMethodName = 'authorizationErrorAction';
            }
        } catch (ShopwareApiClientConfigurationException $exception) {
            $actionMethodName = 'authorizationErrorAction';
        }

        return $actionMethodName;
    }

    protected function initializeAction()
    {
        $this->currentTimeStamp = time();

        if (ExtensionManagementUtility::isLoaded('solr') === true) {
            $this->indexQueue = GeneralUtility::makeInstance(Queue::class);
        }

        if ($this->request->getMethod() === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true);
            $this->request->setArgument('payload', $payload);
        }
        parent::initializeAction();
    }

    /**
     * @param array $payload
     * @return ResponseInterface
     */
    public function indexAction($payload)
    {
        foreach ($payload['data'] as $command) {
            $this->flushCacheForCommand($command['type'], (int)$command['id']);
            if (isset($command['id']) && $command['id'] !== '' && ExtensionManagementUtility::isLoaded('solr') === true) {
                switch ($command['action']) {
                    case self::COMMAND_CREATE:
                        $this->addItemToQueue($command['type'], (int)$command['id']);
                        break;
                    case self::COMMAND_UPDATE:
                        $this->updateItemInQueue($command['type'], (int)$command['id']);
                        break;
                    case self::COMMAND_DELETE:
                        $this->deleteItemFromQueueAndCore($command['type'], (int)$command['id']);
                        break;
                    default:
                        return new JsonResponse([
                            'status' => 'error',
                            'code' => 1471432314,
                            'message' => 'Invalid command type',
                        ], 400);
                }
            }
        }

        return new JsonResponse(null, 201);
    }

    /**
     * @return ResponseInterface
     */
    public function authorizationErrorAction()
    {
        return new JsonResponse([
            'status' => 'error',
            'code' => 1471432315,
            'message' => 'The given credentials are wrong!',
        ], 401);
    }

    /**
     * @param string $type
     * @param int $id
     */
    protected function addItemToQueue($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE:
                if ($this->indexQueue->containsItem(self::SOLR_ITEM_TYPE_ARTICLE, $id)) {
                    $this->updateItemInQueue($type, $id);
                    return;
                }
                $item = [
                    'item_type' => self::SOLR_ITEM_TYPE_ARTICLE,
                    'item_uid' => $id,
                    'indexing_configuration' => self::SOLR_ITEM_TYPE_ARTICLE,
                    'changed' => $this->currentTimeStamp,
                ];
                $this->addItem($item);
                break;
            case self::TYPE_CATEGORY:
                if ($this->indexQueue->containsItem(self::SOLR_ITEM_TYPE_CATEGORY, $id)) {
                    $this->updateItemInQueue($type, $id);
                    return;
                }
                $item = [
                    'item_type' => self::SOLR_ITEM_TYPE_CATEGORY,
                    'item_uid' => $id,
                    'indexing_configuration' => self::SOLR_ITEM_TYPE_CATEGORY,
                    'changed' => $this->currentTimeStamp,
                ];
                $this->addItem($item);
                break;
        }
    }

    /**
     * @param $item
     */
    protected function addItem($item)
    {
        $rootPageId = (GeneralUtility::makeInstance(RootPageResolver::class))->getRootPageId($GLOBALS['TSFE']->id);
        $item = array_merge(['root' => $rootPageId, 'errors' => ''], $item);

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $databaseConnectionForPages = $connectionPool->getConnectionForTable('tx_solr_indexqueue_item');
        $databaseConnectionForPages->insert(
            'tx_solr_indexqueue_item',
            $item
        );
    }

    /**
     * @param string $type
     * @param int $id
     */
    protected function updateItemInQueue($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE:
                $this->indexQueue->updateItem(self::SOLR_ITEM_TYPE_ARTICLE, $id, $this->currentTimeStamp);
                break;
            case self::TYPE_CATEGORY:
                $this->indexQueue->updateItem(self::SOLR_ITEM_TYPE_CATEGORY, $id, $this->currentTimeStamp);
                break;
        }
    }

    /**
     * @param string $type
     * @param int $id
     */
    protected function deleteItemFromQueueAndCore($type, $id)
    {
        /** @var GarbageCollector $garbageCollector */
        $garbageCollector = GeneralUtility::makeInstance(GarbageCollector::class);

        switch ($type) {
            case self::TYPE_ARTICLE:
                $garbageCollector->collectGarbage(self::SOLR_ITEM_TYPE_ARTICLE, $id);
                break;
            case self::TYPE_CATEGORY:
                $garbageCollector->collectGarbage(self::SOLR_ITEM_TYPE_CATEGORY, $id);
                break;
            default:
                $garbageCollector->collectGarbage($type, $id);
                break;
        }
    }

    /**
     * @param string $type
     * @param int $id
     */
    protected function flushCacheForCommand($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE:
                $tag = ArticleClientInterface::CACHE_TAG;
                break;
            case self::TYPE_CATEGORY:
                $tag = CategoryClientInterface::CACHE_TAG;
                break;
            case self::TYPE_MEDIA:
                $tag = MediaClientInterface::CACHE_TAG;
                break;
            case self::TYPE_SHOP:
                $tag = ShopClientInterface::CACHE_TAG;
                break;
            case self::TYPE_VERSION:
                $tag = VersionClientInterface::CACHE_TAG;
                break;
            default:
                $tag = 'shopware_' . $type;
        }

        $this->cacheManager->flushCachesByTag($tag);
        $this->cacheManager->flushCachesByTag($tag . '_' . (int)$id);
    }
}

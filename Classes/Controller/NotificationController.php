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

use ApacheSolrForTypo3\Solr\Util;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\MediaClientInterface;
use Portrino\PxShopware\Service\Shopware\ShopClientInterface;
use Portrino\PxShopware\Service\Shopware\VersionClientInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @property \TYPO3\CMS\Extbase\Mvc\Web\Response $response
 */
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
     * @var \Portrino\PxShopware\Service\Shopware\ConfigurationService
     * @inject
     */
    protected $configurationService;

    /**
     * @var \ApacheSolrForTypo3\Solr\IndexQueue\Queue
     */
    protected $indexQueue;

    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     * @inject
     */
    protected $cacheManager;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ArticleClientInterface
     * @inject
     */
    protected $articleClient;

    /**
     * @var int
     */
    protected $currentTimeStamp;

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
            $this->indexQueue = $this->objectManager->get(\ApacheSolrForTypo3\Solr\IndexQueue\Queue::class);
        }

        if ($this->request->getMethod() === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true);
            $this->request->setArgument('payload', $payload);
        }
        parent::initializeAction();
    }

    /**
     * @param array $payload
     * @return string
     */
    public function indexAction($payload)
    {
        foreach ($payload['data'] as $command) {
            $this->flushCacheForCommand($command['type'], intval($command['id']));
            if (isset($command['id']) && $command['id'] !== '' && ExtensionManagementUtility::isLoaded('solr') === true) {
                switch ($command['action']) {
                    case self::COMMAND_CREATE;
                        $this->addItemToQueue($command['type'], intval($command['id']));
                        break;
                    case self::COMMAND_UPDATE;
                        $this->updateItemInQueue($command['type'], intval($command['id']));
                        break;
                    case self::COMMAND_DELETE;
                        $this->deleteItemFromQueue($command['type'], intval($command['id']));
                        break;
                    default:
                        $this->response->setStatus(400);
                        return json_encode([
                            'status' => 'error',
                            'code' => 1471432314,
                            'message' => 'Invalid command type'
                        ]);
                }
            }
        }

        $this->response->setStatus(201);
        return '';
    }

    /**
     * @return string
     */
    public function authorizationErrorAction()
    {
        $this->response->setStatus(401);
        return json_encode([
            'status' => 'error',
            'code' => 1471432315,
            'message' => 'The given credentials are wrong!'
        ]);
    }

    /**
     * @param string $type
     * @param integer $id
     * @return void
     */
    protected function addItemToQueue($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE;
                if ($this->indexQueue->containsItem(self::SOLR_ITEM_TYPE_ARTICLE, $id)) {
                    return $this->updateItemInQueue($type, $id);
                }
                $item = [
                    'item_type' => self::SOLR_ITEM_TYPE_ARTICLE,
                    'item_uid' => $id,
                    'indexing_configuration' => self::SOLR_ITEM_TYPE_ARTICLE,
                    'changed' => $this->currentTimeStamp
                ];
                $this->addItem($item);
                break;
            case self::TYPE_CATEGORY;
                if ($this->indexQueue->containsItem(self::SOLR_ITEM_TYPE_CATEGORY, $id)) {
                    return $this->updateItemInQueue($type, $id);
                }
                $item = [
                    'item_type' => self::SOLR_ITEM_TYPE_CATEGORY,
                    'item_uid' => $id,
                    'indexing_configuration' => self::SOLR_ITEM_TYPE_CATEGORY,
                    'changed' => $this->currentTimeStamp
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
        $rootPageId = Util::getRootPageId($GLOBALS['TSFE']->id);
        $item = array_merge(['root' => $rootPageId, 'errors' => ''], $item);
        $GLOBALS['TYPO3_DB']->exec_INSERTquery(
            'tx_solr_indexqueue_item',
            $item
        );
    }

    /**
     * @param string $type
     * @param integer $id
     */
    protected function updateItemInQueue($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE;
                $this->indexQueue->updateItem(self::SOLR_ITEM_TYPE_ARTICLE, $id, null, $this->currentTimeStamp);
                break;
            case self::TYPE_CATEGORY;
                $this->indexQueue->updateItem(self::SOLR_ITEM_TYPE_CATEGORY, $id, null, $this->currentTimeStamp);
                break;
        }
    }

    /**
     * @param string $type
     * @param integer $id
     */
    protected function deleteItemFromQueue($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE;
                $this->indexQueue->deleteItem(self::SOLR_ITEM_TYPE_ARTICLE, $id);
                break;
            case self::TYPE_CATEGORY;
                $this->indexQueue->deleteItem(self::SOLR_ITEM_TYPE_CATEGORY, $id);
                break;
        }
    }

    /**
     * @param string $type
     * @param integer $id
     */
    protected function flushCacheForCommand($type, $id)
    {
        switch ($type) {
            case self::TYPE_ARTICLE;
                $tag = ArticleClientInterface::CACHE_TAG;
                break;
            case self::TYPE_CATEGORY;
                $tag = CategoryClientInterface::CACHE_TAG;
                break;
            case self::TYPE_MEDIA;
                $tag = MediaClientInterface::CACHE_TAG;
                break;
            case self::TYPE_SHOP;
                $tag = ShopClientInterface::CACHE_TAG;
                break;
            case self::TYPE_VERSION;
                $tag = VersionClientInterface::CACHE_TAG;
                break;
            default:
                $tag = 'shopware_' . $type;
        }

        $this->cacheManager->flushCachesByTag($tag);
        $this->cacheManager->flushCachesByTag($tag . '_' . (int)$id);
    }

}
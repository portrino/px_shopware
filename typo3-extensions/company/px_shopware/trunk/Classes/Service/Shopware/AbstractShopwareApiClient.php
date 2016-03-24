<?php
namespace Portrino\PxShopware\Service\Shopware;
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

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException;
use \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientRequestException;
use \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientJsonException;
use \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientResponseException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AbstractShopwareApiClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
abstract class AbstractShopwareApiClient implements \TYPO3\CMS\Core\SingletonInterface, AbstractShopwareApiClientInterface {

    const METHOD_GET    = 'GET';
    const METHOD_PUT    = 'PUT';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var array
     */
    protected $validMethods = array(
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE
    );

    /**
     * @var string
     */
    protected $apiUrl = '';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $cacheLifeTime = '';

    /**
     * @var resource
     */
    protected $cURL;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * the settings array of PxShopware Plugin
     *
     * @var array
     */
    protected $settings;

    /**
     * application context
     *
     * @var \TYPO3\CMS\Core\Core\ApplicationContext
     */
    protected $applicationContext;

    /**
     * itemRepository
     *
     * @var \Portrino\PxShopware\Domain\Repository\ItemRepository
     * @inject
     */
    protected $itemRepository;

    /**
     *
     */
    public function initializeObject() {
        $this->applicationContext = GeneralUtility::getApplicationContext();

        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,'PxShopware');

        $this->apiUrl = isset($this->settings['api']['url']) ? $this->settings['api']['url'] : FALSE;
        if ($this->apiUrl === FALSE) {
            throw new ShopwareApiClientConfigurationException('No apiUrl given to connect to shopware REST-Service! Please add it to your TS or Flexform.', 1458807513);
        }
        $this->apiUrl = rtrim($this->apiUrl, '/') . '/';

        $this->username = isset($this->settings['api']['username']) ? $this->settings['api']['username'] : FALSE;
        if ($this->username === FALSE) {
            throw new ShopwareApiClientConfigurationException('No username given to connect to shopware REST-Service! Please add it to your TS or Flexform.', 1458807514);
        }

        $this->apiKey = isset($this->settings['api']['key']) ? $this->settings['api']['key'] : FALSE;
        if ($this->apiKey === FALSE) {
            throw new ShopwareApiClientConfigurationException('No apiKey given to connect to shopware REST-Service! Please add it to your TS or Flexform.', 1458807515);
        }

        $this->cacheLifeTime = intval(empty($settings['cacheLifeTime']) ? 3600 : $settings['cacheLifeTime']);
        if ($this->cacheLifeTime == 0) {
            $this->cacheLifeTime = 3600;
        } //reset to 3600sec if intval() cant convert

        /**
         * curl initialization block
         */
        $this->cURL = curl_init();
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->cURL, CURLOPT_USERAGENT, 'Shopware ApiClient');
        curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($this->cURL, CURLOPT_USERPWD, $this->username . ':' . $this->apiKey);
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
        ));
    }

    /**
     * @param $url
     * @param string $method
     * @param array $data
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\Item
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function call($url, $method = self::METHOD_GET, $data = array(), $params = array()) {
        $item = NULL;
        $queryString = '';

        if (!empty($params)) {
            $queryString = http_build_query($params);
        }

        $url = rtrim($url, '?') . '?';
        $url = $this->apiUrl . $url . $queryString;

        /** @var \Portrino\PxShopware\Domain\Model\Item $item */
        $item = $this->itemRepository->findOneByTypeAndCacheIdentifier($url);
        /**
         * create new item object if it was not found
         */
        if ($item === NULL) {
            /** @var \Portrino\PxShopware\Domain\Model\Item $item */
            $item = $this->objectManager->get(\Portrino\PxShopware\Domain\Model\Item::class, $url);
        }

        /**
         * if no item was new or item should be updated (cacheLifeTime) then make an api request
         */
        if($item != NULL && ($item->_isNew() || $item->shouldBeUpdated($this->cacheLifeTime))) {
            try {

                if (!in_array($method, $this->validMethods)) {
                    throw new ShopwareApiClientRequestException('Invalid HTTP-Methode: ' . $method);
                }

                $dataString = json_encode($data);

                curl_setopt($this->cURL, CURLOPT_URL, $url);
                curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $dataString);

                $result   = curl_exec($this->cURL);
                $httpCode = curl_getinfo($this->cURL, CURLINFO_HTTP_CODE);

                $response = $this->prepareResponse($result, $httpCode);
                $item->setResult($response);

                if ($item->_isNew()) {
                    $this->itemRepository->add($item);
                } else if (!$item->_isNew()) {
                    $this->itemRepository->update($item);
                }
                $this->persistenceManager->persistAll();

            } catch (ShopwareApiClientException $exception) {

                if ($this->applicationContext->isDevelopment()) {
                    throw $exception;
                }
            }
        }

        return $item;
    }

    /**
     * @param $result
     * @param $httpCode
     *
     * @return mixed|string
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientJsonException
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientResponseException
     */
    protected function prepareResponse($result, $httpCode) {
        if (NULL === $decodedResult = json_decode($result, true)) {
            $jsonErrors = array(
                JSON_ERROR_NONE => 'No error occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached',
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded',
                JSON_ERROR_SYNTAX => 'Syntaxerror',
            );
            throw new ShopwareApiClientJsonException($jsonErrors[json_last_error()], 1458808216);
        }

        if (!isset($decodedResult['success'])) {
            throw new ShopwareApiClientResponseException('Invalid Response', 1458808324);
        }

        if (!$decodedResult['success']) {
            throw new ShopwareApiClientResponseException($decodedResult['message'], 1458808501);
        }

        if (isset($decodedResult['data'])) {
            return $result;
        }
    }

    /**
     * @param $url
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\Item
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function get($url, $params = array()) {
        return $this->call($url, self::METHOD_GET, array(), $params);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\Item
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function post($url, $data = array(), $params = array()) {
        return $this->call($url, self::METHOD_POST, $data, $params);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\Item
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function put($url, $data = array(), $params = array()) {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * @param $url
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\Item
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function delete($url, $params = array()) {
        return $this->call($url, self::METHOD_DELETE, array(), $params);
    }

    /**
     *
     */
    protected function getValidEndpoint() {
        return rtrim($this->getEndpoint(), '/') . '/';
    }

    /**
     * @return string
     */
    abstract protected function getEndpoint();

    /**
     * @return string
     */
    abstract protected function getEntityClassName();

    /**
     * @param int $id
     *
     * @return \Portrino\PxShopware\Domain\Model\Shopware\AbstractShopwareModel
     */
    public function findById($id) {
        $item = $this->get($this->getValidEndpoint() . $id);
        if ($item) {
            $result = $item->getResult();
            if (isset($result->data) && isset($result->data->id)) {
                /** @var \Portrino\PxShopware\Domain\Model\Shopware\AbstractShopwareModel $shopwareModel */
                $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $result->data);
            }
        }
        return $shopwareModel;
    }


    /**
     * @return \Portrino\PxShopware\Domain\Model\Shopware\AbstractShopwareModel
     */
    public function findAll() {
        $shopwareModels = new ObjectStorage();
        $item = $this->get($this->getValidEndpoint());
        if ($item) {
            $result = $item->getResult();
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data);
                        if ($shopwareModel != NULL) {
                            $shopwareModels->attach($shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }
}

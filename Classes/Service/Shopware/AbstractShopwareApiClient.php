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

use Portrino\PxShopware\Cache\CacheChainFactory;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientJsonException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientRequestException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientResponseException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AbstractShopwareApiClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
abstract class AbstractShopwareApiClient implements SingletonInterface, AbstractShopwareApiClientInterface
{

    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var array
     */
    protected $validMethods = [
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE
    ];

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
     * @var int
     */
    protected $cacheLifeTime = 0;

    /**
     * @var resource
     */
    protected $cURL;

    /**
     * the language id we should send to shopware API
     *
     * @var int
     */
    protected $shopId;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ConfigurationService
     * @inject
     */
    protected $configurationService;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService
     * @inject
     */
    protected $languageToShopMappingService;

    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    public function initializeObject()
    {
        if ($this->configurationService->isLoggingEnabled()) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        try {
            $this->apiUrl = $this->configurationService->getApiUrl();
            $this->username = $this->configurationService->getApiUsername();
            $this->apiKey = $this->configurationService->getApiKey();
        } catch (ShopwareApiClientConfigurationException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else {
                if (TYPO3_MODE === 'FE') {
                    throw $exception;
                }
            }
        }

        /**
         * curl initialization
         */
        $this->cURL = curl_init();
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->cURL, CURLOPT_USERAGENT, 'Shopware ApiClient');
        curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($this->cURL, CURLOPT_USERPWD, $this->username . ':' . $this->apiKey);
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
        ]);

        // cache initialization (if caching is not disabled!)
        if ($this->configurationService->isCachingEnabled()) {
            /** @var CacheChainFactory $cacheChainFactory */
            $cacheChainFactory = GeneralUtility::makeInstance(CacheChainFactory::class);
            $this->cache = $cacheChainFactory->create();
            $this->cacheLifeTime = $this->configurationService->getCacheLifeTime();
        }

        /**
         * Get the current language
         * -> depends on the TYPO3_MODE
         */
        if (TYPO3_MODE === 'FE') {
            /**
             * retrieve the language id from localeMappingService
             */
            $language = GeneralUtility::trimExplode('.', $GLOBALS['TSFE']->config['config']['sys_language_uid'], true);
            $language = ($language && isset($language[0])) ? $language[0] : 0;
            $this->shopId = $this->languageToShopMappingService->getShopIdBySysLanguageUid($language);
        } else {
            $this->shopId = null;
        }

    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @param array $params
     * @param bool $doCacheRequest
     * @return mixed
     * @throws ShopwareApiClientRequestException
     * @throws \Exception
     */
    public function call($endpoint, $method = self::METHOD_GET, $data = [], $params = [], $doCacheRequest = true)
    {
        $entry = null;

        ArrayUtility::mergeRecursiveWithOverrule($params, ['px_shopware' => 1]);

        if ($this->shopId && !array_key_exists('language', $params)) {
            ArrayUtility::mergeRecursiveWithOverrule($params, ['language' => $this->shopId]);
        }

        $queryString = http_build_query($params);

        $url = rtrim($endpoint, '?') . '?';
        $url = $this->apiUrl . $url . $queryString;

        /**
         * only cache when:
         *  - GET METHOD is used
         *  - doCacheRequest is TRUE
         *  - cache is available
         */
        $cacheIdentifier = sha1((string)$url);
        $cachingEnabled = ($method == self::METHOD_GET && $doCacheRequest === true && $this->cache !== null);


        if ($cachingEnabled && $this->cache->has($cacheIdentifier)) {
            return json_decode($this->cache->get($cacheIdentifier));
        }

        try {

            if (!in_array($method, $this->validMethods)) {
                throw new ShopwareApiClientRequestException('Invalid HTTP-Methode: ' . $method);
            }

            $dataString = json_encode($data);

            curl_setopt($this->cURL, CURLOPT_URL, $url);
            curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $dataString);

            $result = curl_exec($this->cURL);
            $entry = $this->prepareResponse($result);
            $result = json_decode($entry);

            /**
             * only cache when:
             *  - GET METHOD is used
             *  - doCacheRequest is TRUE
             *  - cache is available
             */
            if ($cachingEnabled) {
                $cacheTags = $this->generateCacheTags($endpoint, $result);
                $this->cache->set($cacheIdentifier, $entry, $cacheTags, $this->cacheLifeTime);
            }

            return $result;

        } catch (ShopwareApiClientException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else {
                if (TYPO3_MODE === 'FE') {
                    throw $exception;
                }
            }
        }
    }

    /**
     * @param string $result
     * @return mixed|string
     * @throws ShopwareApiClientJsonException
     * @throws ShopwareApiClientResponseException
     */
    protected function prepareResponse($result)
    {

        if (null === $decodedResult = json_decode($result, true)) {

            $jsonErrors = [
                JSON_ERROR_NONE => 'No error occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached',
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded',
                JSON_ERROR_SYNTAX => 'Syntaxerror',
            ];

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
     * @param string $endpoint
     * @param array $result
     * @return array
     */
    protected function generateCacheTags($endpoint, $result)
    {
        $urlParts = GeneralUtility::trimExplode('/', rtrim($endpoint, '/'));

        switch ($urlParts[0]) {
            case ArticleClientInterface::ENDPOINT:
                $cacheTag = ArticleClientInterface::CACHE_TAG;
                break;
            case CategoryClientInterface::ENDPOINT:
                $cacheTag = CategoryClientInterface::CACHE_TAG;
                break;
            case MediaClientInterface::ENDPOINT:
                $cacheTag = MediaClientInterface::CACHE_TAG;
                break;
            case ShopClientInterface::ENDPOINT:
                $cacheTag = ShopClientInterface::CACHE_TAG;
                break;
            case VersionClientInterface::ENDPOINT:
                $cacheTag = VersionClientInterface::CACHE_TAG;
                break;
            default:
                $cacheTag = 'showpare_' . $urlParts[0];
        }

        if (isset($urlParts[1]) && MathUtility::canBeInterpretedAsInteger($urlParts[1])) {
            $cacheTag .= '_' . $urlParts[1];
        }

        return [$cacheTag];
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $doCacheRequest
     *
     * @return mixed
     * @throws ShopwareApiClientException
     */
    public function get($url, $params = [], $doCacheRequest = true)
    {
        return $this->call($url, self::METHOD_GET, [], $params, $doCacheRequest);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     *
     * @return string
     * @throws ShopwareApiClientException
     */
    public function post($url, $data = [], $params = [])
    {
        return $this->call($url, self::METHOD_POST, $data, $params);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     *
     * @return string
     * @throws ShopwareApiClientException
     */
    public function put($url, $data = [], $params = [])
    {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * @param $url
     * @param array $params
     *
     * @return string
     * @throws ShopwareApiClientException
     */
    public function delete($url, $params = [])
    {
        return $this->call($url, self::METHOD_DELETE, [], $params);
    }

    /**
     * @param ShopwareApiClientException $exception
     */
    protected function logException($exception)
    {
        if ($this->logger !== null) {
            $this->logger->log(
                LogLevel::ERROR,
                $exception->getMessage(),
                [
                    'code' => $exception->getCode()
                ]
            );

            if (TYPO3_MODE === 'BE') {
                $GLOBALS['BE_USER']->writelog(4, 0, 1, 0, $exception->getMessage(), ['code' => $exception->getCode()]);
            }
        }
    }

    /**
     * @return string
     */
    public function getValidEndpoint()
    {
        return rtrim($this->getEndpoint(), '/') . '/';
    }

    /**
     * @return mixed
     */
    abstract protected function getEndpoint();

    /**
     * @return mixed
     */
    abstract protected function getEntityClassName();

    /**
     * @return bool
     * @throws ShopwareApiClientException
     */
    public function isConnected()
    {
        $result = false;
        $response = null;
        try {
            $response = $this->get('version', [], false);
            if ($response) {
                $result = ($response->success);
            }
        } catch (ShopwareApiClientException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else {
                if (TYPO3_MODE === 'FE') {
                    throw $exception;
                }
            }
            $result = false;
        } finally {
            return $result;
        }
    }

    /**
     * Returns one of the given states
     * - status_connected_full (TYPO3-Connector is installed on shopware system)
     * - status_connected_trial (TYPO3-Connector is NOT installed on shopware system - trial version)
     * - status_disconnected (No connection to shopware system possible)
     *
     * @return string
     * @throws ShopwareApiClientException
     */
    public function getStatus()
    {
        $result = self::STATUS_DISCONNECTED;
        $response = null;
        try {
            $response = $this->get('version', [], false);

            if ($response) {
                if ($response->success && isset($response->pxShopwareTypo3Token) && (boolean)$response->pxShopwareTypo3Token) {
                    $result = self::STATUS_CONNECTED_FULL;
                } else {
                    if ($response->success && !isset($response->pxShopwareTypo3Token)) {
                        $result = self::STATUS_CONNECTED_TRIAL;
                    }
                }
            }
        } catch (ShopwareApiClientException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else {
                if (TYPO3_MODE === 'FE') {
                    throw $exception;
                }
            }
            $result = self::STATUS_DISCONNECTED;
        } finally {
            return $result;
        }
    }

    /**
     * @return \Portrino\PxShopware\Domain\Model\AbstractShopwareModel
     */
    public function find()
    {
        $result = $this->get($this->getValidEndpoint());
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data)) {
                /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $result->data, $token);
                return $shopwareModel;
            }
        }
    }

    /**
     * @param int $id
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\ShopwareModelInterface
     */
    public function findById($id, $doCacheRequest = true, $params = [])
    {
        $result = $this->get($this->getValidEndpoint() . $id, $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && isset($result->data->id)) {
                /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $result->data, $token);
                return $shopwareModel;
            }
        }
    }

    /**
     * @param $term
     * @param int $limit
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = true, $params = [])
    {
        $shopwareModels = new ObjectStorage();

        ArrayUtility::mergeRecursiveWithOverrule($params, [
            'limit' => $limit,
            'sort' => [
                [
                    'property' => 'name',
                    'direction' => 'ASC'
                ]
            ],
            'filter' => [
                [
                    'property' => 'name',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ]
            ]
        ]);

        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != null) {
                            $shopwareModels->attach($shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }

    /**
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findAll($doCacheRequest = true, $params = [])
    {
        $shopwareModelArray = [];

        $params['limit'] = isset($params['limit']) ? $params['limit'] : 1000;
        $params['start'] = isset($params['start']) ? $params['start'] : 0;

            // first request has default limit of 1000
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {

            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != null) {
                            $shopwareModelArray[$shopwareModel->getId()] = $shopwareModel;
                        }
                    }
                }
                $total = $result->total;

                    // shop has more items than first request returned? poll again!
                if ($total > 0 && $total > count($shopwareModelArray) + $params['start']) {

                    $i = 0;
                        // safety break
                    while ($i < 99) {
                            // increase offset (called start in shopware)
                        $params['start'] = $params['start'] + $params['limit'];
                            // get API result
                        $additionalResults = $this->findByParams($params, $doCacheRequest);
                        foreach ($additionalResults as $additionalResult) {
                                // add new items to original array, if not already there
                            if (!array_key_exists($additionalResult->getId(), $shopwareModelArray)) {
                                $shopwareModelArray[$additionalResult->getId()] = $additionalResult;
                            }
                        }

                            // stop if API returns empty
                        if ($additionalResults->count() == 0) {
                            break;
                        }
                            // stop if total count is reached
                        if (count($shopwareModelArray) >= $total) {
                            break;
                        }
                        $i++;
                    }
                }
            }
        }

            // transform array of models to ObjectStorage
        $shopwareModels = new ObjectStorage();
        foreach ($shopwareModelArray as $shopwareModel) {
            $shopwareModels->attach($shopwareModel);
        }
        return $shopwareModels;
    }

    /**
     * @param array $params
     * @param bool $doCacheRequest
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByParams($params = [], $doCacheRequest = true)
    {
        $shopwareModels = new ObjectStorage();
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != null) {
                            $shopwareModels->attach($shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }

}

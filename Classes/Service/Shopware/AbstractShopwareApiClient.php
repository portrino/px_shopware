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
use Portrino\PxShopware\Domain\Model\AbstractShopwareModel;
use Portrino\PxShopware\Domain\Model\ShopwareModelInterface;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientJsonException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientRequestException;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientResponseException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AbstractShopwareApiClient
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
        self::METHOD_DELETE,
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
     * @var int|null
     */
    protected $shopId;

    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var LanguageToShopwareMappingService
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

    public function injectConfigurationService(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectLanguageToShopwareMappingService(
        LanguageToShopwareMappingService $languageToShopwareMappingService
    ) {
        $this->languageToShopMappingService = $languageToShopwareMappingService;
    }

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
            $this->logException($exception);
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
         */
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            /**
             * retrieve the language id from localeMappingService
             */
            $context = GeneralUtility::makeInstance(Context::class);
            $language = $context->getPropertyFromAspect('language', 'id');
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
     * @return \stdClass|null
     * @throws ShopwareApiClientRequestException
     * @throws \Exception
     */
    public function call(
        string $endpoint,
        string $method = self::METHOD_GET,
        array $data = [],
        array $params = [],
        bool $doCacheRequest = true
    ): ?\stdClass {
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
        $cachingEnabled = ($method === self::METHOD_GET && $doCacheRequest === true && $this->cache !== null);

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
            $this->logException($exception);
        }
        return null;
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
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached - Original Response:' . $result,
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded - Original Response:' . $result,
                JSON_ERROR_SYNTAX => 'Syntaxerror - Original Response:' . $result,
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
                $cacheTag = 'shopware_' . $urlParts[0];
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
     * @param string $url
     * @param array $data
     * @param array $params
     *
     * @return \stdClass|null
     * @throws ShopwareApiClientException
     */
    public function post(string $url, array $data = [], array $params = []): ?\stdClass
    {
        return $this->call($url, self::METHOD_POST, $data, $params);
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $params
     *
     * @return \stdClass|null
     * @throws ShopwareApiClientException
     */
    public function put(string $url, array $data = [], array $params = []): ?\stdClass
    {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * @param string $url
     * @param array $params
     *
     * @return \stdClass|null
     * @throws ShopwareApiClientException
     */
    public function delete(string $url, array $params = []): ?\stdClass
    {
        return $this->call($url, self::METHOD_DELETE, [], $params);
    }

    /**
     * @param ShopwareApiClientException $exception
     */
    protected function logException($exception): void
    {
        if ($this->logger !== null) {
            $this->logger->log(
                LogLevel::ERROR,
                $exception->getMessage(),
                [
                    'code' => $exception->getCode(),
                ]
            );

            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                $GLOBALS['BE_USER']->writelog(4, 0, 1, 0, $exception->getMessage(), ['code' => $exception->getCode()]);
            }
        }
    }

    /**
     * @return string
     */
    public function getValidEndpoint(): string
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
    public function isConnected(): bool
    {
        $result = false;
        try {
            $response = $this->get('version', [], false);
            if ($response) {
                $result = ($response->success);
            }
        } catch (ShopwareApiClientException $exception) {
            $this->logException($exception);
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
     */
    public function getStatus(): string
    {
        $result = self::STATUS_DISCONNECTED;
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
            $this->logException($exception);
        } finally {
            return $result;
        }
    }

    /**
     * @return ShopwareModelInterface|null
     * @throws ShopwareApiClientException
     */
    public function find(): ?ShopwareModelInterface
    {
        $response = $this->get($this->getValidEndpoint());
        $result = null;
        if ($response) {
            $token = (isset($response->pxShopwareTypo3Token)) ? (bool)$response->pxShopwareTypo3Token : false;
            if (isset($response->data)) {
                /** @var AbstractShopwareModel $shopwareModel */
                $shopwareModel = GeneralUtility::makeInstance($this->getEntityClassName());
                $shopwareModel->initialize($response->data, $token);
                $result = clone $shopwareModel;
            }
        }
        return $result;
    }

    /**
     * @param int $id
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return ShopwareModelInterface|null
     * @throws ShopwareApiClientException
     */
    public function findById($id, $doCacheRequest = true, $params = []): ?ShopwareModelInterface
    {
        $response = $this->get($this->getValidEndpoint() . $id, $params, $doCacheRequest);
        $result = null;
        if ($response) {
            $token = (isset($response->pxShopwareTypo3Token)) ? (bool)$response->pxShopwareTypo3Token : false;
            if (isset($response->data, $response->data->id)) {
                /** @var AbstractShopwareModel $shopwareModel */
                $shopwareModel = GeneralUtility::makeInstance($this->getEntityClassName());
                $shopwareModel->initialize($response->data, $token);
                $result = clone $shopwareModel;
            }
        }
        return $result;
    }

    /**
     * @param $term
     * @param int $limit
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return ObjectStorage<ShopwareModelInterface>
     * @throws ShopwareApiClientException
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = true, $params = []): ObjectStorage
    {
        ArrayUtility::mergeRecursiveWithOverrule($params, [
            'limit' => $limit,
            'sort' => [
                [
                    'property' => 'name',
                    'direction' => 'ASC',
                ],
            ],
            'filter' => [
                [
                    'property' => 'name',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%',
                ],
            ],
        ]);

        return $this->findByParams($params, $doCacheRequest);
    }

    /**
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return ObjectStorage<ShopwareModelInterface>
     */
    public function findAll($doCacheRequest = true, $params = []): ObjectStorage
    {
        $shopwareModelArray = [];

        $params['limit'] = $params['limit'] ?? 1000;
        $params['start'] = $params['start'] ?? 0;

        // first request has default limit of 1000
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var AbstractShopwareModel $shopwareModel */
                        $shopwareModel = GeneralUtility::makeInstance($this->getEntityClassName());
                        if ($shopwareModel !== null) {
                            $shopwareModel->initialize($data, $token);
                            $shopwareModelArray[$shopwareModel->getId()] = $shopwareModel;
                        }
                    }
                }
                $total = $result->total ?? 0;

                // shop has more items than first request returned? poll again!
                if ($total > 0 && $total > count($shopwareModelArray) + $params['start']) {
                    $i = 0;
                    // safety break
                    while ($i < 99) {
                        // increase offset (called start in shopware)
                        $params['start'] += $params['limit'];
                        // get API result
                        $additionalResults = $this->findByParams($params, $doCacheRequest);
                        foreach ($additionalResults as $additionalResult) {
                            // add new items to original array, if not already there
                            if (!array_key_exists($additionalResult->getId(), $shopwareModelArray)) {
                                $shopwareModelArray[$additionalResult->getId()] = $additionalResult;
                            }
                        }

                        // stop if API returns empty
                        if ($additionalResults->count() === 0) {
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
     * @return ObjectStorage<ShopwareModelInterface>
     */
    public function findByParams($params = [], $doCacheRequest = true): ObjectStorage
    {
        $shopwareModels = new ObjectStorage();
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var AbstractShopwareModel $shopwareModel */
                        $shopwareModel = GeneralUtility::makeInstance($this->getEntityClassName());
                        if ($shopwareModel !== null) {
                            $shopwareModel->initialize($data, $token);
                            $shopwareModels->attach(clone $shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }
}

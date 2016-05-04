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

use Portrino\PxShopware\Backend\Form\Wizard\CacheChain;
use Portrino\PxShopware\Backend\Form\Wizard\CacheChainManager;
use Portrino\PxShopware\Cache\CacheChainFactory;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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
     * @var string Key of the extension
     */
    protected $extensionName = 'PxShopware';

    /**
     * @var string Key of the extension
     */
    protected $extensionKey = 'px_shopware';

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
     * @var \Portrino\PxShopware\Service\Shopware\LocaleMappingService
     * @inject
     */
    protected $localeMappingService;

    /**
     * the extConf array of PxShopware extension
     *
     * @var array
     */
    protected $settings;

    /**
     * the settings array of PxShopware plugin
     *
     * @var array
     */
    protected $extConf;

    /**
     * application context
     *
     * @var \TYPO3\CMS\Core\Core\ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * the language id we should send to shopware API
     *
     * @var int
     */
    protected $language;

    /**
     * The user-object the script uses in backend mode
     *
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    public $BE_USER;


    /**
     *
     */
    public function initializeObject() {
        /** @var \TYPO3\CMS\Core\Log\LogManager $logger */
        $this->logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);

        if (TYPO3_MODE === 'BE') {
            $this->BE_USER = $GLOBALS['BE_USER'];
        }

        $this->applicationContext = GeneralUtility::getApplicationContext();
        /**
         * global extension configuration
         */
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['px_shopware']);
        $apiConfiguration = $this->extConf['api.'];
        $cacheConfiguration = $this->extConf['caching.'];

        /**
         * config from TS or flexform
         */
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');

        try {
            $this->apiUrl = (isset($this->settings['api']['url']) && $this->settings['api']['url'] != '') ? $this->settings['api']['url'] : (isset($apiConfiguration['url']) ? $apiConfiguration['url'] : FALSE);

            if ($this->apiUrl === FALSE) {
                throw new ShopwareApiClientConfigurationException('No apiUrl given to connect to shopware REST-Service! Please add it to your extension configuration, TS or flexform.', 1458807513);
            }

            if (filter_var($this->apiUrl, FILTER_VALIDATE_URL) === FALSE) {
                throw new ShopwareApiClientConfigurationException('apiUrl is not valid. Please enter a valid url in your extension configuration, TS or flexform.', 1459492118);
            }

            $this->apiUrl = rtrim($this->apiUrl, '/') . '/';

            $this->username = (isset($this->settings['api']['username']) && $this->settings['api']['username'] != '') ? $this->settings['api']['username'] : (isset($apiConfiguration['username']) ? $apiConfiguration['username'] : FALSE);
            if ($this->username === FALSE) {
                throw new ShopwareApiClientConfigurationException('No username given to connect to shopware REST-Service! Please add it to your extension configuration, TS or Flexform.', 1458807514);
            }

            $this->apiKey = (isset($this->settings['api']['key'])  && $this->settings['api']['key'] != '') ? $this->settings['api']['key'] : (isset($apiConfiguration['key']) ? $apiConfiguration['key'] : FALSE);
            if ($this->apiKey === FALSE) {
                throw new ShopwareApiClientConfigurationException('No apiKey given to connect to shopware REST-Service! Please add it to your extension configuration, TS or Flexform.', 1458807515);
            }

        } catch(ShopwareApiClientConfigurationException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else if (TYPO3_MODE === 'FE') {
                throw $exception;
            }
        }

        /**
         * curl initialization
         */
        $this->cURL = curl_init();
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($this->cURL, CURLOPT_USERAGENT, 'Shopware ApiClient');
        curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($this->cURL, CURLOPT_USERPWD, $this->username . ':' . $this->apiKey);
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
        ));

        /**
         * cache initialization (if caching is not disabled!)
         *
         * @var CacheChainManager $cacheChainManager
         *
         */
        if ((boolean)$cacheConfiguration['disable'] != TRUE) {
            /** @var CacheChainFactory $cacheChainFactory */
            $cacheChainFactory = GeneralUtility::makeInstance(CacheChainFactory::class);
            $this->cache = $cacheChainFactory->create();
            $this->cacheLifeTime = isset($settings['caching']['lifetime']) ? (int)$settings['caching']['lifetime'] : (isset($cacheConfiguration['lifetime']) ? (int)$cacheConfiguration['lifetime'] : 3600);
            //reset lifetime to 3600sec if 0 is set
            if ($this->cacheLifeTime == 0) {
                $this->cacheLifeTime = 3600;
            }
        } else {
            $this->cache = NULL;
        }

        /**
         * Get the current language
         * -> depends on the TYPO3_MODE
         */
        if (TYPO3_MODE === 'FE') {
            /**
             * retrieve the language id from localeMappingService
             */
            $locale = GeneralUtility::trimExplode('.', $GLOBALS['TSFE']->config['config']['locale_all'], TRUE);
            $locale = ($locale && isset($locale[0])) ? $locale[0] : $this->settings['api']['locale_all'];
            $this->language = $this->localeMappingService->getLanguageId($locale);
        } else {
            $this->language = NULL;
        }
    }

    /**
     * @param $url
     * @param string $method
     * @param array $data
     * @param array $params
     * @param bool $doCacheRequest
     *
     * @return mixed
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function call($url, $method = self::METHOD_GET, $data = array(), $params = array(), $doCacheRequest = TRUE) {
        $queryString = '';
        $entry = NULL;

        ArrayUtility::mergeRecursiveWithOverrule($params, array('px_shopware' => 1));

        if ($this->language) {
            ArrayUtility::mergeRecursiveWithOverrule($params, array('language' => $this->language));
        }

        $queryString = http_build_query($params);

        $url = rtrim($url, '?') . '?';
        $url = $this->apiUrl . $url . $queryString;

        /**
         * only cache when:
         *  - GET METHOD is used
         *  - doCacheRequest is TRUE
         *  - cache is available
         */
        if ($method == self::METHOD_GET && $doCacheRequest && $this->cache) {
            /**
             * try to get entry from cache
             */
            $cacheIdentifier = sha1((string)$url);
            $entry = $this->cache->get($cacheIdentifier);
        }

        /**
         * if no entry was found within cache then make an api request
         */
        if($entry === FALSE || $entry === NULL) {
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

                $entry = $this->prepareResponse($result, $httpCode);

                /**
                 * only cache when:
                 *  - GET METHOD is used
                 *  - doCacheRequest is TRUE
                 *  - cache is available
                 */
                if ($method == self::METHOD_GET && $doCacheRequest && $this->cache) {
                    $this->cache->set($cacheIdentifier, $entry, array(), $this->cacheLifeTime);
                }

                return json_decode($entry);

            } catch(ShopwareApiClientException $exception) {
                if (TYPO3_MODE === 'BE') {
                    $this->logException($exception);
                } else if (TYPO3_MODE === 'FE') {
                    throw $exception;
                }
            }
        } else {
            return json_decode($entry);
        }
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

         if (NULL === $decodedResult = json_decode($result, TRUE)) {

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
     * @param bool $doCacheRequest
     *
     * @return mixed
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function get($url, $params = array(), $doCacheRequest = TRUE) {
        return $this->call($url, self::METHOD_GET, array(), $params, $doCacheRequest);
    }

    /**
     * @param $url
     * @param array $data
     * @param array $params
     *
     * @return string
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
     * @return string
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function put($url, $data = array(), $params = array()) {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * @param $url
     * @param array $params
     *
     * @return string
     * @throws \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException
     */
    public function delete($url, $params = array()) {
        return $this->call($url, self::METHOD_DELETE, array(), $params);
    }

    /**
     * @param \Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException $exception
     */
    protected function logException($exception) {
        $loggingConfiguration = $this->extConf['logging.'];
        if ((boolean)$loggingConfiguration['disable'] != TRUE) {
            $this->logger->log(
                \TYPO3\CMS\Core\Log\LogLevel::ERROR,
                $exception->getMessage(),
                array(
                    'code' => $exception->getCode()
                )
            );
            $this->BE_USER->writelog(4, 0, 1, 0, $exception->getMessage(), array('code' => $exception->getCode()));
        }
    }

    /**
     * @return string
     */
    public function getValidEndpoint() {
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
     * @throws \ShopwareApiClientException
     */
    public function isConnected() {
        $result = FALSE;
        $response = NULL;
        try {
            $response = $this->get('version', array(), FALSE);
            if($response) {
                $result = ($response->success);
            }
        } catch (ShopwareApiClientException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else if (TYPO3_MODE === 'FE') {
                throw $exception;
            }
            $result = FALSE;
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
     * @throws \ShopwareApiClientException
     */
    public function getStatus() {
        $result = self::STATUS_DISCONNECTED;
        $response = NULL;
        try {
            $response = $this->get('version', array(), FALSE);

            if($response) {
                if ($response->success && isset($response->pxShopwareTypo3Token) && (boolean)$response->pxShopwareTypo3Token) {
                    $result = self::STATUS_CONNECTED_FULL;
                } else if ($response->success && !isset($response->pxShopwareTypo3Token)) {
                    $result = self::STATUS_CONNECTED_TRIAL;
                }
            }
        } catch (ShopwareApiClientException $exception) {
            if (TYPO3_MODE === 'BE') {
                $this->logException($exception);
            } else if (TYPO3_MODE === 'FE') {
                throw $exception;
            }
            $result = self::STATUS_DISCONNECTED;
        } finally {
            return $result;
        }
    }

    /**
     * @return \Portrino\PxShopware\Domain\Model\AbstractShopwareModel
     */
    public function find() {
        $result = $this->get($this->getValidEndpoint());
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data)) {
                /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $result->data, $token);
            }
        }
        return $shopwareModel;
    }

    /**
     * @param int $id
     * @param bool $doCacheRequest
     *
     * @return \Portrino\PxShopware\Domain\Model\ShopwareModelInterface
     */
    public function findById($id, $doCacheRequest = TRUE) {
        $result = $this->get($this->getValidEndpoint() . $id, array(), $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data) && isset($result->data->id)) {
                /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $result->data, $token);
            }
        }
        return $shopwareModel;
    }

    /**
     * @param $term
     * @param int $limit
     * @param bool $doCacheRequest
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = TRUE) {
        $shopwareModels = new ObjectStorage();

        $params = array(
            'limit' => $limit,
            'sort' => array(
                array(
                    'property' => 'name',
                    'direction' => 'ASC'
                )
            ),
            'filter' => array(
                array(
                    'property' => 'name',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                )
            )
        );

        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != NULL) {
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
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findAll($doCacheRequest = TRUE) {
        $shopwareModels = new ObjectStorage();
        $result = $this->get($this->getValidEndpoint(), array(), $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != NULL) {
                            $shopwareModels->attach($shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }


    /**
     * @param array $params
     * @param bool $doCacheRequest
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByParams($params = array(), $doCacheRequest = TRUE) {
        $shopwareModels = new ObjectStorage();
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
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

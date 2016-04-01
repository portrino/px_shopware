<?php
namespace Portrino\PxShopware\Controller;

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
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AbstractController
 *
 * @package Portrino\PxShopware\Controller
 */
abstract class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * application context
     *
     * @var \TYPO3\CMS\Core\Core\ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var \DateTime The current time
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $extConf = array();

    /**
     * @var string
     */
    protected $language = '';

    /**
     * contains the ts settings for the current action
     *
     * @var array
     */
    protected $actionSettings = array();

    /**
     * contains the specific ts settings for the current controller
     *
     * @var array
     */
    protected $controllerSettings = array();

    /**
     * contains the ts settings for the extbase mvc framework
     *
     * @var array
     */
    protected $extbaseFrameworkConfiguration = array();

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager = NULL;

    /**
     * @var string
     */
    protected $entityNotFoundMessage = 'The requested entity could not be found.';

    /**
     * @var string
     */
    protected $unknownErrorMessage = 'An unknown error occurred. We try to fix this as soon as possible.';

    /**
     * @var \Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface
     * @inject
     */
    protected $shopwareClient;

    /**
     * Initializes the controller before invoking an action method.
     *
     * Override this method to solve tasks which all actions have in
     * common.
     *
     * @return void
     */
    protected function initializeAction() {

        parent::initializeAction();
        $this->applicationContext = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
        $this->dateTime = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)]);
        $this->extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $this->controllerSettings = $this->settings['controllers'][$this->request->getControllerName()];
        $this->actionSettings = $this->controllerSettings['actions'][$this->request->getControllerActionName()];

        if (TYPO3_MODE === 'FE') {
            $this->typoScriptFrontendController = $GLOBALS['TSFE'];
            $this->language = $this->typoScriptFrontendController->config['config']['language'];
        }
    }

    /**
     * Initializes the view before invoking an action method.
     *
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     * @return void
     */
    protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
        parent::initializeView($view);
        $this->view->assignMultiple(array(
            'extbaseFrameworkConfiguration' => $this->extbaseFrameworkConfiguration,
            'controllerSettings' => $this->controllerSettings,
            'actionSettings' => $this->actionSettings,
            'extConf' => $this->extConf,
            'dateTime' => $this->dateTime,
            'language' => $this->language
        ));
    }

    /**
     * redirects to page
     *
     * @param null $pageUid
     * @param array $additionalParams
     * @param int $pageType
     * @param bool $noCache
     * @param bool $noCacheHash
     * @param string $section
     * @param bool $linkAccessRestrictedPages
     * @param bool $absolute
     * @param bool $addQueryString
     * @param array $argumentsToBeExcludedFromQueryString
     */
    protected function redirectToPage($pageUid = NULL, array $additionalParams = array(), $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $linkAccessRestrictedPages = FALSE, $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array()) {
        $uri = $this->uriBuilder
            ->reset()
            ->setTargetPageUid($pageUid)
            ->setTargetPageType($pageType)
            ->setNoCache($noCache)
            ->setUseCacheHash(!$noCacheHash)
            ->setSection($section)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri($absolute)
            ->setAddQueryString($addQueryString)
            ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
            ->build();

        $this->redirectToURI($uri);
    }

    /**
     * Deactivate FlashMessages for erros
     *
     * @see Tx_Extbase_MVC_Controller_ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage() {
        return FALSE;
    }

    /**
     * Returns the current view
     *
     * @return \TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    public function getView() {
        return $this->view;
    }

    /**
     * @return array
     */
    public function getActionSettings() {
        return $this->actionSettings;
    }

    /**
     * @return array
     */
    public function getControllerSettings() {
        return $this->controllerSettings;
    }

    /**
     * Handles a request. The result output is returned by altering the given response.
     *
     * We add some stuff to handle exceptions when we are in production context to prevent ugly extbase error messages
     *
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The request object
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response The response, modified by this handler
     *
     *
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Property\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @return void
     * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response) {
        try {
            parent::processRequest($request, $response);
        }
        catch(\Exception $exception) {

            if (TYPO3_DLOG) {
                GeneralUtility::devLog($exception->getMessage(), $this->extensionName, 1);
            }

            $applicationContext = GeneralUtility::getApplicationContext();
            if ($applicationContext->isProduction()) {
                // If the property mapper did throw a \TYPO3\CMS\Extbase\Property\Exception, because it was unable to find the requested entity, call the page-not-found handler.
                $previousException = $exception->getPrevious();
                if (($exception instanceof \TYPO3\CMS\Extbase\Property\Exception) && (($previousException instanceof \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException) || ($previousException instanceof \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException))) {
                    $this->typoScriptFrontendController->pageNotFoundAndExit();
                }
            }

            throw $exception;
        }
    }

    /**
     * Calls the specified action method and passes the arguments.
     *
     * If the action returns a string, it is appended to the content in the
     * response object. If the action doesn't return anything and a valid
     * view exists, the view is rendered automatically.
     *
     * @return void
     * @api
     * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected function callActionMethod() {
        try {
            parent::callActionMethod();
        }
        catch(\Exception $exception) {

            if (TYPO3_DLOG) {
                GeneralUtility::devLog($exception->getMessage(), $this->extensionName, 1);
            }

            if ($this->applicationContext->isProduction()) {
                // This enables you to trigger the call of TYPO3s page-not-found handler by throwing \TYPO3\CMS\Core\Error\Http\PageNotFoundException
                if ($exception instanceof \TYPO3\CMS\Core\Error\Http\PageNotFoundException) {
                    $GLOBALS['TSFE']->pageNotFoundAndExit($this->entityNotFoundMessage);
                }
            }

            throw $exception;
        }
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction() {
        $itemUidList = isset($this->settings['items']) ? GeneralUtility::trimExplode(',', $this->settings['items']) : array();
        $items = new ObjectStorage();
        foreach ($itemUidList as $itemUid) {
            if ($item = $this->shopwareClient->findById($itemUid)) {
                $items->attach($item);
            }
        }
        $this->view->assign('items', $items);
    }
}
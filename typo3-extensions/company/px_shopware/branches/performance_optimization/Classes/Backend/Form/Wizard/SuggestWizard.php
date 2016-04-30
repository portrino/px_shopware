<?php
namespace Portrino\PxShopware\Backend\Form\Wizard;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class SuggestWizard
 *
 * @package Portrino\PxShopware\Backend\Form\Wizard
 */
class SuggestWizard {

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $languagePrefix = 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:';

    /**
     * SuggestWizard constructor.
     */
    public function __construct() {
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Renders an ajax-enabled text field. Also adds required JS
     *
     * @param array $params the params given by TCA or Flexform config
     * @param AbstractFormElement $pObj
     *
     * @return string The HTML code for the selector
     */
    public function renderSuggestSelector($params, $pObj) {
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/PxShopware/FormEngineSuggest');
        $this->pageRenderer->addCssFile(
            ExtensionManagementUtility::extRelPath('px_shopware') . 'Resources/Public/Css/backend.css'
        );

        /**
         * get the specific endpoint from type
         */
        $endpoint = $params['params']['type'];

        $fieldname = $params['itemName'];

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

        $selector = '
        <label>&nbsp;</label>
        <div class="px-shopware autocomplete t3-form-suggest-container">
            <div class="input-group has-feedback">
                <span class="input-group-addon">' . $this->iconFactory->getIcon('actions-search', Icon::SIZE_SMALL)->render() . '</span>
                <input type="search" class="t3-form-suggest-px-shopware form-control" 
                        placeholder="' . $this->getLanguageService()->sL($this->languagePrefix . 'suggest_wizard.placeholder.' . strtolower($endpoint), FALSE) . '"
                        data-type="' . htmlspecialchars($endpoint) . '"
                        data-fieldname="' . htmlspecialchars($fieldname) . '" 
                />
                <span class="loading input-group-addon">
                    <i style="display: none;" id="loader" class="fa fa-circle-o-notch fa-spin"></i>
                </span>
            </div>
        </div>';
        return $selector;
    }


    /**
     * Ajax handler for the "suggest" feature in FormEngine.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function searchAction(ServerRequestInterface $request, ResponseInterface $response) {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        // Get parameters from $_GET/$_POST
        $search = isset($parsedBody['value']) ? $parsedBody['value'] : $queryParams['value'];
        $endpoint = isset($parsedBody['type']) ? $parsedBody['type'] : $queryParams['type'];

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

        $results = $shopwareApiClient->findByTerm($search, 8);
        /** @var SuggestEntryInterface $result */
        foreach ($results as $result) {
            /** @var SuggestEntryInterface $detailedResult */
            $detailedResult = $shopwareApiClient->findById($result->getSuggestId());

            $entry = array(
                'text' => '<span class="suggest-label">&nbsp;' . $this->highlight($detailedResult->getSuggestLabel(), $search) . '</span><br />
                                <span class="suggest-path"><i>' . $this->crop($detailedResult->getSuggestDescription(), 80) . '</i></span>',
                'label' => $detailedResult->getSuggestLabel(),
                'uid' => $detailedResult->getSuggestId(),
                'sprite' => $this->iconFactory->getIcon($detailedResult->getSuggestIconIdentifier(), Icon::SIZE_SMALL)->render()
            );
            $rows[$result->getId()] = $entry;
        }

        $response->getBody()->write(json_encode($rows));

        return $response;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService() {
        return $GLOBALS['LANG'];
    }

    /**
     * @param string $text
     * @param string $words
     *
     * @return mixed
     */
    protected function highlight($text, $words) {
        $highlighted = preg_filter('/' . preg_quote($words) . '/i', '<b><span class="search-highlight">$0</span></b>', $text);
        if (!empty($highlighted)) {
            $text = $highlighted;
        }
        return $text;
    }

    /**
     * @param string $string
     * @param int $limit
     *
     * @return string
     */
    private function crop($string, $limit) {
        if (strlen($string) > $limit) {
            return substr($string, 0, $limit) . '...';
        } else {
            return $string;
        }
    }
}

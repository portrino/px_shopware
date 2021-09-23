<?php
namespace Portrino\PxShopware\Backend\FormEngine\FieldControl;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Thomas Griessbach <griessbach@portrino.de>, portrino GmbH
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
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientConfigurationException;
use Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;


/**
 * Class SuggestWizardControl
 * Adds a wizard to select Shopware Items (Articles/Categories)
 *
 * @package Portrino\PxShopware\Backend\FormEngine\FieldControl
 */
class SuggestWizardControl extends AbstractNode
{

    /**
     * @var LanguageToShopwareMappingService
     */
    protected $localeToShopMappingService;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $languagePrefix = 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:';

    /**
     * SuggestWizardControl constructor.
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory = null, array $data = [])
    {
        if ($nodeFactory) {
            parent::__construct($nodeFactory, $data);
        }

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->localeToShopMappingService = $this->objectManager->get(LanguageToShopwareMappingService::class);
    }

    /**
     * @return array
     * @throws ShopwareApiClientConfigurationException
     */
    public function render(): array
    {
        $row = $this->data['databaseRow'];
        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $resultArray['requireJsModules'][] = 'TYPO3/CMS/PxShopware/FormEngineSuggest';
        $resultArray['stylesheetFiles'][] = ExtensionManagementUtility::extPath('px_shopware') . 'Resources/Public/Css/autocomplete.css';

        /**
         * get the specific endpoint from type
         */
        $endpoint = $paramArray['fieldConf']['config']['fieldWizard']['suggestWizardControl']['params']['type'];

        /*
         * get the minimal characters to trigger autosuggest from params
         */
        $minchars = isset($paramArray['fieldConf']['config']['fieldWizard']['suggestWizardControl']['params']['minchars']) ?
            (int)$paramArray['fieldConf']['config']['fieldWizard']['suggestWizardControl']['params']['minchars'] :
            5;

        $fieldname = $paramArray['itemFormElName'];

        if (isset($row['sys_language_uid'][0])) {
            $language = (int)$row['sys_language_uid'][0];
        }

        $selector = '
        <label>&nbsp;</label>
        <div class="px-shopware autocomplete t3-form-suggest-container">
            <div class="input-group has-feedback">
                <span class="input-group-addon">' . '' . '</span>
                <input type="search" class="t3-form-suggest-px-shopware form-control"
                        placeholder="' . $this->getLanguageService()->sL($this->languagePrefix . 'suggest_wizard.placeholder.' . strtolower($endpoint)) . '"
                        data-type="' . htmlspecialchars($endpoint) . '"
                        data-fieldname="' . htmlspecialchars($fieldname) . '"
                        data-language="' . $language . '"
                        data-minchars="' . $minchars . '"
                />
                <span class="loading input-group-addon">
                    <i style="display: none;" id="loader" class="fa fa-circle-o-notch fa-spin"></i>
                </span>
            </div>
        </div>';

        $resultArray['html'] = $selector;
        return $resultArray;
    }


    /**
     * Ajax handler for the "suggest" feature in FormEngine.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws ShopwareApiClientConfigurationException
     */
    public function searchAction(ServerRequestInterface $request)
    {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        // Get parameters from $_GET/$_POST
        $search = $parsedBody['value'] ?? $queryParams['value'];
        $endpoint = $parsedBody['type'] ?? $queryParams['type'];
        $language = isset($parsedBody['language']) ? (int)$parsedBody['language'] : (int)$queryParams['language'];
        // set language to 0 if no language was given
        if ($language < 0) {
            $language = 0;
        }

        /** @var AbstractShopwareApiClientInterface $shopwareApiClient */
        $shopwareApiClient = $this->objectManager->get($this->getShopwareApiClientClass($endpoint));

        $shopId = $this->localeToShopMappingService->getShopIdBySysLanguageUid($language);
        $results = $shopwareApiClient->findByTerm($search, 8, true, ['language' => $shopId]);

        /** @var SuggestEntryInterface $result */
        foreach ($results as $result) {
            $entry = [
                'text' => trim('
                    <span class="suggest-label">&nbsp;' . $this->highlight($result->getSuggestLabel(), $search) . '</span><br />
                    <span class="suggest-path"><i>' . $this->crop($result->getSuggestDescription(), 80) . '</i></span>
                '),
                'label' => $result->getSuggestLabel(),
                'uid' => $result->getSuggestId(),
                'sprite' => ''
            ];
            $rows[$result->getId()] = $entry;
        }

        return new JsonResponse($rows);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param string $endpoint
     * @return string
     * @throws ShopwareApiClientConfigurationException
     */
    protected function getShopwareApiClientClass($endpoint)
    {
        /**
         * check if the responsible shopwareApiClient interface and class exists for the given flexform type configuration
         */
        $shopwareApiClientInterface = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'ClientInterface';
        $shopwareApiClientClass = 'Portrino\\PxShopware\\Service\\Shopware\\' . $endpoint . 'Client';

        if (!interface_exists($shopwareApiClientInterface)) {
            throw new ShopwareApiClientConfigurationException(
                'The Interface:"' . $shopwareApiClientInterface . '" does not exist. Please check your type configuration in flexform config!',
                1460126052
            );
        }
        if (!class_exists($shopwareApiClientClass)) {
            throw new ShopwareApiClientConfigurationException(
                'The Class:"' . $shopwareApiClientClass . '" does not exist. Please check your type configuration in flexform config!',
                1460126052
            );
        }

        return $shopwareApiClientClass;
    }

    /**
     * @param string $text
     * @param string $words
     *
     * @return mixed
     */
    protected function highlight($text, $words)
    {
        $highlighted = preg_filter(
            '/' . preg_quote($words, '/') . '/i',
            '<b><span class="search-highlight">$0</span></b>',
            $text
        );
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
    private function crop($string, $limit)
    {
        $string = strip_tags($string);
        if (strlen($string) > $limit) {
            return substr($string, 0, $limit) . '...';
        }
        return $string;
    }
}

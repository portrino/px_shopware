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

use Portrino\PxDynamicContent\Domain\Model\Element;
use Portrino\PxDynamicContent\Domain\Model\ElementContainer;
use Portrino\PxDynamicContent\Domain\Repository\ElementRepository;
use Portrino\PxLib\Domain\Model\Content;
use Portrino\PxLib\Domain\Repository\ContentRepository;
use Portrino\PxLib\Utility\FlexformUtility;
use Portrino\PxShopware\Service\Shopware\ArticleClient;
use Portrino\PxShopware\Service\Shopware\LocaleToShopMappingService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\TtAddress\Hooks\DataHandler\BackwardsCompatibilityNameFormat;

/**
 * Class Pi1PageLayoutViewDraw
 *
 * @package Portrino\PxShopware\Backend\Hooks
 */
class Pi1PageLayoutViewDraw implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {

    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Configuration Manager
     *
     * @var BackendConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var LocaleToShopMappingService
     */
    protected $localeToShopMappingService;

    /**
     * @var FlexFormService
     */
    protected $flexFormService;

    /**
     * @var ArticleClient
     */
    protected $articleClient;

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected $view;

    /**
     * @var string
     */
    protected $extensionKey = 'px_shopware';

    /**
     * Pi1PageLayoutViewDraw constructor.
     *
     */
    public function __construct() {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->configurationManager = $this->objectManager->get(BackendConfigurationManager::class);
        $this->settings = $this->configurationManager->getConfiguration('PxShopware');
        $this->typoScriptService = $this->objectManager->get(TypoScriptService::class);
        $this->flexFormService = $this->objectManager->get(FlexFormService::class);
        $this->articleClient = $this->objectManager->get(ArticleClient::class);
        $this->localeToShopMappingService = $this->objectManager->get(LocaleToShopMappingService::class);

        /**
         * initialize the view
         */
        $this->view = $this->objectManager->get(StandaloneView::class);
        $this->view->setTemplateRootPaths(array(0 => 'EXT:' . $this->extensionKey . '/Resources/Private/Templates/Backend/'));
        $this->view->setTemplate('Pi1PageLayoutViewDraw');
    }


    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param boolean                                $drawItem Whether to draw the item using the default functionalities
     * @param string                                 $headerContent Header content
     * @param string                                 $itemContent Item content
     * @param array                                  $row Record row of tt_content
     *
     * @return void
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
        if ($row['CType'] !== 'pxshopware_pi1') {
            return;
        }

        $flexformConfiguration = $this->flexFormService->convertFlexFormContentToArray($row['pi_flexform']);

        /** @var ObjectStorage $selectedItems */
        $selectedItems = new ObjectStorage();
        $selectedItemsArray = isset($flexformConfiguration['settings']['items']) ? GeneralUtility::trimExplode(',', $flexformConfiguration['settings']['items'], TRUE) : array();

        foreach ($selectedItemsArray as $item) {
            $language = $this->localeToShopMappingService->getShopIdBySysLanguageUid($row['sys_language_uid']);
            /** @var ItemEntryInterface $selectedItem */
            $selectedItem = $this->articleClient->findById($item, FALSE, array('language' => $language));

            if ($selectedItem) {
                $selectedItems->attach($selectedItem);
            }
        }

        $this->view->assign('selectedItems', $selectedItems);


        $pageTsConfig = BackendUtility::getTCEFORM_TSconfig('tt_content', $row);
        $pageTsConfig = $this->typoScriptService->convertTypoScriptArrayToPlainArray($pageTsConfig['pi_flexform']);

        $this->view->assign(
            'template',
            array(
                0 => $pageTsConfig['pxshopware_pi1']['sDEF']['settings.template']['addItems'][$flexformConfiguration['settings']['template']]
            )
        );
        $this->view->assign('row', $row);

        $itemContent = $this->view->render();
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
}

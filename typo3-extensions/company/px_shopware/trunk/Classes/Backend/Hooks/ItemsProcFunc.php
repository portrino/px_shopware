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

use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Domain\Model\Shopware\Article;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ItemsProcFunc
 *
 * @package Portrino\PxShopware\Backend\Hooks
 */
class ItemsProcFunc {

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ArticleClient
     */
    protected $articleClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CategoryClient
     */
    protected $categoryClient;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * ItemsProcFunc constructor.
     *
     */
    public function __construct() {
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->articleClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\ArticleClient::class);
        $this->categoryClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\CategoryClient::class);
    }

    /**
     * @param array  $config
     * @param string $key
     */
    public function getArticles(array &$config, $key) {
        $articles = $this->articleClient->findAll();
        /** @var Article $article */
        foreach ($articles as $article) {

            $name = $article->getName() . ' [' . $article->getId() . ']';
            $orderNumber = !empty($article->getOrdnerNumber()) ? ' (' . $article->getOrdnerNumber() .')' : '';
            $name .= $orderNumber;

            $articleOption = array(
                $name,
                $article->getId()
            );
            array_push($config['items'], $articleOption);
        }
    }

    /**
     * @param array  $config
     * @param string $key
     */
    public function getCategories(array &$config, $key) {
        $categories = $this->categoryClient->findAll();
        /** @var Category $category */
        foreach ($categories as $category) {
            $name = $category->getName() . ' [' . $category->getId() . ']';
            $categoryOption = array(
                $name,
                $category->getId()
            );
            array_push($config['items'], $categoryOption);
        }
    }
}


<?php
namespace Portrino\PxShopware\Service\Solr\Indexer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Thomas Griessbach <griessbach@portrino.de>, portrino GmbH
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

use ApacheSolrForTypo3\Solr\IndexQueue\Item;
use Portrino\PxShopware\Domain\Model\Category;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ApacheSolrForTypo3\Solr\Util;

/**
 * Class CategoryIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class CategoryIndexer extends AbstractShopwareIndexer {


    /**
     * get Article Data from shopware API
     *
     * @param Item $item The item to index
     * @param integer $language The language to use.
     * @return Category The record to use to build the base document
     */
    protected function getShopwareRecord(Item $item, $language = 0) {

            // get Data from shopware API
        $shopwareClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\CategoryClient::class);

        $shopId = $this->languageToShopMappingService->getShopIdBySysLanguageUid($language);

        return $shopwareClient->findById($item->getRecordUid(), TRUE, array('language' => $shopId));
    }


    /**
     * overwrite special fields for categories
     *
     * @param \Apache_Solr_Document $itemDocument
     * @param Category $category
     * @return \Apache_Solr_Document $itemDocument
     */
    protected function overwriteSpecialFields(\Apache_Solr_Document $itemDocument, Category $category) {

        $itemDocument->setField('title', $category->getName());
        if (is_object($category->getRaw()) && is_string($category->getRaw()->metaDescription) && $category->getRaw()->metaDescription != '') {
            $itemDocument->setField('description', $category->getRaw()->metaDescription);
        }

        if ($category->getImage()) {
            $itemDocument->setField('image_stringS', $category->getImage()->getUrl());
        }

        return $itemDocument;
    }
}
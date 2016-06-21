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
use Portrino\PxShopware\Domain\Model\Article;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ArticleIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class ArticleIndexer extends AbstractShopwareIndexer {


    /**
     * get Article Data from shopware API
     *
     * @param Item $item The item to index
     * @param integer $language The language to use.
     * @return Article The record to use to build the base document
     */
    protected function getShopwareRecord(Item $item, $language = 0) {

        // get Data from shopware API
        $shopwareClient = $this->objectManager->get(\Portrino\PxShopware\Service\Shopware\ArticleClient::class);

        $shopId = $this->languageToShopMappingService->getShopIdBySysLanguageUid($language);

        return $shopwareClient->findById($item->getRecordUid(), TRUE, array('language' => $shopId));
    }


    /**
     * check if record should be added/updated or deleted from index
     *
     * @param Article $article The item to index
     * @return bool valid or not
     */
    protected function itemIsValid(Article $article) {

        $result = parent::itemIsValid($article);

            // ALSO check for categories, if article has no category shopware will throw error! so do not add to search result!
        if (!isset($article->getRaw()->categories) || $article->getRaw()->categories == FALSE) {
            $result = FALSE;
        }

        return $result;
    }


    /**
     * overwrite special fields for articles
     *
     * @param \Apache_Solr_Document $itemDocument
     * @param Article $article
     * @return \Apache_Solr_Document $itemDocument
     */
    protected function overwriteSpecialFields(\Apache_Solr_Document $itemDocument, Article $article) {

        $itemDocument->setField('title', $article->getName());

        if ($article->getRaw()->keywords) {
            $itemDocument->setField('keywords', GeneralUtility::trimExplode(',', $article->getRaw()->keywords, true));
        }

        if ($article->getChanged()->getTimestamp()) {
            $itemDocument->setField('changed', $article->getChanged()->getTimestamp());
        }

        $itemDocument->setField('description', trim(strip_tags($article->getDescription())));

        if ($article->getFirstImage()) {
            $itemDocument->setField('image_stringS', $article->getFirstImage()->getUrl());
        }

        if ($article->getCategories()->count() > 0) {
            $categoryNames = array();
            /** @var \Portrino\PxShopware\Domain\Model\Category $category */
            foreach ($article->getCategories() as $category) {
                $categoryNames[] = $category->getName();
            }
            $itemDocument->setField('category_stringM', array_unique($categoryNames));
            $itemDocument->setField('category_textM', array_unique($categoryNames));
        }

        if (is_object($article->getRaw()) && is_object($article->getRaw()->tax)) {
            $itemDocument->setField('tax_doubleS', $article->getRaw()->tax->tax);
            $itemDocument->setField('taxName_stringS', $article->getRaw()->tax->name);
        }


        if (is_object($article->getRaw()) && is_object($article->getRaw()->mainDetail)) {
            if ($article->getRaw()->mainDetail->number) {
                $itemDocument->setField('productNumber_stringS', $article->getRaw()->mainDetail->number);
                $itemDocument->setField('productNumber_textS', $article->getRaw()->mainDetail->number);
            }
            if ($article->getRaw()->mainDetail->unitId) {
                $itemDocument->setField('unitId_stringS', $article->getRaw()->mainDetail->unitId);
            }
            if ($article->getRaw()->mainDetail->packUnit) {
                $itemDocument->setField('packUnit_stringS', $article->getRaw()->mainDetail->packUnit);
            }
            if ($article->getRaw()->mainDetail->purchaseUnit) {
                $itemDocument->setField('purchaseUnit_tdoubleS', $article->getRaw()->mainDetail->purchaseUnit);
            }
            if ($article->getRaw()->mainDetail->referenceUnit) {
                $itemDocument->setField('referenceUnit_tdoubleS', $article->getRaw()->mainDetail->referenceUnit);
            }

            if (is_array($article->getRaw()->mainDetail->prices) && count($article->getRaw()->mainDetail->prices) > 0) {
                $itemDocument->setField('price_tDoubleS', $article->getRaw()->mainDetail->prices[0]->price);
                $itemDocument->setField('pseudoPrice_tDoubleS', $article->getRaw()->mainDetail->prices[0]->pseudoPrice);
            }
        }
        if (is_object($article->getRaw()->supplier)) {
            $itemDocument->setField('supplier_stringS', $article->getRaw()->supplier->name);
            $itemDocument->setField('supplier_textS', $article->getRaw()->supplier->name);
        }

        return $itemDocument;
    }
}
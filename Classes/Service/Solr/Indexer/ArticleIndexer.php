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

use Portrino\PxShopware\Domain\Model\AbstractShopwareModel;
use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Domain\Model\Detail;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ArticleIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class ArticleIndexer extends AbstractShopwareIndexer
{

    /**
     * @var string
     */
    protected $clientClassName = ArticleClientInterface::class;

    /**
     * check if record should be added/updated or deleted from index
     *
     * @param AbstractShopwareModel $itemRecord The item to index
     * @return bool valid or not
     */
    protected function itemIsValid(AbstractShopwareModel $itemRecord)
    {
        $result = parent::itemIsValid($itemRecord);

        if ($itemRecord instanceof Article) {
            // ALSO check for categories, if article has no category shopware will throw error! so do not add to search result!
            if (!isset($itemRecord->getRaw()->categories) || $itemRecord->getRaw()->categories === false) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * overwrite special fields for articles
     *
     * @param \Apache_Solr_Document $itemDocument
     * @param AbstractShopwareModel $itemRecord
     * @param integer $language The language to use.
     * @return \Apache_Solr_Document $itemDocument
     */
    protected function overwriteSpecialFields(
        \Apache_Solr_Document $itemDocument,
        AbstractShopwareModel $itemRecord,
        $language = 0
    ) {
        if ($itemRecord instanceof Article) {
            $itemDocument->setField('title', $itemRecord->getName());

            if ($itemRecord->getRaw()->keywords) {
                $itemDocument->setField(
                    'keywords',
                    GeneralUtility::trimExplode(',', $itemRecord->getRaw()->keywords, true)
                );
            }

            if ($itemRecord->getChanged()->getTimestamp()) {
                $itemDocument->setField('changed', $itemRecord->getChanged()->getTimestamp());
            }

            $itemDocument->setField('description', trim(strip_tags($itemRecord->getDescription())));
            $itemDocument->setField('descriptionLong_textS', trim(strip_tags($itemRecord->getDescriptionLong())));

            if ($itemRecord->getTeaserImage()) {
                $itemDocument->setField('image_stringS', $itemRecord->getTeaserImage()->getUrl());
            }

            if ($itemRecord->getCategories()->count() > 0) {
                $categoryNames = [];
                /** @var Category $category */
                foreach ($itemRecord->getCategories() as $category) {
                    if ($category->getLanguage() === $language) {
                        $categoryNames[] = $category->getName();
                    }
                }

                $itemDocument->setField('category_stringM', array_unique($categoryNames));
                $itemDocument->setField('category_textM', array_unique($categoryNames));
            }

            if ($itemRecord->getDetails()->count() > 0) {
                $detailLabels = [];
                /** @var Detail $detail */
                foreach ($itemRecord->getDetails() as $detail) {
                    $detailLabels[] = $detail->getNumber() . ' (' . $detail->getAdditionalText() . ')';
                }

                $itemDocument->setField('details_stringM', array_unique($detailLabels));
                $itemDocument->setField('details_textM', array_unique($detailLabels));
            }

            if (is_object($itemRecord->getRaw()) && is_object($itemRecord->getRaw()->tax)) {
                $itemDocument->setField('tax_doubleS', $itemRecord->getRaw()->tax->tax);
                $itemDocument->setField('taxName_stringS', $itemRecord->getRaw()->tax->name);
            }

            if (is_object($itemRecord->getRaw()) && is_object($itemRecord->getRaw()->mainDetail)) {
                if ($itemRecord->getRaw()->mainDetail->number) {
                    $itemDocument->setField('productNumber_stringS', $itemRecord->getRaw()->mainDetail->number);
                    $itemDocument->setField('productNumber_textS', $itemRecord->getRaw()->mainDetail->number);
                }
                if ($itemRecord->getRaw()->mainDetail->ean) {
                    $itemDocument->setField('ean_textS', $itemRecord->getRaw()->mainDetail->ean);
                }
                if ($itemRecord->getRaw()->mainDetail->additionalText) {
                    $itemDocument->setField('additionalText_textS', $itemRecord->getRaw()->mainDetail->additionalText);
                }
                if ($itemRecord->getRaw()->mainDetail->unitId) {
                    $itemDocument->setField('unitId_stringS', $itemRecord->getRaw()->mainDetail->unitId);
                }
                if ($itemRecord->getRaw()->mainDetail->packUnit) {
                    $itemDocument->setField('packUnit_stringS', $itemRecord->getRaw()->mainDetail->packUnit);
                }
                if ($itemRecord->getRaw()->mainDetail->purchaseUnit) {
                    $itemDocument->setField('purchaseUnit_tdoubleS', $itemRecord->getRaw()->mainDetail->purchaseUnit);
                }
                if ($itemRecord->getRaw()->mainDetail->referenceUnit) {
                    $itemDocument->setField('referenceUnit_tdoubleS', $itemRecord->getRaw()->mainDetail->referenceUnit);
                }

                if (is_array($itemRecord->getRaw()->mainDetail->prices) && count($itemRecord->getRaw()->mainDetail->prices) > 0) {
                    $itemDocument->setField('price_tDoubleS', $itemRecord->getRaw()->mainDetail->prices[0]->price);
                    $itemDocument->setField(
                        'pseudoPrice_tDoubleS',
                        $itemRecord->getRaw()->mainDetail->prices[0]->pseudoPrice
                    );
                }
            }
            if (is_object($itemRecord->getRaw()->supplier)) {
                $itemDocument->setField('supplier_stringS', $itemRecord->getRaw()->supplier->name);
                $itemDocument->setField('supplier_textS', $itemRecord->getRaw()->supplier->name);
            }
        }

        return $itemDocument;
    }
}

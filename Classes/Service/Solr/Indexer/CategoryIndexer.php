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

use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;

/**
 * Class CategoryIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class CategoryIndexer extends AbstractShopwareIndexer {

    /**
     * @var string
     */
    protected $clientClassName = CategoryClientInterface::class;

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
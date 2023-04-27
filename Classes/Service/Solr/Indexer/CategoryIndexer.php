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

use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use Portrino\PxShopware\Domain\Model\AbstractShopwareModel;
use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;

/**
 * Class CategoryIndexer
 */
class CategoryIndexer extends AbstractShopwareIndexer
{
    /**
     * @var string
     */
    protected $clientClassName = CategoryClientInterface::class;

    /**
     * overwrite special fields for categories
     *
     * @param Document $itemDocument
     * @param AbstractShopwareModel $itemRecord
     * @param int $language The language to use.
     * @return Document $itemDocument
     */
    protected function overwriteSpecialFields(
        Document $itemDocument,
        AbstractShopwareModel $itemRecord,
        int $language = 0
    ): Document {
        if ($itemRecord instanceof Category) {
            $itemDocument->setField('title', $itemRecord->getName());
            if (\is_object($itemRecord->getRaw())
                && \is_string($itemRecord->getRaw()->metaDescription)
                && $itemRecord->getRaw()->metaDescription !== ''
            ) {
                $itemDocument->setField('description', $itemRecord->getRaw()->metaDescription);
            }

            if ($itemRecord->getImage()) {
                $itemDocument->setField('image_stringS', $itemRecord->getImage()->getUrl());
            }
        }

        return $itemDocument;
    }
}

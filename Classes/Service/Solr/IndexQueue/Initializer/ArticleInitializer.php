<?php

namespace Portrino\PxShopware\Service\Solr\IndexQueue\Initializer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ArticleInitializer
 *
 * @package Portrino\PxShopware\Service\Solr\IndexQueue\Initializer
 */
class ArticleInitializer extends AbstractInitializer
{

    /**
     * @var string
     */
    protected $clientClassName = ArticleClientInterface::class;

    /**
     * @return int The number of affected rows.
     */
    public function initialize()
    {
        $rowsToIndex = [];

        $defaultRecord = $this->getRecordDefaults();
        /** @var Article $article */
        foreach ($this->shopwareClient->findAll(false) as $article) {
            $record = $defaultRecord;
            $record['item_uid'] = $article->getId();
            $record['changed'] = $article->getChanged()->getTimestamp();
            $rowsToIndex[] = $record;
        }

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $databaseConnectionForPages = $connectionPool->getConnectionForTable('tx_solr_indexqueue_item');
        return $databaseConnectionForPages->bulkInsert(
            'tx_solr_indexqueue_item',
            $rowsToIndex,
            array_keys($defaultRecord)
        );
    }

}
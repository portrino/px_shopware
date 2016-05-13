<?php
namespace Portrino\PxShopware\Service\Solr\Queue;

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

/**
 * Class AbstractIndexQueue
 *
 * @package Portrino\PxShopware\Service\Solr\Queue
 */
abstract class AbstractIndexQueue implements \TYPO3\CMS\Core\SingletonInterface, IndexQueueInterface {

    /**
     * @var string
     */
    protected $itemType;

    /**
     * @var int
     */
    protected $rootPageId;

    /**
     * @var \ApacheSolrForTypo3\Solr\IndexQueue\Queue
     * @inject
     */
    protected $solrIndexQueue;


    /**
     * @param int $rootPageId The rootPage for solr indexing configuration
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return bool
     */
    public function populateIndexQueue($rootPageId, $documentsToIndexLimit = 0) {
        $this->rootPageId = $rootPageId;

        $result = TRUE;

        $items = $this->getItemsFromApi($documentsToIndexLimit);
        if ($items) {
            $result = $this->writeToQueue($items);
        } else {
            $result = FALSE;
        }

        return $result;
    }

    /**
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\AbstractShopwareModel>
     */
    abstract protected function getItemsFromApi($documentsToIndexLimit);

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\AbstractShopwareModel> $items
     * @return bool
     */
    protected function writeToQueue($items) {

        $result = TRUE;

        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $item */
        foreach ($items as $item) {

                // check for active flag here and delete inactive items from queue AND index!
            if (isset($item->getRaw()->active) && $item->getRaw()->active === FALSE) {
                $garbageCollector = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\GarbageCollector');
                $garbageCollector->collectGarbage($this->itemType, $item->getId());
                continue;
            }

            if ($this->solrIndexQueue->containsItem($this->itemType, $item->getId())) {
                // existing Item: update!
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
                    'tx_solr_indexqueue_item',
                    'item_type = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->itemType, 'tx_solr_indexqueue_item') .
                    ' AND item_uid = ' . (int)$item->getId(),
                    array('changed' => $item->getChanged()->getTimestamp())
                );
            } else {
                // new Item: insert!
                $item = array(
                    'root' => $this->rootPageId,
                    'item_type' => $this->itemType,
                    'item_uid' => (int)$item->getId(),
                    'changed' => $item->getChanged()->getTimestamp(),
                    'indexing_configuration' => $this->itemType,
                    'errors' => ''
                );
                $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                    'tx_solr_indexqueue_item',
                    $item
                );
            }
        }

        return $result;
    }


    /**
     * Returns the timestamp of the last changed Item by type
     *
     * @param integer $rootPageId
     * @param string $itemType
     *
     * @return integer Timestamp of last add to solrIndexQueue
     */
    protected function getLastChangedTime($rootPageId, $itemType) {
        $lastChangedTime = 0;
        $lastChangedRow = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'changed',
            'tx_solr_indexqueue_item',
            'root = ' . (int)$rootPageId . ' AND item_type = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($itemType, 'tx_solr_indexqueue_item'),
            '',
            'changed DESC',
            1
        );
        if ($lastChangedRow[0]['changed']) {
            $lastChangedTime = $lastChangedRow[0]['changed'];
        }
        return $lastChangedTime;
    }
}
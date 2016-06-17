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

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $db;

    /**
     * @var \ApacheSolrForTypo3\Solr\IndexQueue\Queue
     * @inject
     */
    protected $solrIndexQueue;

    /**
     * AbstractIndexQueue constructor.
     */
    public function __construct() {
        $this->db = $this->getDatabaseConnection();
    }


    /**
     * @param int $rootPageId The rootPage for solr indexing configuration
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return bool
     */
    public function populateIndexQueue($rootPageId, $documentsToIndexLimit = 0) {
        $this->rootPageId = $rootPageId;

        $result = FALSE;

        $items = $this->getItemsFromApi($documentsToIndexLimit);
        if ($items) {
            $result = $this->writeToQueue($items);
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

            if ($this->solrIndexQueue->containsItem($this->itemType, $item->getId())) {
                // existing Item: update!
                $this->db->exec_UPDATEquery(
                    'tx_solr_indexqueue_item',
                    'item_type = ' . $this->db->fullQuoteStr($this->itemType, 'tx_solr_indexqueue_item') .
                    ' AND item_uid = ' . (int)$item->getId(),
                    array('changed' => $item->getChanged()->getTimestamp())
                );
            } else {
                // new Item: insert!
                $insertFields = array(
                    'root' => $this->rootPageId,
                    'item_type' => $this->itemType,
                    'item_uid' => (int)$item->getId(),
                    'changed' => $item->getChanged()->getTimestamp(),
                    'indexing_configuration' => $this->itemType,
                    'errors' => ''
                );
                $this->db->exec_INSERTquery(
                    'tx_solr_indexqueue_item',
                    $insertFields
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
        $lastChangedRow = $this->db->exec_SELECTgetRows(
            'changed',
            'tx_solr_indexqueue_item',
            'root = ' . (int)$rootPageId . ' AND item_type = ' . $this->db->fullQuoteStr($itemType, 'tx_solr_indexqueue_item'),
            '',
            'changed DESC',
            1
        );
        if (isset($lastChangedRow[0]['changed'])) {
            $lastChangedTime = $lastChangedRow[0]['changed'];
        }
        return $lastChangedTime;
    }

    /**
     * Get global database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection;
     */
    protected function getDatabaseConnection() {
        return $GLOBALS['TYPO3_DB'];
    }
}
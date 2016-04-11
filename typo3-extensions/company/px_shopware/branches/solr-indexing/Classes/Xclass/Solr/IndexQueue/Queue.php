<?php
namespace Portrino\PxShopware\Xclass\Solr\IndexQueue;

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

use ApacheSolrForTypo3\Solr\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Queue
 *
 * @package Portrino\PxShopware\Xclass\Solr\IndexQueue
 */
class Queue extends \ApacheSolrForTypo3\Solr\IndexQueue\Queue {

    /**
     * Gets the class name of the initializer class.
     *
     * For most cases the default initializer
     * "\ApacheSolrForTypo3\Solr\IndexQueue\Initializer\Record" will be enough. For special cases
     * like pages we need to do some more work though. In the case of pages we
     * also need to take care of resolving mount pages and their mounted sub
     * trees for example. For these cases it is possible to define a initializer
     * class using the indexing configuration's "initialization" property.
     *
     * @param array $solrConfiguration Solr TypoScript configuration
     * @param string $indexingConfigurationName Indexing configuration name
     * @return string Name of the initializer class
     */
    protected function resolveInitializerClass(
        $solrConfiguration,
        $indexingConfigurationName
    ) {
        $initializerClass = 'ApacheSolrForTypo3\\Solr\\IndexQueue\\Initializer\\Record';

        if (!empty($solrConfiguration['index.']['queue.'][$indexingConfigurationName . '.']['initialization'])) {
            $initializerClass = $solrConfiguration['index.']['queue.'][$indexingConfigurationName . '.']['initialization'];
        }

        return $initializerClass;
    }

    /**
     * Gets $limit number of items to index for a particular $site.
     *
     * @param Site $site TYPO3 site
     * @param integer $limit Number of items to get from the queue
     * @return Item[] Items to index to the given solr server
     */
    public function getItemsToIndex(Site $site, $limit = 50) {
        $itemsToIndex = array();

        // determine which items to index with this run
        $indexQueueItemRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            '*',
            'tx_solr_indexqueue_item',
            'root = ' . $site->getRootPageId() .
            ' AND changed > indexed' .
            ' AND changed <= ' . time() .
            ' AND errors = \'\'',
            '',
            'indexing_priority DESC, changed DESC, uid DESC',
            intval($limit)
        );

        if (!empty($indexQueueItemRecords)) {
            // convert queued records to index queue item objects
            $itemsToIndex = $this->getIndexQueueItemObjectsFromRecords($indexQueueItemRecords, $site);
        }

        return $itemsToIndex;
    }

    /**
     * Creates an array of ApacheSolrForTypo3\Solr\IndexQueue\Item objects from an array of
     * index queue records.
     *
     * @param array $indexQueueItemRecords Array of plain index queue records
     * @param Site $site
     * @return array Array of ApacheSolrForTypo3\Solr\IndexQueue\Item objects
     */
    protected function getIndexQueueItemObjectsFromRecords(array $indexQueueItemRecords, Site $site) {

        $solrConfiguration = $site->getSolrConfiguration();

        $indexQueueItems = array();
        $tableUids = array();
        $tableRecords = array();

            // grouping records by table
        foreach ($indexQueueItemRecords as $indexQueueItemRecord) {
                // should record prefetch from local database be skipped?
            $skipPreFetchFromDb = FALSE;
            if (isset($solrConfiguration['index.']['queue.'][$indexQueueItemRecords[0]['indexing_configuration'] . '.']['skipPreFetchFromDb'])
                && (bool) $solrConfiguration['index.']['queue.'][$indexQueueItemRecords[0]['indexing_configuration'] . '.']['skipPreFetchFromDb']
            ) {
                $skipPreFetchFromDb = TRUE;
            }
            if ($skipPreFetchFromDb) {
                $tableRecords[$indexQueueItemRecord['item_type']][$indexQueueItemRecord['item_uid']] = array('uid' => $indexQueueItemRecord['item_uid']);
            } else {
                $tableUids[$indexQueueItemRecord['item_type']][] = $indexQueueItemRecord['item_uid'];
            }
        }


            // fetching records by table, saves us a lot of single queries
        foreach ($tableUids as $table => $uids) {
            $uidList = implode(',', $uids);
            $records = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                $table,
                'uid IN(' . $uidList . ')',
                '', '', '', // group, order, limit
                'uid'
            );
            $tableRecords[$table] = $records;
        }

            // creating index queue item objects and assigning / mapping
            // records to index queue items
        foreach ($indexQueueItemRecords as $indexQueueItemRecord) {
            if (isset($tableRecords[$indexQueueItemRecord['item_type']][$indexQueueItemRecord['item_uid']])) {
                $indexQueueItems[] = GeneralUtility::makeInstance(
                    'ApacheSolrForTypo3\\Solr\\IndexQueue\\Item',
                    $indexQueueItemRecord,
                    $tableRecords[$indexQueueItemRecord['item_type']][$indexQueueItemRecord['item_uid']]
                );
            } else {
                GeneralUtility::devLog('Record missing for Index Queue item. Item removed.',
                    'solr', 3, array($indexQueueItemRecord));
                $this->deleteItem($indexQueueItemRecord['item_type'],
                    $indexQueueItemRecord['item_uid']);
            }
        }

        return $indexQueueItems;
    }

}

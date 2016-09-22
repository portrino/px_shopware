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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Queue
 * TODO: Remove this class when https://github.com/TYPO3-Solr/ext-solr/pull/609 is merged.
 */
class Queue extends \ApacheSolrForTypo3\Solr\IndexQueue\Queue {

    /**
     * @inheritdoc
     */
    protected function getIndexQueueItemObjectsFromRecords(
        array $indexQueueItemRecords
    ) {
        $indexQueueItems = array();
        $tableUids = array();
        $tableRecords = array();

        // grouping records by table
        foreach ($indexQueueItemRecords as $indexQueueItemRecord) {
            $tableUids[$indexQueueItemRecord['item_type']][] = $indexQueueItemRecord['item_uid'];
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

            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessFetchRecordsForIndexQueueItem'])) {
                $params = ['table' => $table, 'uids' => $uids, 'tableRecords' => &$tableRecords];
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessFetchRecordsForIndexQueueItem'] as $reference) {
                    GeneralUtility::callUserFunction($reference, $params, $this);
                }
                unset($params);
            }
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

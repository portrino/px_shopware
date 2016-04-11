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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ApacheSolrForTypo3\Solr\Util;

/**
 * Class AbstractShopwareIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class AbstractShopwareIndexer extends \ApacheSolrForTypo3\Solr\IndexQueue\Indexer {


    /**
     * Creates a single Solr Document for an item in a specific language.
     *
     * @param Item $item An index queue item to index.
     * @param integer $language The language to use.
     * @return boolean TRUE if item was indexed successfully, FALSE on failure
     */
    protected function indexItem(Item $item, $language = 0) {

        if ($this->options['ignoredIds']) {
            $ignoredIds = GeneralUtility::trimExplode(',', $this->options['ignoredIds'], TRUE);
            if (in_array($item->getRecordUid(), $ignoredIds)) {
                return TRUE;
            }
        }

        $documents = array();

        // TODO: how to add different languages
        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $itemRecord */
        $itemRecord = $this->getShopwareRecord($item);

            // get general fields
        /** @var \Apache_Solr_Document $itemDocument */
        $itemDocument = $this->getBaseDocument($item, $itemRecord);

        $itemIndexingConfiguration = $this->getItemTypeConfiguration($item, $language);

            // get raw item data as array, needed for Solr core functions
        $itemDataRaw = json_decode(json_encode($itemRecord->getRaw()), true);

            // process TS config for additional fields
        $itemDocument = $this->addDocumentFieldsFromTyposcript($itemDocument, $itemIndexingConfiguration, $itemDataRaw);

            // overwrite fields for specific item type
        $itemDocument = $this->overwriteSpecialFields($itemDocument, $itemRecord);

        $documents[] = $itemDocument;

            // allow indexItemAddDocuments Hooks
        $documents = array_merge($documents, $this->getAdditionalDocuments(
            $item,
            $language,
            $itemDocument
        ));

            // apply fieldProcessingInstructions from TS
        $documents = $this->processDocuments($item, $documents);

            // allow preAddModifyDocuments Hooks
        $documents = $this->preAddModifyDocuments(
            $item,
            $language,
            $documents
        );

            // add to solr index core
        $response = $this->solr->addDocuments($documents);
        if ($response->getHttpStatus() == 200) {
            $itemIndexed = TRUE;
        }

        $this->log($item, $documents, $response);

        return $itemIndexed;

    }


    /**
     * get Data from shopware API
     *
     * @param Item $item The item to index
     * @return \Portrino\PxShopware\Domain\Model\AbstractShopwareModel The record to use to build the base document
     */
    protected function getShopwareRecord(Item $item) {
        // overwrite in sub classes
    }


    /**
     * overwrite special fields for item type
     *
     * @param \Apache_Solr_Document $itemDocument
     * @param \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $itemRecord
     * @return \Apache_Solr_Document $itemDocument
     */
    protected function overwriteSpecialFields(\Apache_Solr_Document $itemDocument, \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $itemRecord) {
        // overwrite in sub classes
    }


    /**
     * Creates a Solr document with the basic / core fields set already.
     *
     * @param Item $item The item to index
     * @param \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $itemRecord The record to use to build the base document
     * @return \Apache_Solr_Document A basic Solr document
     */
    protected function getBaseDocument(Item $item, \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $itemRecord) {
        $site = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\Site',
            $item->getRootPageUid());

        /** @var $document \Apache_Solr_Document */
        $document = GeneralUtility::makeInstance('Apache_Solr_Document');

        // required fields
        $document->setField('id', Util::getDocumentId(
            $item->getType(),
            0, // TODO: pid for shopware models ??
            $itemRecord->getId()
        ));
        $document->setField('type', $item->getType());
        $document->setField('appKey', 'EXT:solr');

        // site, siteHash
        $document->setField('site', $site->getDomain());
        $document->setField('siteHash', $site->getSiteHash());

        // uid, pid
        $document->setField('uid', $itemRecord->getId());
        // TODO: pid for shopware models ??
//        $document->setField('pid', $itemRecord['pid']);

        // created and changed, get TimeStamps from ISO strings
        if (is_object($itemRecord->getRaw()) && is_string($itemRecord->getRaw()->added) && $itemRecord->getRaw()->added != '') {
            $added = new \DateTime($itemRecord->getRaw()->added);
            $document->setField('created', $added->getTimestamp());
        }
        if (is_object($itemRecord->getRaw()) && is_string($itemRecord->getRaw()->changed) && $itemRecord->getRaw()->changed != '') {
            $changed = new \DateTime($itemRecord->getRaw()->changed);
            $document->setField('changed', $changed->getTimestamp());
        }

        return $document;
    }

}
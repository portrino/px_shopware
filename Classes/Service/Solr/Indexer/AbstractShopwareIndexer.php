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

use ApacheSolrForTypo3\Solr\Domain\Site\Site;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\GarbageCollector;
use ApacheSolrForTypo3\Solr\IndexQueue\Indexer;
use ApacheSolrForTypo3\Solr\IndexQueue\Item;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
use ApacheSolrForTypo3\Solr\System\Solr\ResponseAdapter;
use ApacheSolrForTypo3\Solr\Util;
use Portrino\PxShopware\Domain\Model\AbstractShopwareModel;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService;
use Solarium\Exception\HttpException;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class AbstractShopwareIndexer
 *
 * @package Portrino\PxShopware\Service\Solr\Indexer
 */
class AbstractShopwareIndexer extends Indexer
{

    /**
     * @var string
     */
    protected $clientClassName = AbstractShopwareApiClientInterface::class;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LanguageToShopwareMappingService
     */
    protected $languageToShopMappingService;

    /**
     * Constructor
     *
     * @param array $options of indexer options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->languageToShopMappingService = $this->objectManager->get(LanguageToShopwareMappingService::class);
    }

    /**
     * Creates a single Solr Document for an item in a specific language.
     *
     * @param Item $item An index queue item to index.
     * @param integer $language The language to use.
     * @return boolean TRUE if item was indexed successfully, FALSE on failure
     * @throws HttpTransportException
     */
    protected function indexItem(Item $item, $language = 0)
    {
        $itemIndexed = false;
        $documents = [];

        /** @var AbstractShopwareModel $itemRecord */
        $itemRecord = $this->getShopwareRecord($item, $language);

        // In this case we have no item for the current language and skip indexing
        if ($itemRecord === null) {
            return true;
        }

        // get raw item data as array, needed for Solr core functions
        $itemDataRaw = json_decode(json_encode($itemRecord->getRaw()), true);

        // get general fields
        /** @var Document $itemDocument */
        $itemDocument = $this->getBaseDocument($item, $itemDataRaw);

        $itemIndexingConfiguration = $this->getItemTypeConfiguration($item, $language);

        // process TS config for additional fields
        $itemDocument = $this->addDocumentFieldsFromTyposcript($itemDocument, $itemIndexingConfiguration, $itemDataRaw);

        // overwrite fields for specific item type
        $itemDocument = $this->overwriteSpecialFields($itemDocument, $itemRecord, $language);

        // check if item should be indexed
        if ($this->itemIsValid($itemRecord)) {
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
        } else {
            // item is not valid, delete from index!
            $garbageCollector = GeneralUtility::makeInstance(GarbageCollector::class);
            $garbageCollector->collectGarbage($item->getType(), $itemRecord->getId());
        }

        try {
            $response = $this->solr->getWriteService()->addDocuments($documents);
            if ($response->getHttpStatus() == 200) {
                $itemIndexed = true;
            }
        } catch (HttpException $e) {
            $response = new ResponseAdapter($e->getBody(), $httpStatus = 500, $e->getStatusMessage());
        }

        $this->log($item, $documents, $response);

        return $itemIndexed;
    }


    /**
     * check if record should be added/updated or deleted from index
     *
     * @param AbstractShopwareModel $itemRecord The item to index
     * @return bool valid or not
     */
    protected function itemIsValid(AbstractShopwareModel $itemRecord)
    {
        $result = true;

        // check for active flag here
        if (isset($itemRecord->getRaw()->active) && $itemRecord->getRaw()->active === false) {
            $result = false;
        }
        // check if item should be ignored
        if ($this->options['ignoredIds'] !== '') {
            $ignoredIds = GeneralUtility::intExplode(',', $this->options['ignoredIds'], true);
            if (\in_array($itemRecord->getId(), $ignoredIds, true)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Get data from shopware API
     *
     * @param Item $item The item to index
     * @param integer $language The language to use.
     * @return AbstractShopwareModel The record to use to build the base document
     */
    protected function getShopwareRecord(Item $item, $language = 0)
    {
        $shopwareClient = $this->objectManager->get($this->clientClassName);
        $shopId = $this->languageToShopMappingService->getShopIdBySysLanguageUid($language);
        return $shopwareClient->findById($item->getRecordUid(), true, ['language' => $shopId]);
    }

    /**
     * overwrite special fields for item type
     *
     * @param Document $itemDocument
     * @param AbstractShopwareModel $itemRecord
     * @param integer $language The language to use.
     * @return Document $itemDocument
     */
    protected function overwriteSpecialFields(
        Document $itemDocument,
        AbstractShopwareModel $itemRecord,
        $language = 0
    ) {
        // overwrite in sub classes
        return $itemDocument;
    }

    /**
     * Creates a Solr document with the basic / core fields set already.
     *
     * @param Item $item The item to index
     * @param array $itemRecord The record to use to build the base document
     * @return Document A basic Solr document
     */
    protected function getBaseDocument(Item $item, array $itemRecord)
    {
        $site = $this->getSiteByPageId($item->getRootPageUid());

        /** @var Document $document */
        $document = GeneralUtility::makeInstance(Document::class);

        // required fields
        $document->setField('id', Util::getDocumentId(
            $item->getType(),
            $item->getRootPageUid(),
            $itemRecord['id']
        ));
        $document->setField('type', $item->getType());
        $document->setField('appKey', 'EXT:solr');

        // site, siteHash
        $document->setField('site', $site->getDomain());
        $document->setField('siteHash', $site->getSiteHash());

        // uid, pid
        $document->setField('uid', $itemRecord['id']);
        // TODO: pid for shopware models ??
//        $document->setField('pid', $itemRecord['pid']);

        // created and changed, get TimeStamps from ISO strings
        if (array_key_exists('added', $itemRecord) && $itemRecord['added'] !== '') {
            $added = new \DateTime($itemRecord['added']);
            $document->setField('created', $added->getTimestamp());
        }
        if (array_key_exists('changed', $itemRecord) && $itemRecord['changed'] !== '') {
            $changed = new \DateTime($itemRecord['changed']);
            $document->setField('changed', $changed->getTimestamp());
        }

        return $document;
    }

    /**
     * @param integer $pageId
     * @return Site
     */
    protected function getSiteByPageId($pageId)
    {
        return GeneralUtility::makeInstance(SiteRepository::class)->getSiteByPageId($pageId);
    }
}

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
 * Class ArticleIndexQueue
 *
 * @package Portrino\PxShopware\Service\Solr\Queue
 */
class ArticleIndexQueue extends AbstractIndexQueue implements ArticleIndexQueueInterface {

    /**
     * @var string
     */
    protected $itemType = \Portrino\PxShopware\Domain\Model\Article::class;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ArticleClient
     * @inject
     */
    protected $shopwareClient;


    /**
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Article>
     */
    protected function getItemsFromApi($documentsToIndexLimit) {

            // call API for all items with changed > lastChanged in Solr indexQueue
            // sorted by changed: oldest first
        $lastChanged = new \DateTime();
        $lastChanged->setTimestamp($this->getLastChangedTime($this->rootPageId, $this->itemType));
        $params = array(
            'limit' => $documentsToIndexLimit,
            'sort' => array(
                array(
                    'property' => 'changed',
                    'direction' => 'ASC'
                )
            ),
            'filter' => array(
                array(
                    'property' => 'changed',
                    'expression' => '>=',
                    'value' => $lastChanged->format(\DateTime::ISO8601)
                )
            )
        );

            // call API uncached
        return $this->shopwareClient->findByParams($params, FALSE);
    }

}
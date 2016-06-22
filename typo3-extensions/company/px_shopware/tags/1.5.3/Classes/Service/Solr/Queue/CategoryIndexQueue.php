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
 * Class CategoryIndexQueue
 *
 * @package Portrino\PxShopware\Service\Solr\Queue
 */
class CategoryIndexQueue extends AbstractIndexQueue implements CategoryIndexQueueInterface {

    /**
     * @var string
     */
    protected $itemType = 'Portrino_PxShopware_Domain_Model_Category';

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CategoryClient
     * @inject
     */
    protected $shopwareClient;

    
    /**
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    protected function getItemsFromApi($documentsToIndexLimit) {
        $categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

            // get list of categories has not all data we need
        $categoryStubs = $this->shopwareClient->findAll(FALSE);

            // iterate over all categories, to get full data with single findById request
        foreach ($categoryStubs as $categoryStub) {
            /** @var \Portrino\PxShopware\Domain\Model\Category $category */
            $category = $this->shopwareClient->findById($categoryStub->getId(), FALSE);
            if ($category) {
                $categories->attach($category);
            }
        }

        return $categories;
    }
}
<?php
namespace Portrino\PxShopware\Service\Shopware;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CategoryClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class CategoryClient extends AbstractShopwareApiClient implements CategoryClientInterface {

    /**
     * @var string
     */
    protected $endpoint = 'categories';

    /**
     * @var
     */
    protected $entityClassName = \Portrino\PxShopware\Domain\Model\Category::class;

    /**
     * @return string
     */
    public function getEndpoint() {
        return $this->endpoint;
    }

    /**
     * @return mixed
     */
    public function getEntityClassName() {
        return $this->entityClassName;
    }

    /**
     *
     * Do not use this during initialization, because it could lead to max nesting level exception
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    public function findByParent($parentId) {
        $result = new ObjectStorage();

        $filterByParentId = array(
            array(
                'property' => 'parentId',
                'expression' => '=',
                'value'    => $parentId
            ),
        );

        $response = $this->get($this->getValidEndpoint(), array('filter' => $filterByParentId));

        if ($response) {
            $token = (isset($response->pxShopwareTypo3Token)) ? (bool)$response->pxShopwareTypo3Token : FALSE;
            $isTrialVersion = ($this->getStatus() === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL);

            if (isset($response->data) && is_array($response->data)) {
                foreach ($response->data as $data) {
                    if (isset($data->id)) {
                        $category = $this->findById($data->id);
                        if ($category != NULL) {
                            $result->attach($category);
                            if ($isTrialVersion === TRUE) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

}

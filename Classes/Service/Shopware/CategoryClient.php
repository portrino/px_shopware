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
class CategoryClient extends AbstractShopwareApiClient implements CategoryClientInterface
{

    /**
     * Do not use this during initialization, because it could lead to max nesting level exception
     *
     * @param int $parentId
     * @return ObjectStorage <\Portrino\PxShopware\Domain\Model\Category>
     * @throws Exceptions\ShopwareApiClientException
     */
    public function findByParent($parentId)
    {
        $result = new ObjectStorage();

        $filterByParentId = [
            [
                'property' => 'parentId',
                'expression' => '=',
                'value' => $parentId
            ],
        ];

        $response = $this->get($this->getValidEndpoint(), ['filter' => $filterByParentId]);


        if ($response) {
            $token = (isset($response->pxShopwareTypo3Token)) ? (bool)$response->pxShopwareTypo3Token : false;
            $isTrialVersion = ($this->getStatus() === AbstractShopwareApiClientInterface::STATUS_CONNECTED_TRIAL);

            if (isset($response->data) && is_array($response->data)) {
                foreach ($response->data as $data) {
                    if (isset($data->id)) {
                        $category = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($category != null) {
                            $result->attach($category);
                            if ($isTrialVersion === true) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return CategoryClientInterface::ENDPOINT;
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return CategoryClientInterface::ENTITY_CLASS_NAME;
    }
}

<?php
namespace Portrino\PxShopware\Service\Shopware;

/***************************************************************
 *  Copyright notice
 *
 *  (c) (c) 2017 Axel Boeswetter <boeswetter@portrino.de>, portrino GmbH
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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CustomerClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class CustomerClient extends AbstractShopwareApiClient implements OrderClientInterface
{

    /**
     * @param string $term
     * @param int $limit
     * @param bool $doCacheRequest
     * @param array $params
     * @return ObjectStorage
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = true, $params = [])
    {
        $shopwareModels = new ObjectStorage();

        /**
         * only search for id if term is integer
         */
        if (is_numeric($term)) {
            $filter = [
                [
                    'property' => 'id',
                    'expression' => '=',
                    'value' => $term
                ],
                [
                    'operator' => 'OR',
                    'property' => 'number',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ],
            ];
        } else {
            $filter = [
                [
                    'property' => 'email',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ],
                [
                    'operator' => 'OR',
                    'property' => 'firstname',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ],
                [
                    'operator' => 'OR',
                    'property' => 'lastname',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ]
            ];
        }

        ArrayUtility::mergeRecursiveWithOverrule($params, [
            'limit' => $limit,
            'sort' => [
                [
                    'property' => 'number',
                    'direction' => 'DESC'
                ]
            ],
            'filter' => $filter
        ]);

        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);
        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : false;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != null) {
                            $shopwareModels->attach($shopwareModel);
                        }
                    }
                }
            }
        }
        return $shopwareModels;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT;
    }

    /**
     * @return string
     */
    public function getEntityClassName()
    {
        return self::ENTITY_CLASS_NAME;
    }
}

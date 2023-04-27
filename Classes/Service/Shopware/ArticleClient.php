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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ArticleClient
 */
class ArticleClient extends AbstractShopwareApiClient implements ArticleClientInterface
{
    /**
     * @param string $term
     * @param int $limit
     * @param bool $doCacheRequest
     * @param array $params
     * @return ObjectStorage
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = true, $params = []): ObjectStorage
    {
        /**
         * only search for id if term is integer
         */
        if (is_numeric($term)) {
            $filter = [
                [
                    'property' => 'id',
                    'expression' => '=',
                    'value' => $term,
                ],
            ];
        } else {
            $filter = [
                [
                    'property' => 'name',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%',
                ],
                [
                    'operator' => 'OR',
                    'property' => 'mainDetail.number',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%',
                ],
            ];
        }

        ArrayUtility::mergeRecursiveWithOverrule($params, [
            'limit' => $limit,
            'sort' => [
                [
                    'property' => 'name',
                    'direction' => 'ASC',
                ],
            ],
            'filter' => $filter,
        ]);

        return $this->findByParams($params, $doCacheRequest);
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

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
 * Class ArticleClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class ArticleClient extends AbstractShopwareApiClient implements ArticleClientInterface {

    /**
     * @var string
     */
    protected $endpoint = 'articles';

    /**
     * @var
     */
    protected $entityClassName = \Portrino\PxShopware\Domain\Model\Article::class;

    /**
     * @param string $term
     * @param int $limit
     * @param bool $doCacheRequest
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = TRUE) {
        $shopwareModels = new ObjectStorage();

        $params = array(
            'limit' => $limit,
            'sort' => array(
                array(
                    'property' => 'name',
                    'direction' => 'ASC'
                )
            ),
            'filter' => array(
                array(
                    'property' => 'name',
                    'expression' => 'LIKE',
                    'value' => '%' . $term . '%'
                ),
//                array(
//                    'property' => 'descriptionLong',
//                    'expression' => 'LIKE',
//                    'value' => '%' . $term . '%'
//                )
            )
        );
        
        $result = $this->get($this->getValidEndpoint(), $params, $doCacheRequest);

        if ($result) {
            $token = (isset($result->pxShopwareTypo3Token)) ? (bool)$result->pxShopwareTypo3Token : FALSE;
            if (isset($result->data) && is_array($result->data)) {
                foreach ($result->data as $data) {
                    if (isset($data->id)) {
                        /** @var \Portrino\PxShopware\Domain\Model\AbstractShopwareModel $shopwareModel */
                        $shopwareModel = $this->objectManager->get($this->getEntityClassName(), $data, $token);
                        if ($shopwareModel != NULL) {
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
    public function getEndpoint() {
        return $this->endpoint;
    }

    /**
     * @return mixed
     */
    public function getEntityClassName() {
        return $this->entityClassName;
    }

}

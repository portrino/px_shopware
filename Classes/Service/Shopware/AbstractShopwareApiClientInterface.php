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
use Portrino\PxShopware\Service\Shopware\Exceptions\ShopwareApiClientException;

/**
 * Class AbstractShopwareApiClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
interface AbstractShopwareApiClientInterface {

    const STATUS_CONNECTED_FULL = 'status_connected_full';
    const STATUS_CONNECTED_TRIAL = 'status_connected_trial';
    const STATUS_DISCONNECTED = 'status_disconnected';

    /**
     * @return bool
     * @throws ShopwareApiClientException
     */
    public function isConnected();

    /**
     * Returns one of the given states
     * - status_connected_full (TYPO3-Connector is installed on shopware system)
     * - status_connected_trial (TYPO3-Connector is NOT installed on shopware system - trial version)
     * - status_disconnected (No connection to shopware system possible)
     *
     * @return string
     * @throws ShopwareApiClientException
     */
    public function getStatus();

    /**
     * @return \Portrino\PxShopware\Domain\Model\ShopwareModelInterface
     */
    public function find();

    /**
     * @param $id
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \Portrino\PxShopware\Domain\Model\ShopwareModelInterface
     */
    public function findById($id, $doCacheRequest = TRUE, $params = []);

    /**
     * @param $term
     * @param int $limit
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByTerm($term, $limit = -1, $doCacheRequest = TRUE, $params = []);

    /**
     * @param bool $doCacheRequest
     * @param array $params
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findAll($doCacheRequest = TRUE, $params = []);

    /**
     * @param array $params
     * @param bool $doCacheRequest
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\ShopwareModelInterface>
     */
    public function findByParams($params = [], $doCacheRequest = TRUE);

}
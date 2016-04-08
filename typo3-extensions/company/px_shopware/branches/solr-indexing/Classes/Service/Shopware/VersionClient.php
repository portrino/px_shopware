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

/**
 * Class VersionClient
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class VersionClient extends AbstractShopwareApiClient implements VersionClientInterface {

    /**
     * @var string
     */
    protected $endpoint = 'version';

    /**
     * @var
     */
    protected $entityClassName = \Portrino\PxShopware\Domain\Model\Version::class;

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

<?php
namespace Portrino\PxShopware\Domain\Model;

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
 * Class AbstractShopwareModel
 *
 * @package Portrino\PxShopware\Domain\Model
 */
abstract class AbstractShopwareModel {

    /**
     * id
     *
     * @var string
     */
    protected $id = '';

    /**
     * @var object
     */
    protected $raw;

    /**
     * @var boolean
     */
    protected $token;

    /**
     * AbstractShopwareModel constructor.
     *
     * @param $raw
     * @param $token
     */
    public function __construct($raw, $token) {
        $this->setRaw($raw);
        if (isset($this->raw->id)) {
            $this->setId($this->raw->id);
        }
        $this->setToken($token);
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return object|NULL
     */
    public function getRaw() {
        return $this->raw;
    }

    /**
     * @param string $raw
     */
    public function setRaw($raw) {
        $this->raw = $raw;
    }

    /**
     * @return boolean
     */
    public function isToken() {
        return $this->token;
    }

    /**
     * @param boolean $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

}
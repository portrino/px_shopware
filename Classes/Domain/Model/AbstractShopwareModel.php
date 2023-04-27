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
 */
abstract class AbstractShopwareModel implements ShopwareModelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var object
     */
    protected $raw;

    /**
     * @var bool
     */
    protected $token;

    /**
     * @param object $raw
     * @param bool $token
     */
    public function initialize($raw, $token)
    {
        $this->setRaw($raw);
        if (isset($this->raw->id)) {
            $this->setId((int)$this->raw->id);
        }
        $this->setToken($token);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return object
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param object $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return bool
     */
    public function isToken()
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        if (property_exists($this->raw, $name)) {
            return $this->raw->$name;
        }

        throw new \BadMethodCallException('Requested property "' . $name . '" not found in "' . static::class . '"');
    }
}

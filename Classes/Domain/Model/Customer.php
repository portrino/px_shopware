<?php
namespace Portrino\PxShopware\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Axel Boeswetter <boeswetter@portrino.de>, portrino GmbH
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
 * Class Customer
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Customer extends AbstractShopwareModel {

    /**
     * @var string
     */
    protected $number = '';

    /**
     * @var string
     */
    protected $groupKey = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $firstName = '';

    /**
     * @var string
     */
    protected $lastName = '';

    /**
     * @param object $raw
     * @param string $token
     */
    public function __construct($raw, $token) {
        parent::__construct($raw, $token);

        if (isset($this->raw->number)) {
            $this->setNumber($this->raw->number);
        }
        if (isset($this->raw->groupKey)) {
            $this->setGroupKey($this->raw->groupKey);
        }
        if (isset($this->raw->email)) {
            $this->setEmail($this->raw->email);
        }
        if (isset($this->raw->firstname)) {
            $this->setFirstName($this->raw->firstname);
        }
        if (isset($this->raw->lastname)) {
            $this->setLastName($this->raw->lastname);
        }
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getGroupKey(): string
    {
        return $this->groupKey;
    }

    /**
     * @param string $groupKey
     */
    public function setGroupKey(string $groupKey)
    {
        $this->groupKey = $groupKey;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}

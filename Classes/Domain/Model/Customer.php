<?php
namespace Portrino\PxShopware\Domain\Model;

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
use Portrino\PxShopware\Backend\Form\Wizard\SuggestEntryInterface;
use Portrino\PxShopware\Backend\Hooks\ItemEntryInterface;

/**
 * Class Customer
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Customer extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $number = '';

    /**
     * @var \DateTime
     */
    protected $firstLogin;

    /**
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * Article constructor.
     *
     * @param $raw
     * @param $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->number)) {
            $this->setNumber($this->raw->number);
        }

        if (isset($this->raw->firstLogin)) {
            $this->setFirstLogin($this->raw->firstLogin);
        }

        if (isset($this->raw->lastLogin)) {
            $this->setLastLogin($this->raw->lastLogin);
        }

        if (isset($this->raw->email)) {
            $this->setEmail($this->raw->email);
        }

        if (isset($this->raw->firstname)) {
            $this->setFirstname($this->raw->firstname);
        }

        if (isset($this->raw->lastname)) {
            $this->setLastname($this->raw->lastname);
        }
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return \DateTime
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param \DateTime|string $firstLogin
     */
    public function setFirstLogin($firstLogin)
    {
        if (is_string($firstLogin)) {
            $firstLogin = new \DateTime($firstLogin);
        }
        $this->firstLogin = $firstLogin;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime|string $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        if (is_string($lastLogin)) {
            $lastLogin = new \DateTime($lastLogin);
        }
        $this->lastLogin = $lastLogin;
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
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @return int
     */
    public function getSuggestId()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSuggestLabel()
    {
        return $this->getNumber() . ' [' . $this->getId() . ']';
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {
        return $this->getName() . ' (' . $this->getEmail() . ')';
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return 'px-shopware-customer';
    }

    /**
     * @return int
     */
    public function getSelectItemId()
    {
        return (int)$this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel()
    {
        $result = $this->getNumber() . ' [' . $this->getId() . ']';
        return $result;
    }
}

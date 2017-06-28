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
 * Class Order
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Order extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $number = '';

    /**
     * @var \DateTime
     */
    protected $orderTime;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var int
     */
    protected $customerId;

    /**
     * @var int
     */
    protected $dispatchId;

    /**
     * @var int
     */
    protected $orderStatusId;

    /**
     * @var int
     */
    protected $paymentId;

    /**
     * @var int
     */
    protected $paymentStatusId;

    /**
     * @var int
     */
    protected $shopId;

    /**
     * @var int
     */
    protected $transactionId;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CustomerClientInterface
     * @inject
     */
    protected $customerClient;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Article constructor.
     *
     * @param $raw
     * @param $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        $rawProperties = get_object_vars($this->raw);
        foreach ($rawProperties as $name => $value) {
            if (is_object($value) === false || is_array($value) === false) {
                $setter = 'set' . ucfirst($name);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
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
    public function getOrderTime()
    {
        return $this->orderTime;
    }

    /**
     * @param \DateTime|string $orderTime
     */
    public function setOrderTime($orderTime)
    {
        if (is_string($orderTime)) {
            $orderTime = new \DateTime($orderTime);
        }
        $this->orderTime = $orderTime;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        if (!$this->customer) {
            /**
             * try to get the customer object from raw
             */
            if (!isset($this->getRaw()->customer)) {
                /** @var Customer $customer */
                $customer = $this->customerClient->findById($this->getRaw()->customerId, false);
                $this->setCustomer($customer);
            } else {
                if (isset($this->getRaw()->customer)) {
                    /** @var Customer $customer */
                    $customer = $this->objectManager->get(Customer::class, $this->getRaw()->customer, $this->token);
                    $this->setCustomer($customer);
                }
            }
        }

        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getDispatchId()
    {
        return $this->dispatchId;
    }

    /**
     * @param int $dispatchId
     */
    public function setDispatchId($dispatchId)
    {
        $this->dispatchId = $dispatchId;
    }

    /**
     * @return int
     */
    public function getOrderStatusId()
    {
        return $this->orderStatusId;
    }

    /**
     * @param int $orderStatusId
     */
    public function setOrderStatusId($orderStatusId)
    {
        $this->orderStatusId = $orderStatusId;
    }

    /**
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param int $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return int
     */
    public function getPaymentStatusId()
    {
        return $this->paymentStatusId;
    }

    /**
     * @param int $paymentStatusId
     */
    public function setPaymentStatusId($paymentStatusId)
    {
        $this->paymentStatusId = $paymentStatusId;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param int $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
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
        $result = $this->getNumber() . ' [' . $this->getId() . ']';
        return $result;
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {

        return $this->getOrderTime()->format('d.m.Y H:i') . ' - ' .
            $this->getCustomer()->getName() . '(' . $this->getCustomer()->getEmail() . ')';
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return 'px-shopware-order';
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
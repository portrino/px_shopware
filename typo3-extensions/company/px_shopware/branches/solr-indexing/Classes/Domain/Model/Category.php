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
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Category
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Category extends AbstractShopwareModel {

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \DateTime $changed
     */
    protected $changed;

    /**
     * @var \TYPO3\CMS\Core\Http\Uri
     */
    protected $uri = '';

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CategoryClientInterface
     * @inject
     */
    protected $categoryClient;

    /**
     * Category constructor.
     *
     * @param $raw
     * @param $token
     */
    public function __construct($raw, $token) {
        parent::__construct($raw, $token);

        if (isset($this->raw->name)) {
            $this->setName($this->raw->name);
        }
        if (isset($this->raw->pxShopwareUrl)) {
            $this->setUri($this->raw->pxShopwareUrl);
        }
        if (isset($this->raw->changed)) {
            $this->setChanged($this->raw->changed);
        }
        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects() {
    }

    /**
     *
     */
    public function initializeObject() {
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    public function getSubCategories () {
        return $this->categoryClient->findByParent($this->id);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return \TYPO3\CMS\Core\Http\Uri
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * @param \TYPO3\CMS\Core\Http\Uri|string $uri
     */
    public function setUri($uri) {
        if (is_string($uri)) {
            $uri = new \TYPO3\CMS\Core\Http\Uri($uri);
        }
        $this->uri = $uri;
    }

    /**
     * @return \DateTime
     */
    public function getChanged() {
        return $this->changed;
    }

    /**
     * @param \DateTime|string $changed
     */
    public function setChanged($changed) {
        if (is_string($changed)) {
            $changed = new \DateTime($changed);
        }
        $this->changed = $changed;
    }
}
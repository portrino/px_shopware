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
 * Class Article
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Article extends AbstractShopwareModel {

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \TYPO3\CMS\Core\Http\Uri
     */
    protected $uri = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Portrino\PxShopware\Domain\Model\Media>
     */
    protected $images = array();

    /**
     * @var \Portrino\PxShopware\Domain\Model\Detail
     */
    protected $detail = array();

    /**
     * @var \Portrino\PxShopware\Service\Shopware\MediaClientInterface
     * @inject
     */
    protected $mediaClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\DetailClientInterface
     * @inject
     */
    protected $detailClient;

    /**
     * Article constructor.
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
            $this->setUrl($this->raw->pxShopwareUrl);
        }

        /**
         * set description in dependence of the description or descriptionLong attribute
         */
        if (isset($this->raw->description) && $this->raw->description != '') {
            $this->setDescription($this->raw->description);
        } else if (isset($this->raw->descriptionLong) && $this->raw->descriptionLong != '') {
            $this->setDescription($this->raw->descriptionLong);
        }

        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects() {
        $this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     *
     */
    public function initializeObject() {
        if (isset($this->getRaw()->images) && is_array($this->getRaw()->images)) {
            foreach ($this->raw->images as $image) {
                if (isset($image->mediaId)) {
                    $media = $this->mediaClient->findById($image->mediaId);
                    $this->addImage($media);
                }
            }
        }

        if (isset($this->getRaw()->mainDetailId)) {
            if (!isset($this->getRaw()->mainDetail)) {
                $detail = $this->detailClient->findById($this->getId());
                $this->setDetail($detail);
            }
        }
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
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Adds a image
     *
     * @param \Portrino\PxShopware\Domain\Model\Media $image
     *
     * @return void
     */
    public function addImage(\Portrino\PxShopware\Domain\Model\Media $image) {
        $this->images->attach($image);
    }

    /**
     * Removes a image
     *
     * @param \Portrino\PxShopware\Domain\Model\Media $imageToRemove The image to be removed
     *
     * @return void
     */
    public function removeMedia(\Portrino\PxShopware\Domain\Model\Media $imageToRemove) {
        $this->images->detach($imageToRemove);
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Media> $images
     */
    public function getImages() {
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Media> $images
     *
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images) {
        $this->images = $images;
    }

    /**
     * Return the first image
     *
     * @return NULL|\Portrino\PxShopware\Domain\Model\Media
     */
    public function getFirstImage() {
        return ($this->getImages() != NULL) ? array_values($this->getImages()->toArray())[0] : NULL;
    }

    /**
     * @return Detail
     */
    public function getDetail() {
        return $this->detail;
    }

    /**
     * @param Detail $detail
     */
    public function setDetail($detail) {
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getOrdnerNumber() {
        return ($this->getDetail() != NULL) ? $this->getDetail()->getNumber() : '';
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

}
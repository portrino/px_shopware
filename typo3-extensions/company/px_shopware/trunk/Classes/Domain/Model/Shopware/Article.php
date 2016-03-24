<?php
namespace Portrino\PxShopware\Domain\Model\Shopware;

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
 * @package Portrino\PxShopware\Domain\Model\Shopware
 */
class Article extends AbstractShopwareModel {

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Portrino\PxShopware\Domain\Model\Shopware\Media>
     */
    protected $images = array();

    /**
     * @var \Portrino\PxShopware\Service\Shopware\MediaClient
     * @inject
     */
    protected $mediaClient;

    public function __construct($raw) {
        parent::__construct($raw);

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
                if (isset($image->id)) {
                    $media = $this->mediaClient->findById($image->id);
                    $this->addImage($media);
                }
            }
        }
    }

    /**
     * Adds a image
     *
     * @param \Portrino\PxShopware\Domain\Model\Shopware\Media $image
     *
     * @return void
     */
    public function addImage(\Portrino\PxShopware\Domain\Model\Shopware\Media $image) {
        $this->images->attach($image);
    }

    /**
     * Removes a image
     *
     * @param \Portrino\PxShopware\Domain\Model\Shopware\Media $imageToRemove The image to be removed
     *
     * @return void
     */
    public function removeMedia(\Portrino\PxShopware\Domain\Model\Shopware\Media $imageToRemove) {
        $this->images->detach($imageToRemove);
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Shopware\Media> $images
     */
    public function getImages() {
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Shopware\Media> $images
     *
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images) {
        $this->images = $images;
    }

}
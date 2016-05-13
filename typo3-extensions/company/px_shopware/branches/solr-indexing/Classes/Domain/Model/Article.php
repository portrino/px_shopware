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
use Portrino\PxShopware\Backend\Form\Wizard\SuggestEntryInterface;
use Portrino\PxShopware\Backend\Hooks\ItemEntryInterface;

/**
 * Class Article
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Article extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface{

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
     * @var string
     */
    protected $description = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Portrino\PxShopware\Domain\Model\Media>
     */
    protected $images;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Portrino\PxShopware\Domain\Model\Category>
     */
    protected $categories;

    /**
     * @var \Portrino\PxShopware\Domain\Model\Detail
     */
    protected $detail = array();

    /**
     * @var \Portrino\PxShopware\Service\Shopware\CategoryClientInterface
     * @inject
     */
    protected $categoryClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\MediaClientInterface
     * @inject
     */
    protected $mediaClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ArticleClientInterface
     * @inject
     */
    protected $articleClient;

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
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     *
     */
    public function initializeObject() {
        if (isset($this->getRaw()->images) && is_array($this->getRaw()->images)) {
            foreach ($this->raw->images as $image) {
                if (isset($image->mediaId)) {
                    /** @var Media $media */
                    $media = $this->mediaClient->findById($image->mediaId);
                    $this->addImage($media);
                }
            }
        }

        if (isset($this->getRaw()->categories)) {
            /**
             * we have to cast it to array, because the response is not of type array (maybe this is a bug in the shopware API)
             */
            $categories = (array)$this->raw->categories;
            foreach ($categories as $category) {
                if (isset($category->id)) {
                    /** @var Category $detailedCategory */
                    $detailedCategory = $this->categoryClient->findById($category->id);
                    $this->addCategory($detailedCategory);
                }
            }
        }

        if (!isset($this->getRaw()->mainDetail)) {
            /** @var Article $detail */
            $detailedArticle = $this->articleClient->findById($this->getId(), FALSE);
            /** @var Detail $detail */
            $detail = $this->objectManager->get(Detail::class, $detailedArticle->raw->mainDetail, $this->token);
            $this->setDetail($detail);
        } else if (isset($this->getRaw()->mainDetail)) {
            /** @var Detail $detail */
            $detail = $this->objectManager->get(Detail::class, $this->getRaw()->mainDetail, $this->token);
            $this->setDetail($detail);
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
    public function removeImage(\Portrino\PxShopware\Domain\Model\Media $imageToRemove) {
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
        return ($this->getImages() != NULL && $this->getImages()->count() > 0) ? array_values($this->getImages()->toArray())[0] : NULL;
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
    public function getOrderNumber() {
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

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
    }

    /**
     * Adds a category
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $category
     *
     * @return void
     */
    public function addCategory(\Portrino\PxShopware\Domain\Model\Category $category) {
        $this->categories->attach($category);
    }

    /**
     * Removes a category
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $categoryToRemove The category to be removed
     *
     * @return void
     */
    public function removeCategory(\Portrino\PxShopware\Domain\Model\Category $categoryToRemove) {
        $this->categories->detach($categoryToRemove);
    }

    /**
     * @return int
     */
    public function getSuggestId() {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSuggestLabel() {
        $result = $this->getName() . ' [' . $this->getId() . ']';
        $orderNumber = !empty($this->getOrderNumber()) ? ' (' . $this->getOrderNumber() . ')' : '';
        $result .= $orderNumber;
        return $result;
    }

    /**
     * @return string
     */
    public function getSuggestDescription() {
        /** @var Category $firstCategory */
        $firstCategory = $this->getCategories()->toArray()[0];
        if ($firstCategory) {
            $result = $firstCategory->getBreadCrumbPath();
        } else {
            $result = $this->getDescription();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier() {
        return 'px-shopware-article';
    }

    /**
     * @return int
     */
    public function getSelectItemId() {
        return (int)$this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel() {
        $result = $this->getName() . ' [' . $this->getId() . ']';
        $orderNumber = !empty($this->getOrderNumber()) ? ' (' . $this->getOrderNumber() . ')' : '';
        $result .= $orderNumber;
        return $result;
    }

}
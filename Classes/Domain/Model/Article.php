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

use Portrino\PxShopware\Backend\FormEngine\FieldControl\SuggestEntryInterface;
use Portrino\PxShopware\Backend\Hooks\ItemEntryInterface;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use Portrino\PxShopware\Service\Shopware\MediaClientInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Article
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Article extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var \DateTime $changed
     */
    protected $changed;

    /**
     * @var Uri
     */
    protected $uri = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $descriptionLong = '';

    /**
     * @var string
     */
    protected $orderNumber = null;

    /**
     * @var ObjectStorage<Media>
     */
    protected $images;

    /**
     * @var ObjectStorage<Category>
     */
    protected $categories;

    /**
     * @var Detail
     */
    protected $detail;

    /**
     * @var ObjectStorage<Detail>
     */
    protected $details;

    /**
     * @var CategoryClientInterface
     */
    protected $categoryClient;

    /**
     * @var MediaClientInterface
     */
    protected $mediaClient;

    /**
     * @var ArticleClientInterface
     */
    protected $articleClient;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function injectCategoryClient(CategoryClientInterface $categoryClient)
    {
        $this->categoryClient = $categoryClient;
    }

    public function injectMediaClient(MediaClientInterface $mediaClient)
    {
        $this->mediaClient = $mediaClient;
    }

    public function injectArticleClient(ArticleClientInterface $articleClient)
    {
        $this->articleClient = $articleClient;
    }

    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Article constructor.
     *
     * @param $raw
     * @param $token
     */
    public function __construct($raw, $token)
    {
        parent::__construct($raw, $token);

        if (isset($this->raw->name)) {
            $this->setName($this->raw->name);
        }

        if (isset($this->raw->pxShopwareOrderNumber)) {
            $this->setOrderNumber($this->raw->pxShopwareOrderNumber);
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
        } else {
            if (isset($this->raw->descriptionLong) && $this->raw->descriptionLong != '') {
                $this->setDescription($this->raw->descriptionLong);
            }
        }

        if (isset($this->raw->descriptionLong) && $this->raw->descriptionLong != '') {
            $this->setDescriptionLong($this->raw->descriptionLong);
        }

        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->images = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->details = new ObjectStorage();
    }

    /**
     *
     */
    public function initializeObject()
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * @param string $descriptionLong
     */
    public function setDescriptionLong($descriptionLong)
    {
        $this->descriptionLong = $descriptionLong;
    }


    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @param \DateTime|string $changed
     */
    public function setChanged($changed)
    {
        if (is_string($changed)) {
            $changed = new \DateTime($changed);
        }
        $this->changed = $changed;
    }

    /**
     * Adds a image
     *
     * @param Media $image
     *
     * @return void
     */
    public function addImage(Media $image)
    {
        $this->images->attach($image);
    }

    /**
     * Removes a image
     *
     * @param Media $imageToRemove The image to be removed
     *
     * @return void
     */
    public function removeImage(Media $imageToRemove)
    {
        $this->images->detach($imageToRemove);
    }

    /**
     * Returns the images
     *
     * @return ObjectStorage<Media> $images
     */
    public function getImages()
    {
        if ($this->images->count() === 0) {
            if (isset($this->getRaw()->images) && is_array($this->getRaw()->images)) {
                foreach ($this->getRaw()->images as $image) {
                    if (isset($image->mediaId)) {
                        /** @var Media $media */
                        $media = $this->mediaClient->findById($image->mediaId);
                        if ($media && is_a($media, Media::class)) {
                            $this->addImage($media);
                        }
                    }
                }
            }
        }
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param ObjectStorage<Media> $images
     *
     * @return void
     */
    public function setImages(ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Return the first image
     *
     * @return NULL|Media
     * @deprecated Use getTeaserImage()
     */
    public function getFirstImage()
    {
        return ($this->getImages() != null && $this->getImages()->count() > 0) ? array_values($this->getImages()->toArray())[0] : null;
    }

    /**
     * Return the main/ teaser image (selected in shopware backend)
     *
     * @return NULL|Media
     */
    public function getTeaserImage()
    {
        $result = null;
        if ($this->getImages() != null && $this->getImages()->count() > 0) {
            foreach ($this->getRaw()->images as $image) {
                if (isset($image->mediaId) && isset($image->main) && (int)$image->main === 1) {
                    /** @var Media $media */
                    $media = $this->mediaClient->findById($image->mediaId);
                    if ($media && is_a($media, Media::class)) {
                        $result = $media;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return Detail
     */
    public function getDetail()
    {
        if (!$this->detail) {

            /**
             * try to get the detail object from raw
             */
            if (!isset($this->getRaw()->mainDetail)) {
                /** @var Article $detailedArticle */
                $detailedArticle = $this->articleClient->findById($this->getId(), false);
                /** @var Detail $detail */
                $detail = $this->objectManager->get(Detail::class, $detailedArticle->raw->mainDetail, $this->token);
                $this->setDetail($detail);
            } else {
                if (isset($this->getRaw()->mainDetail)) {
                    /** @var Detail $detail */
                    $detail = $this->objectManager->get(Detail::class, $this->getRaw()->mainDetail, $this->token);
                    $this->setDetail($detail);
                }
            }
        }

        return $this->detail;
    }

    /**
     * @param Detail $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return ObjectStorage
     */
    public function getDetails()
    {
        if ($this->details->count() === 0) {

            // check if raw details are available or get them from API
            if (!isset($this->getRaw()->details)) {
                /** @var Article $detail */
                $detailedArticle = $this->articleClient->findById($this->getId(), false);
                $details = (array)$detailedArticle->raw->details;
            } else {
                $details = (array)$this->getRaw()->details;
            }
            // build Detail models and add them to ObjectStorage
            foreach ($details as $detailData) {
                /** @var Detail $detail */
                $detail = $this->objectManager->get(Detail::class, $detailData, $this->token);
                $this->addDetail($detail);
            }
        }
        return $this->details;
    }

    /**
     * @param ObjectStorage $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Adds a detail
     *
     * @param Detail $detail
     *
     * @return void
     */
    public function addDetail(Detail $detail)
    {
        $this->details->attach($detail);
    }

    /**
     * Removes a detail
     *
     * @param Detail $detailToRemove The detail to be removed
     *
     * @return void
     */
    public function removeDetail(Detail $detailToRemove)
    {
        $this->details->detach($detailToRemove);
    }


    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        $result = '';
        if ($this->orderNumber != null) {
            $result = $this->orderNumber;
        } else {
            $result = ($this->getDetail() != null) ? $this->getDetail()->getNumber() : '';
        }
        return $result;
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param Uri|string $uri
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
//            $uri = new \TYPO3\CMS\Core\Http\Uri($uri);
        }
        $this->uri = $uri;
    }

    /**
     * @return ObjectStorage
     */
    public function getCategories()
    {

        if ($this->categories->count() === 0) {
            if (isset($this->getRaw()->categories)) {
                /**
                 * we have to cast it to array, because the response is not of type array (maybe this is a bug in the shopware API)
                 */
                $categories = (array)$this->raw->categories;
                foreach ($categories as $category) {
                    if (isset($category->id)) {
                        /** @var Category $detailedCategory */
                        $detailedCategory = $this->categoryClient->findById($category->id);
                        if (!$detailedCategory || !is_a($detailedCategory, Category::class)) {
                            continue;
                        }

                        /**
                         * Get the current language
                         * -> depends on the TYPO3_MODE
                         */
                        if (TYPO3_MODE === 'FE') {
                            $language = GeneralUtility::trimExplode('.',
                                $GLOBALS['TSFE']->config['config']['sys_language_uid'], true);
                            $sys_language_id = ($language && isset($language[0])) ? $language[0] : 0;
                            // add only categories of current FE language
                            if ($detailedCategory->getLanguage() == $sys_language_id) {
                                $this->addCategory($detailedCategory);
                            }
                        } else {
                            // if BE access add all categories, needs to be filtered elsewhere
                            $this->addCategory($detailedCategory);
                        }
                    }
                }
            }
        }

        return $this->categories;
    }

    /**
     * @param ObjectStorage $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Adds a category
     *
     * @param Category $category
     *
     * @return void
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a category
     *
     * @param Category $categoryToRemove The category to be removed
     *
     * @return void
     */
    public function removeCategory(Category $categoryToRemove)
    {
        $this->categories->detach($categoryToRemove);
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
        $result = $this->getName() . ' [' . $this->getId() . ']';
        $orderNumber = !empty($this->getOrderNumber()) ? ' (' . $this->getOrderNumber() . ')' : '';
        $result .= $orderNumber;
        return $result;
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {
        return $this->getDescription();
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return 'px-shopware-article';
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
        $result = $this->getName() . ' [' . $this->getId() . ']';
        $orderNumber = !empty($this->getOrderNumber()) ? ' (' . $this->getOrderNumber() . ')' : '';
        $result .= $orderNumber;
        return $result;
    }

}

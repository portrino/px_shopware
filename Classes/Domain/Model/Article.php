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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @var \TYPO3\CMS\Core\Http\Uri
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
    protected $detail;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Portrino\PxShopware\Domain\Model\Detail>
     */
    protected $details;

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
        $this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->details = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * @param \Portrino\PxShopware\Domain\Model\Media $image
     *
     * @return void
     */
    public function addImage(\Portrino\PxShopware\Domain\Model\Media $image)
    {
        $this->images->attach($image);
    }

    /**
     * Removes a image
     *
     * @param \Portrino\PxShopware\Domain\Model\Media $imageToRemove The image to be removed
     *
     * @return void
     */
    public function removeImage(\Portrino\PxShopware\Domain\Model\Media $imageToRemove)
    {
        $this->images->detach($imageToRemove);
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Media> $images
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
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Media> $images
     *
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Return the first image
     *
     * @return NULL|\Portrino\PxShopware\Domain\Model\Media
     * @deprecated Use getTeaserImage()
     */
    public function getFirstImage()
    {
        return ($this->getImages() != null && $this->getImages()->count() > 0) ? array_values($this->getImages()->toArray())[0] : null;
    }

    /**
     * Return the main/ teaser image (selected in shopware backend)
     *
     * @return NULL|\Portrino\PxShopware\Domain\Model\Media
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
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
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
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Adds a detail
     *
     * @param \Portrino\PxShopware\Domain\Model\Detail $detail
     *
     * @return void
     */
    public function addDetail(\Portrino\PxShopware\Domain\Model\Detail $detail)
    {
        $this->details->attach($detail);
    }

    /**
     * Removes a detail
     *
     * @param \Portrino\PxShopware\Domain\Model\Detail $detailToRemove The detail to be removed
     *
     * @return void
     */
    public function removeDetail(\Portrino\PxShopware\Domain\Model\Detail $detailToRemove)
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
     * @return \TYPO3\CMS\Core\Http\Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param \TYPO3\CMS\Core\Http\Uri|string $uri
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
//            $uri = new \TYPO3\CMS\Core\Http\Uri($uri);
        }
        $this->uri = $uri;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
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
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Adds a category
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $category
     *
     * @return void
     */
    public function addCategory(\Portrino\PxShopware\Domain\Model\Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a category
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $categoryToRemove The category to be removed
     *
     * @return void
     */
    public function removeCategory(\Portrino\PxShopware\Domain\Model\Category $categoryToRemove)
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
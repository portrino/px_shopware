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
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use Portrino\PxShopware\Service\Shopware\LanguageToShopwareMappingService;
use Portrino\PxShopware\Service\Shopware\MediaClientInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Category
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Category extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
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
     * @var Media
     */
    protected $image;

    /**
     * @var CategoryClientInterface
     */
    protected $categoryClient;

    /**
     * @var MediaClientInterface
     */
    protected $mediaClient;

    /**
     * @var ObjectStorage<Category>
     */
    protected $path;

    /**
     * @var int
     */
    protected $language;

    /**
     * @var LanguageToShopwareMappingService
     */
    protected $languageToShopMappingService;

    public function injectCategoryClient(CategoryClientInterface $categoryClient)
    {
        $this->categoryClient = $categoryClient;
    }

    public function injectMediaClient(MediaClientInterface $mediaClient)
    {
        $this->mediaClient = $mediaClient;
    }

    public function injectLanguageToShopwareMappingService(LanguageToShopwareMappingService $languageToShopwareMappingService)
    {
        $this->languageToShopMappingService = $languageToShopwareMappingService;
    }

    /**
     * @param object $raw
     * @param string $token
     */
    public function __construct($raw, $token)
    {
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

        if ($this->raw->path) {
            if (!$this->languageToShopMappingService) {
                $this->languageToShopMappingService = $this->objectManager->get(LanguageToShopwareMappingService::class);
            }
            $this->language = $this->languageToShopMappingService->getSysLanguageUidByParentCategoryPath($this->raw->path);
        }
    }

    public function initializeObject()
    {
        $this->path = new ObjectStorage();
        if (isset($this->getRaw()->mediaId)) {
            /** @var Media $media */
            $media = $this->mediaClient->findById($this->getRaw()->mediaId);
            if ($media && is_a($media, Media::class)) {
                $this->setImage($media);
            }
        }
    }

    /**
     * @return ObjectStorage<Category>
     */
    public function getSubCategories()
    {
        return $this->categoryClient->findByParent($this->id);
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
            $uri = new Uri($uri);
        }
        $this->uri = $uri;
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
     * @return Media
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Media $image
     */
    public function setImage(Media $image)
    {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param ObjectStorage $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Adds a path element
     *
     * @param Category $pathElement
     *
     * @return void
     */
    public function addPathElement(Category $pathElement)
    {
        $this->path->attach($pathElement);
    }

    /**
     * Removes a path
     *
     * @param Category $pathElementToRemove The path element to be removed
     *
     * @return void
     */
    public function removePathElement(Category $pathElementToRemove)
    {
        $this->path->detach($pathElementToRemove);
    }

    /**
     * @param bool $includeSelf TRUE if this element should be included in bread crumb path, FALSE if not
     *
     * @return mixed
     */
    public function getBreadCrumbPath($includeSelf = true)
    {

        if (isset($this->getRaw()->path) && $this->getRaw()->path != '') {
            $pathArray = array_reverse(GeneralUtility::trimExplode('|', $this->getRaw()->path, true));
            foreach ($pathArray as $pathItem) {
                /** @var Category|NULL $pathElement */
                $pathElement = $this->categoryClient->findById($pathItem);
                if ($pathElement && is_a($pathElement, Category::class)) {
                    $this->addPathElement($pathElement);
                }
            }
        }

        /** @var array $path */
        $path = array_map(function ($item) {
            return $item->getName();
        }, $this->getPath()->toArray());
        if ($includeSelf === true) {
            array_push($path, $this->getName());
        }
        return implode('/', $path);
    }

    /**
     * @return int
     */
    public function getSelectItemId()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel()
    {
        return $this->getName() . ' [' . $this->getId() . ']';
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
        return $this->getName() . ' [' . $this->getId() . ']';
    }

    /**
     * @return string
     */
    public function getSuggestDescription()
    {
        return $this->getBreadCrumbPath(false);
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier()
    {
        return 'px-shopware-category';
    }

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param int $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

}

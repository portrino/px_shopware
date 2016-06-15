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
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Category
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Category extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface {

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
     * @var \Portrino\PxShopware\Domain\Model\Media
     */
    protected $image;

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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Portrino\PxShopware\Domain\Model\Category>
     */
    protected $path;

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
        $this->path = new ObjectStorage();
    }

    /**
     *
     */
    public function initializeObject() {


        if (isset($this->getRaw()->media) && is_object($this->getRaw()->media) && isset($this->getRaw()->media->id)) {
            $media = $this->mediaClient->findById($this->getRaw()->media->id);
            $this->setImage($media);
        }
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

    /**
     * @return \Portrino\PxShopware\Domain\Model\Media
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @param \Portrino\PxShopware\Domain\Model\Media $image
     */
    public function setImage(\Portrino\PxShopware\Domain\Model\Media $image) {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param ObjectStorage $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * Adds a path element
     *
     * @param \Portrino\PxShopware\Domain\Model\Category $pathElement
     *
     * @return void
     */
    public function addPathElement(\Portrino\PxShopware\Domain\Model\Category $pathElement) {
        $this->path->attach($pathElement);
    }

    /**
     * Removes a path
     *
     * @param \Portrino\PxShopware\Domain\Model\Path $pathElementToRemove The path element to be removed
     *
     * @return void
     */
    public function removePathElement(\Portrino\PxShopware\Domain\Model\Category $pathElementToRemove) {
        $this->path->detach($pathElementToRemove);
    }

    /**
     * @param bool $includeSelf TRUE if this element should be included in bread crumb path, FALSE if not
     *
     * @return mixed
     */
    public function getBreadCrumbPath($includeSelf = TRUE) {

        if (isset($this->getRaw()->path) && $this->getRaw()->path != '') {
            $pathArray = array_reverse(GeneralUtility::trimExplode('|', $this->getRaw()->path, TRUE));
            foreach ($pathArray as $pathItem) {
                /** @var Category|NULL $pathElement */
                $pathElement = $this->categoryClient->findById($pathItem);
                if ($pathElement) {
                    $this->addPathElement($pathElement);
                }
            }
        }

        /** @var array $path */
        $path = array_map(function($item) {
            return $item->getName();
        }, $this->getPath()->toArray());
        if ($includeSelf === TRUE) {
            array_push($path, $this->getName());
        }
        return implode('/', $path);
    }

    /**
     * @return int
     */
    public function getSelectItemId() {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getSelectItemLabel() {
        return $this->getName() . ' [' . $this->getId() . ']';
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
        return $this->getName() . ' [' . $this->getId() . ']';
    }

    /**
     * @return string
     */
    public function getSuggestDescription() {
        return $this->getBreadCrumbPath(FALSE);
    }

    /**
     * @return string
     */
    public function getSuggestIconIdentifier() {
        return 'px-shopware-category';
    }

}
<?php
namespace Portrino\PxShopware\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Axel Boeswetter <boeswetter@portrino.de>, portrino GmbH
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
 * Class Variant
 *
 * @package Portrino\PxShopware\Domain\Model
 */
class Variant extends AbstractShopwareModel implements SuggestEntryInterface, ItemEntryInterface
{

    /**
     * @var string
     */
    protected $number;

    /**
     * @var \TYPO3\CMS\Core\Http\Uri
     */
    protected $uri = '';

    /**
     * @var \Portrino\PxShopware\Domain\Model\Article
     */
    protected $article;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\ArticleClientInterface
     * @inject
     */
    protected $articleClient;

    /**
     * @var \Portrino\PxShopware\Service\Shopware\VariantClientInterface
     * @inject
     */
    protected $variantClient;

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

        if (isset($this->raw->number)) {
            $this->setNumber($this->raw->number);
        }

        if (isset($this->raw->pxShopwareUrl)) {
            $this->setUri($this->raw->pxShopwareUrl);
        }
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        $result = '';
        if ($this->number !== null) {
            $result = $this->number;
        } else {
            $result = ($this->getDetail() !== null) ? $this->getDetail()->getNumber() : '';
        }
        return $result;
    }


    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
        $this->uri = $uri;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        if (!$this->article) {
            if (!$this->getRaw()->article) {
                /** @var Article $article */
                $article = $this->articleClient->findById($this->getRaw()->articleId, false);
                $article->setOrderNumber($this->getNumber());
                $article->setUri($this->getUri());
            } else {
                $raw = $this->getRaw();
                $raw->article->pxShopwareOrderNumber = $this->getNumber();
                $raw->article->pxShopwareUrl = $this->getUri();
                $this->setRaw($raw);

                $article = $this->objectManager->get(Article::class, $this->getRaw()->article, $this->token);
            }

            $detail = $this->objectManager->get(Detail::class, $this->getRaw(), $this->token);
            $article->setDetail($detail);

            $this->setArticle($article);
        }
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getArticle()->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getArticle()->getDescription();
    }

    /**
     * @return string
     */
    public function getFirstImage()
    {
        return $this->getArticle()->getFirstImage();
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
        $number = !empty($this->getNumber()) ? ' (' . $this->getNumber() . ')' : '';
        $result .= $number;
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
        return $this->getSuggestLabel();
    }

}
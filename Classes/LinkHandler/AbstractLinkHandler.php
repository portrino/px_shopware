<?php

namespace Portrino\PxShopware\LinkHandler;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\Category;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

class AbstractLinkHandler extends \TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler implements LinkHandlerInterface, LinkParameterProviderInterface
{
    /**
     * @var string[]
     */
    protected $linkAttributes = [];

    /**
     * @var bool
     */
    protected $updateSupported = false;

    /**
     * @var AbstractShopwareApiClientInterface
     */
    protected $client;

    /**
     * @var Article|Category|null
     */
    protected $object;

    /**
     * @var array
     */
    protected $linkParts = [];

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @inheritdoc
     */
    public function canHandleLink(array $linkParts)
    {
        if (!isset($linkParts['type']) || $linkParts['type'] !== 'shopware_' . $this->type) {
            return false;
        }
        if (isset($linkParts['url'][$this->type]) && (int)$linkParts['url'][$this->type] > 0) {
            $this->linkParts = $linkParts;
            /** @var Article|Category|null $object */
            $object = $this->client->findById((int)$linkParts['url'][$this->type]);
            $this->object = $object;
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function formatCurrentUrl()
    {
        return $this->object ? $this->object->getSelectItemLabel() : '';
    }

    /**
     * @inheritdoc
     */
    public function render(ServerRequestInterface $request)
    {
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/PxShopware/' . ucfirst($this->type) . 'LinkHandler');
        $this->view->getRequest()->setControllerExtensionName('PxShopware');
        $this->view->setLayoutRootPaths(['EXT:px_shopware/Resources/Private/Layouts/Backend']);
        $this->view->setTemplateRootPaths(['EXT:px_shopware/Resources/Private/Templates/Backend/LinkBrowser']);
        $this->view->setTemplate('Shopware' . ucfirst($this->type));

//        $this->view->assign('object', $this->object);
//        $this->view->assign('currentIdentifier', $this->object ? $this->object->getId() : 0);
        $this->view->assign($this->type, !empty($this->linkParts) ? $this->linkParts['url'][$this->type] : '');
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getBodyTagAttributes()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getUrlParameters(array $values)
    {
        return $this->linkBrowser->getUrlParameters($values);
    }

    /**
     * @inheritdoc
     */
    public function isCurrentlySelectedItem(array $values)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getScriptUrl()
    {
        return $this->linkBrowser->getScriptUrl();
    }
}

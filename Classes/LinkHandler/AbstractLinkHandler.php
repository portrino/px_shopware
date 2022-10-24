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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

class AbstractLinkHandler extends \TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler implements LinkHandlerInterface, LinkParameterProviderInterface
{

    /**
     * @var AbstractShopwareApiClientInterface
     */
    protected $client;

    /**
     * @var Article|Category
     */
    protected $object;

    /**
     * @var string
     */
    protected $type = '';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function canHandleLink(array $linkParts)
    {
        if (!$linkParts['url']) {
            return false;
        }
        $url = rawurldecode($linkParts['url']);
        if (StringUtility::beginsWith($url, $this->getPrefix())) {
            $id = (int)substr($url, strlen($this->getPrefix()));
            $this->object = $this->client->findById($id);
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
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/PxShopware/' . ucfirst($this->type) .  'LinkHandler');
        $listContent = '';

        $objects = $this->client->findAll();
        if ($objects->count()) {
            $titleLen = (int)$this->getBackendUser()->uc['titleLen'];
            $currentIdentifier = $this->object ? $this->object->getId() : 0;

            $listContent .= '<ul class="list-tree">';
            foreach ($objects as $object) {
                $selected = $currentIdentifier === $object->getId() ? ' class="active"' : '';
                $icon = '<span title="' . htmlspecialchars($object->getSelectItemLabel()) . '">'
                    . $this->iconFactory->getIcon('px-shopware-' . $this->type, Icon::SIZE_SMALL)
                    . '</span>';
                $listContent .=
                    '<li' . $selected . '>
                        <span class="list-tree-group">
                            <a href="#" class="t3js-fileLink list-tree-group" title="' . htmlspecialchars($object->getSelectItemLabel()) . '" data-' . $this->type . '="' . $this->getPrefix() . htmlspecialchars($object->getId()) . '">
                                <span class="list-tree-icon">' . $icon . '</span>
                                <span class="list-tree-title">' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($object->getSelectItemLabel(), $titleLen)) . '</span>
                            </a>
                        </span>
                    </li>';
            }
            $listContent .= '</ul>';
        }

        $content = '<table border="0" cellpadding="0" cellspacing="0" id="typo3-linkFiles">
                        <tr>
                            <td class="c-wCell" valign="top"><h3>' . $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:link_handler.' . $this->type) . ':</h3>' . $listContent . '</td>
                        </tr>
                    </table>';

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getBodyTagAttributes()
    {
        return [
            'data-current-link' => $this->object ? $this->getPrefix() . $this->object->getId() : ''
        ];
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

    protected function getPrefix()
    {
        return 'shopware_' . $this->type . ':';
    }
}
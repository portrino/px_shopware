<?php
namespace Portrino\PxShopware\Backend\Toolbar;

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

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ClearCacheMenu
 *
 * @package Portrino\PxShopware\Backend\Toolbar
 */
class ClearCacheMenu implements \TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface
{

    /**
     * Add varnish cache clearing to clear cache menu
     *
     * @param array $cacheActions
     * @param array $optionValues
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        if ($this->getBackendUser()->isAdmin()) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $cacheActions[] = [
                'id' => 'px_shopware',
                'title' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:clear_cache_menu.title',
                'description' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:clear_cache_menu.description',
                'href' => $uriBuilder->buildUriFromRoute('px_shopware_clear_cache'),
                'iconIdentifier' => 'px-shopware-clear-cache'
            ];
            $optionValues[] = 'clearCache';
        }
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns the icon for the cache menu, depending on the TYPO3 version
     *
     * @return string
     */
    protected function getIcon()
    {
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon('px-shopware-clear-cache', Icon::SIZE_SMALL)->render();
    }

}

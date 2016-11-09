<?php
namespace Portrino\PxShopware\Compatibility6\Backend\Toolbar;

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
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class ClearCacheMenu
 * @package Portrino\PxShopware\Compatibility6\Backend\Toolbar
 */
class ClearCacheMenu extends \Portrino\PxShopware\Backend\Toolbar\ClearCacheMenu
{

    /**
     * Returns the icon for the cache menu, depending on the TYPO3 version
     *
     * @return string
     */
    protected function getIcon()
    {
        $result = '<img ' . IconUtility::skinImg($GLOBALS['BACK_PATH'],
                ExtensionManagementUtility::extRelPath('px_shopware') . '/Resources/Public/Icons/Compatibility6/clear_cache.png',
                'width="16" height="16"') . ' />';

        return $result;
    }

}

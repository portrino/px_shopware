<?php
namespace Portrino\PxShopware\Compatibility6\Backend\Hooks;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageLayoutViewDraw
 * @package Portrino\PxShopware\Compatibility6\Backend\Hooks
 */
class PageLayoutViewDraw extends \Portrino\PxShopware\Backend\Hooks\PageLayoutViewDraw
{
    /**
     * @param string $CType
     */
    protected function initializeView($CType)
    {
        $templateName = str_replace('Pxshopware', '', GeneralUtility::underscoredToUpperCamelCase($CType));

        $this->view->setTemplatePathAndFilename(
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extensionKey) .
            'Resources/Private/Templates/Backend/PageLayoutViewDrawItem/' . $templateName . '.html'
        );
    }

}

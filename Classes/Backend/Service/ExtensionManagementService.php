<?php
namespace Portrino\PxShopware\Backend\Service;

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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class ExtensionManagementService
 * @package Portrino\PxShopware\Backend\Service
 */
class ExtensionManagementService implements SingletonInterface
{
    /**
     * Adds an entry to the "ds" array of the tt_content field "pi_flexform".
     * This is used by plugins to add a flexform XML reference / content for use when they are selected as plugin or content element.
     *
     * @param string $piKeyToMatch Plugin key as used in the list_type field. Use the asterisk * to match all list_type values.
     * @param string $value Either a reference to a flex-form XML file (eg. "FILE:EXT:newloginbox/flexform_ds.xml") or the XML directly.
     * @param string $CTypeToMatch Value of tt_content.CType (Content Type) to match. The default is "list" which corresponds to the "Insert Plugin" content element.  Use the asterisk * to match all CType values.
     * @return void
     * @see addPlugin()
     */
    public function addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch = 'list')
    {
        ExtensionManagementUtility::addPiFlexFormValue($piKeyToMatch, $value, $CTypeToMatch);
    }

}
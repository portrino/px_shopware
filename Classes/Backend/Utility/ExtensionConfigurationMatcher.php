<?php
namespace Portrino\PxShopware\Backend\Utility;

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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionConfigurationMatcher
 *
 * @package Portrino\PxShopware\Backend\Utility
 */
class ExtensionConfigurationMatcher
{

    /**
     * @param array $conditionParameters
     *
     * @return bool
     */
    public static function isFeatureEnabled($conditionParameters)
    {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('px_shopware');
        $result = $extConf;
        foreach ($conditionParameters as $conditionParameter) {
            $result = $result[$conditionParameter];
        }
        return (boolean)$result;
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    public static function isFeatureDisabled($config)
    {
        return !self::isFeatureEnabled($config);
    }
}

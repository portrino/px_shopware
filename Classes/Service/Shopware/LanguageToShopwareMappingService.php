<?php
namespace Portrino\PxShopware\Service\Shopware;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class LanguageToShopwareMappingService
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class LanguageToShopwareMappingService implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $configurationManager;

    /**
     *
     * @var array
     */
    protected $settings;

    public function initializeObject()
    {
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');
    }

    /**
     * @param int $sys_language_uid
     *
     * @return integer the shop id for the specific language in SW
     */
    public function getShopIdBySysLanguageUid($sys_language_uid)
    {
        /**
         * set shop_id to 1 per default
         */
        $result = 1;
        $shopToLocaleMappings = $this->settings['api']['languageToShopware'];

        foreach ($shopToLocaleMappings as $shopToLocaleMapping) {
            if ((int)$sys_language_uid === (int)$shopToLocaleMapping['sys_language_uid']) {
                $result = (int)$shopToLocaleMapping['shop_id'];
                break;
            }
        }
        return $result;
    }

    /**
     * @param int $sys_language_uid
     *
     * @return integer the parent category id for the specific language in SW
     */
    public function getParentCategoryBySysLanguageUid($sys_language_uid)
    {
        $result = 0;
        $shopToLocaleMappings = $this->settings['api']['languageToShopware'];

        foreach ($shopToLocaleMappings as $shopToLocaleMapping) {
            if ((int)$sys_language_uid === (int)$shopToLocaleMapping['sys_language_uid']) {
                $result = (int)$shopToLocaleMapping['parentCategory'];
                break;
            }
        }
        return $result;
    }

    /**
     * @param string $path the parent category id for the specific language in SW
     *
     * @return integer $sys_language_uid
     */
    public function getSysLanguageUidByParentCategoryPath($path)
    {
        $result = 0;

        $pathArray = GeneralUtility::trimExplode('|', $path, true);
        $shopToLocaleMappings = $this->settings['api']['languageToShopware'];

        foreach ($shopToLocaleMappings as $shopToLocaleMapping) {
            if (in_array($shopToLocaleMapping['parentCategory'], $pathArray)) {
                $result = (int)$shopToLocaleMapping['sys_language_uid'];
                break;
            }
        }

        return $result;
    }

}

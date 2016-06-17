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

/**
 * Class LocaleToShopMappingService
 *
 * @package Portrino\PxShopware\Service\Shopware
 */
class LocaleToShopMappingService implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     *
     * @var array
     */
    protected $settings;

    /**
     *
     */
    public function initializeObject() {
        /**
         * config from TS or flexform
         */
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware');


    }

    /**
     * @param int $sys_language_uid
     *
     * @return integer
     */
    public function getShopIdBySysLanguageUid($sys_language_uid) {
        $shopToLocaleMappings = $this->settings['api']['shopToLocale'];

        foreach ($shopToLocaleMappings as $shopId => $shopToLocaleMapping) {
            if ((int)$sys_language_uid === (int)$shopToLocaleMapping['sys_language_uid']) {
                return $shopId;
            }
        }
        return 1;
    }

}

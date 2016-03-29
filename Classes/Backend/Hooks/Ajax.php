<?php
namespace Portrino\PxShopware\Backend\Hooks;

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
 * Class Ajax
 *
 * @package Portrino\PxShopware\Backend\Hooks
 */
class Ajax {

    /**
     * @var string Key of the extension
     */
    protected $extensionKey = 'px_shopware';

    /**
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function clearCache() {
        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);

        /**
         * create one cache for each endpoint
         */
        $endpoints = array('articles', 'categories', 'media1');
        foreach ($endpoints as $endpoint) {
            if ($cacheManager->hasCache($this->extensionKey . '_' . $endpoint)) {
                $cacheManager->getCache($this->extensionKey . '_' . $endpoint)->flush();
            }
        }
    }

}

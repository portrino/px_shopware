<?php
namespace Portrino\PxShopware\Cache;

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
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CacheChainFactory
 *
 * @package Portrino\PxShopware\Backend\Form\Wizard
 */
class CacheChainFactory implements SingletonInterface {

    /**
     * @return CacheChain
     */
    public function create() {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheChain = new CacheChain();

        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['px_shopware']['cache_chain'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXT']['px_shopware']['cache_chain'] as $cachePriority => $cacheIdentifier) {
                $cache = ($cacheManager->hasCache($cacheIdentifier)) ? $cacheManager->getCache($cacheIdentifier) : NULL;
                if ($cache) {
                    $cacheChain->addCache($cache, $cachePriority);
                }
            }
        }
        return $cacheChain;
    }
}

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

use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CacheChain
 *
 * @package Portrino\PxShopware\Backend\Form\Wizard
 */
class CacheChain implements FrontendInterface, SingletonInterface {

    /**
     * @var array
     */
    protected $chain = [];

    /**
     * @var LogManager
     */
    protected $logger;

    /**
     * @var FrontendInterface
     */
    private $transientMemoryCache;

    /**
     * @param FrontendInterface $cache
     * @param int $priority
     */
    public function addCache($cache, $priority) {
        $this->chain[$priority] = $cache;
        if ($cache->getBackend() instanceof TransientMemoryBackend) {
            $this->transientMemoryCache = $cache;
        }
    }


    public function getIdentifier() {
        return 'cache_chain';
    }

    public function getBackends() {
        $result = [];
        /**
         * @var int $priority
         * @var FrontendInterface $cache
         */
        foreach ($this->chain as $priority => $cache) {
            $result[$priority] = $cache->getBackend();
        }
        return $result;
    }

    /**
     * Returns the backend used by this cache
     *
     * @return \TYPO3\CMS\Core\Cache\Backend\BackendInterface The backend used by this cache
     */
    public function getBackend() {

    }

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier Something which identifies the data - depends on concrete cache
     * @param mixed $data The data to cache - also depends on the concrete cache implementation
     * @param array $tags Tags to associate with this cache entry
     * @param int $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
     * @return void
     * @api
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = NULL) {
        /**
         * @var FrontendInterface $cache
         */
        foreach ($this->chain as $cache) {
            $cache->set($entryIdentifier, $data, $tags, $lifetime);
        }
    }

    /**
     * Finds and returns data from the cache.
     *
     * @param string $entryIdentifier Something which identifies the cache entry - depends on concrete cache
     * @return mixed
     * @api
     */
    public function get($entryIdentifier) {
        $result = false;

        ksort($this->chain);

        /**
         * @var int $priority
         * @var FrontendInterface $cache
         */
        foreach ($this->chain as $priority => $cache) {
            $result = $cache->get($entryIdentifier);
            if ($result !== false) {
                /**
                 * if the entry identifier was found in database, store the result in the faster transient memory cache
                 */
                if ($cache->getBackend() instanceof Typo3DatabaseBackend) {
                    /** @var LogManager $logger */
                    $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                    $this->logger->log(LogLevel::INFO, $entryIdentifier);

                    if ($this->transientMemoryCache !== null) {
                        $this->transientMemoryCache->set($entryIdentifier, $result);
                    }
                }
                break;
            }
        }
        return $result;
    }

    public function isActive() {
        return (count($this->chain) > 0);
    }

    /**
     * @return array
     */
    public function getCacheTables() {
        $result = [];
        /** @var BackendInterface $backend */
        foreach ($this->getBackends() as $backend) {
            /** @var Typo3DatabaseBackend $backend */
            if ($backend instanceof Typo3DatabaseBackend) {
                $result[] = $backend->getCacheTable();
            }
        }
        return $result;
    }

    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $tag The tag to search for
     * @return array An array with the content of all matching entries. An empty array if no entries matched
     * @api
     */
    public function getByTag($tag) {
        // TODO: Implement getByTag() method.
    }

    public function has($entryIdentifier) {
        $result = false;
        /** @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $result = $cache->has($entryIdentifier);
            if ($result !== false) {
                break;
            }
        }
        return $result;
    }

    public function remove($entryIdentifier) {
        /** @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $cache->remove($entryIdentifier);
        }
    }

    public function flush() {
        /**  @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $cache->flush();
        }
    }

    public function flushByTag($tag) {
        /** @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $cache->flushByTag($tag);
        }
    }

    /**
     * Removes all cache entries of this cache which are tagged by any of the specified tags.
     *
     * @param string[] $tags
     * @throws \InvalidArgumentException
     */
    public function flushByTags(array $tags)
    {
        /** @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $cache->flushByTags($tags);
        }
    }

    public function collectGarbage() {
        /**  @var FrontendInterface $cache */
        foreach ($this->chain as $cache) {
            $cache->collectGarbage();
        }
    }

    public function isValidEntryIdentifier($identifier) {
        // TODO: Implement isValidEntryIdentifier() method.
    }

    public function isValidTag($tag) {
        // TODO: Implement isValidTag() method.
    }
}

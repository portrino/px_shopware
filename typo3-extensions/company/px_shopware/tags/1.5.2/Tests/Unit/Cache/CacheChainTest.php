<?php

namespace Portrino\PxShopware\Tests\Unit\Cache;

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
use Portrino\PxShopware\Cache\CacheChain;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * Class CacheChainTest
 *
 * @package Portrino\PxShopware\Tests\Unit\Cache
 */
class CacheChainTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

    /**
     * @var CacheChain
     */
    protected $cache;

    public function setUp() {
        $this->cache = new CacheChain();
    }

    public function tearDown() {
        unset($this->cache);
    }

    /**
     *
     * @test
     * @return void
     */
    public function noCachesInCacheChainTest() {
        $this->cache->set('123456', 'foo');
        $this->assertFalse($this->cache->get('123456'));
    }

    /**
     *
     * @test
     * @return void
     */
    public function oneCacheInCacheChainTest() {
        $cacheStub = $this->getMock(FrontendInterface::class);
        $cacheStub->expects($this->any())->method('get')->will($this->returnValue('foo'));
        $this->cache->addCache($cacheStub, 0);
        $this->assertEquals('foo', $this->cache->get('123456'));
    }

    /**
     *
     * @test
     * @return void
     */
    public function twoCachesInCacheChainPriorityTest() {
        $cacheLevel1Stub = $this->getMock(FrontendInterface::class);
        $cacheLevel1Stub->expects($this->any())->method('get')->will($this->returnValue('foo'));

        $cacheLevel2Stub = $this->getMock(FrontendInterface::class);
        $cacheLevel2Stub->expects($this->any())->method('get')->will($this->returnValue('bar'));

        $this->cache->addCache($cacheLevel2Stub, 1);
        $this->cache->addCache($cacheLevel1Stub, 0);

        $this->assertEquals('foo', $this->cache->get('123456'));
    }

}

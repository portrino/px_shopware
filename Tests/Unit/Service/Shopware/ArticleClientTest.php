<?php

namespace Portrino\PxShopware\Tests\Unit\Service\Shopware;

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
use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\ShopwareModelInterface;
use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClient;
use Portrino\PxShopware\Service\Shopware\ArticleClient;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ArticleClientTest
 *
 * @package Portrino\PxShopware\Tests\Unit\Service\Shopware
 */
class ArticleClientTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

    public function setUp() {
    }

    public function tearDown() {
    }

    /**
     *
     * @test
     * @return void
     */
    public function findByIdTest() {
        $raw = json_decode('{"data": {"id": 2}}');
        /** @var ArticleClient $stub */
        $stub = $this->getMockBuilder(ArticleClient::class)->setMethods(['call', 'getValidEndpoint'])->getMock();
        $objectManager = $this->getMock(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $objectManager->expects($this->any())->method('get')->will($this->returnValue(new Article($raw->data, FALSE)));
        $this->inject($stub, 'objectManager', $objectManager);

        $stub->expects($this->any())->method('getValidEndpoint')->will($this->returnValue('http://www.foo.bar/api/'));
        $stub->expects($this->any())->method('call')->will($this->returnValue($raw));

        $result = $stub->findById(2);

        $this->assertNotNull($result);
        $this->assertEquals(2, $result->getId());
    }


    /**
     *
     * @test
     * @return void
     */
    public function findByTermTest() {
        $term = 'VEN';
        $raw = json_decode('{"data": [{"id": 2, "name": "VENOM L"}, {"id": 5, "name": "VENOM SKI"}]}');
        /** @var ArticleClient $stub */
        $stub = $this->getMockBuilder(ArticleClient::class)->setMethods(['call', 'getValidEndpoint'])->getMock();
        $objectManager = $this->getMock(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $objectManager
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    new Article(json_decode('{"id": 2, "name": "VENOM L"}'), FALSE),
                    new Article(json_decode('{"id": 5, "name": "VENOM SKI"}'), FALSE)
                )
            );
        $this->inject($stub, 'objectManager', $objectManager);

        $stub->expects($this->any())->method('getValidEndpoint')->will($this->returnValue('http://www.foo.bar/api/'));
        $stub->expects($this->any())->method('call')->will($this->returnValue($raw));

        $result = $stub->findByTerm($term);

        $this->assertInstanceOf(ObjectStorage::class, $result);
        $this->assertEquals(2, $result->count());
        $this->assertEquals('VENOM L', $result->toArray()[0]->getName());
        $this->assertEquals(5, $result->toArray()[1]->getId());
    }
}

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
use Portrino\PxShopware\Service\Shopware\ArticleClient;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ArticleClientTest
 */
class ArticleClientTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function findByIdTest()
    {
        $raw = json_decode('{"data": {"id": 2}}');
        /** @var ArticleClient $stub */
        $stub = $this->getMockBuilder(ArticleClient::class)->setMethods(['call', 'getValidEndpoint'])->getMock();
        $objectManager = $this->getMock(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $objectManager->expects(self::any())->method('get')->willReturn(new Article($raw->data, false));
        $this->inject($stub, 'objectManager', $objectManager);

        $stub->expects(self::any())->method('getValidEndpoint')->willReturn('http://www.foo.bar/api/');
        $stub->expects(self::any())->method('call')->willReturn($raw);

        $result = $stub->findById(2);

        self::assertNotNull($result);
        self::assertEquals(2, $result->getId());
    }

    /**
     * @test
     */
    public function findByTermTest()
    {
        $term = 'VEN';
        $raw = json_decode('{"data": [{"id": 2, "name": "VENOM L"}, {"id": 5, "name": "VENOM SKI"}]}');
        /** @var ArticleClient $stub */
        $stub = $this->getMockBuilder(ArticleClient::class)->setMethods(['call', 'getValidEndpoint'])->getMock();
        $objectManager = $this->getMock(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $objectManager
            ->expects(self::any())
            ->method('get')
            ->will(
                self::onConsecutiveCalls(
                    new Article(json_decode('{"id": 2, "name": "VENOM L"}'), false),
                    new Article(json_decode('{"id": 5, "name": "VENOM SKI"}'), false)
                )
            );
        $this->inject($stub, 'objectManager', $objectManager);

        $stub->expects(self::any())->method('getValidEndpoint')->willReturn('http://www.foo.bar/api/');
        $stub->expects(self::any())->method('call')->willReturn($raw);

        $result = $stub->findByTerm($term);

        self::assertInstanceOf(ObjectStorage::class, $result);
        self::assertEquals(2, $result->count());
        self::assertEquals('VENOM L', $result->toArray()[0]->getName());
        self::assertEquals(5, $result->toArray()[1]->getId());
    }
}

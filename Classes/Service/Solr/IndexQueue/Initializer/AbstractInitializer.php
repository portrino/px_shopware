<?php
namespace Portrino\PxShopware\Service\Solr\IndexQueue\Initializer;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Sascha Nowak <sascha.nowak@netlogix.de>, netlogix GmbH & Co. KG
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

use Portrino\PxShopware\Service\Shopware\AbstractShopwareApiClientInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

abstract class AbstractInitializer extends \ApacheSolrForTypo3\Solr\IndexQueue\Initializer\AbstractInitializer
{

    /**
     * @var string
     */
    protected $clientClassName = AbstractShopwareApiClientInterface::class;

    /**
     * @var AbstractShopwareApiClientInterface
     */
    protected $shopwareClient;

    public function __construct()
    {
        $reflection = new \ReflectionClass(self::class);
        if ($reflection->getParentClass() !== null && $reflection->getParentClass()->getConstructor() !== null) {
            parent::__construct();
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->shopwareClient = $objectManager->get($this->clientClassName);
    }

    /**
     * @return array
     */
    protected function getRecordDefaults()
    {
        return [
            'root' => $this->site->getRootPageId(),
            'item_type' => $this->type,
            'item_uid' => 0,
            'indexing_configuration' => $this->indexingConfigurationName,
            'indexing_priority' => $this->getIndexingPriority(),
            'changed' => (new \DateTime())->getTimestamp()
        ];
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
<?php
namespace Portrino\PxShopware\Task;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Thomas Griessbach <griessbach@portrino.de>, portrino GmbH
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
 * Class IndexQueueCommandController
 *
 * @package Portrino\PxShopware\Task
 */
class IndexQueueCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    protected $itemTypes = array(
        \Portrino\PxShopware\Domain\Model\Article::class,
        \Portrino\PxShopware\Domain\Model\Category::class
    );

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager = NULL;

    /**
     * Initialize the controller.
     */
    protected function initializeCommand() {
        // get settings
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxShopware', 'tx_pxshopware');
    }


    /**
     * import shopware items to solr index queue
     *
     * @param int $rootPageId The rootPage for solr indexing configuration
     * @param int $documentsToIndexLimit How many items to load per run
     *
     * @return bool
     */
    public function importCommand($rootPageId = 1, $documentsToIndexLimit = 5) {
        $this->initializeCommand();

        $result = TRUE;

        foreach ($this->itemTypes as $itemType) {
            $className = 'Portrino\\PxShopware\\Service\\Solr\\Queue\\' . (new \ReflectionClass($itemType))->getShortName() . 'IndexQueueInterface';

            if (!interface_exists($className)) {
                throw new \Exception('No IndexQueue Service found for itemType: '. $itemType, 1460118399);
            }

            /** @var \Portrino\PxShopware\Service\Solr\Queue\IndexQueueInterface $indexQueueService */
            $indexQueueService = $this->objectManager->get($className);
            $result = $result && $indexQueueService->populateIndexQueue($rootPageId, $documentsToIndexLimit);
        }

        return $result;
    }


}
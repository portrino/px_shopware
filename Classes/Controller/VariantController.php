<?php
namespace Portrino\PxShopware\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Axel Boeswetter <boeswetter@portrino.de>, portrino GmbH
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

use Portrino\PxShopware\Domain\Model\Variant;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class VariantController
 *
 * @package Portrino\PxShopware\Controller
 */
class VariantController extends AbstractController
{

    /**
     * @var \Portrino\PxShopware\Service\Shopware\VariantClientInterface
     * @inject
     */
    protected $shopwareClient;

    /**
     * @return void
     */
    public function listAction()
    {
        $itemUidList = isset($this->settings['variants']) ?
            GeneralUtility::trimExplode(',', $this->settings['variants']) :
            [];
        $items = new ObjectStorage();
        $cacheTags = [];
        foreach ($itemUidList as $itemUid) {
            /** @var Variant $item */
            if ($item = $this->shopwareClient->findById($itemUid)) {
                $items->attach($item->getArticle());

                $cacheTag = $this->getCacheTagForItem($item);
                if ($cacheTag !== false) {
                    $cacheTags[] = $cacheTag;
                }

                /**
                 * only show one item if isTrialVersion
                 */
                if ($this->isTrialVersion === true) {
                    break;
                }
            }
        }

        $this->getTypeScriptFrontendController()->addCacheTags(array_unique($cacheTags));
        $this->view->assign('items', $items);
    }
}

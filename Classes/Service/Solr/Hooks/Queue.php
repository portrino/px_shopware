<?php
namespace Portrino\PxShopware\Service\Solr\Hooks;

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

use TYPO3\CMS\Core\SingletonInterface;

class Queue implements SingletonInterface
{

    /**
     * @var array
     */
    protected $tables = [
        'Portrino_PxShopware_Domain_Model_Article',
        'Portrino_PxShopware_Domain_Model_Category'
    ];

    /**
     * @param array $params
     */
    public function postProcessFetchRecordsForIndexQueueItem(&$params)
    {
        if (!in_array($params['table'], $this->tables)) {
            return;
        }

        foreach ($params['uids'] as $uid) {
            $params['tableRecords'][$params['table']][$uid] = ['uid' => $uid];
        }
    }
}
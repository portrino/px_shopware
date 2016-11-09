<?php
namespace Portrino\PxShopware\Compatibility6\Backend\Utility;

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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlashMessageUtility
 * @package Portrino\PxShopware\Compatibility6\Backend\Utility
 */
class FlashMessageUtility
{

    /**
     * @param $PA
     * @param $fObj
     *
     * @return mixed
     */
    public function showNotSupportedMessage($PA, $fObj)
    {
        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(FlashMessage::class,
            'SuggestWizard is not supported in TYPO3 version lower than 7.6. <br> Please choose <code>plugin.fetchAllItems</code> options in extension configuration instead.',
            'Not Supported',
            FlashMessage::WARNING,
            false
        );

        return $message->render();

    }
}
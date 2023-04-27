<?php

namespace Portrino\PxShopware\ViewHelpers\Media;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class ExistsViewHelper
 */
class ExistsViewHelper extends AbstractConditionViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'Filename which must exist to trigger f:then rendering', false);
        $this->registerArgument('directory', 'string', 'Directory which must exist to trigger f:then rendering', false);
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        $file = GeneralUtility::getFileAbsFileName($arguments['file']);
        $directory = $arguments['directory'];
        $evaluation = false;
        if (isset($arguments['file']) === true) {
            $evaluation = (boolean)((file_exists($file) === true || file_exists(constant('PATH_site') . $file) === true) && is_file($file) === true);
        } elseif (isset($arguments['directory']) === true) {
            $evaluation = (boolean)(is_dir($directory) === true || is_dir(constant('PATH_site') . $directory) === true);
        }
        return $evaluation;
    }
}

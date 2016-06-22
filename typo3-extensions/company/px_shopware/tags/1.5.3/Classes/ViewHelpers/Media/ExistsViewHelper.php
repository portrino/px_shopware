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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class ExistsViewHelper
 *
 * @package Portrino\PxShopware\ViewHelpers\Media
 */
class ExistsViewHelper extends AbstractConditionViewHelper {

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'Filename which must exist to trigger f:then rendering', FALSE);
        $this->registerArgument('directory', 'string', 'Directory which must exist to trigger f:then rendering', FALSE);
    }

    /**
     * Render method
     *
     * @return string
     */
    public function render() {

        $evaluation = static::evaluateCondition($this->arguments);

        if (FALSE !== $evaluation) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    static protected function evaluateCondition($arguments = NULL) {
        $file = GeneralUtility::getFileAbsFileName($arguments['file']);
        $directory = $arguments['directory'];
        $evaluation = FALSE;
        if (TRUE === isset($arguments['file'])) {
            $evaluation = (boolean) ((TRUE === file_exists($file) || TRUE === file_exists(constant('PATH_site') . $file)) && TRUE === is_file($file));
        } elseif (TRUE === isset($arguments['directory'])) {
            $evaluation = (boolean) (TRUE === is_dir($directory) || TRUE === is_dir(constant('PATH_site') . $directory));
        }
        return $evaluation;
    }

}

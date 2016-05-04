<?php
namespace Portrino\PxShopware\Backend\Hooks;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class WizardItems
 *
 * Class/Function which manipulates the rendering of items within the new content element wizard
 *
 * @package Portrino\PxShopware\Backend\Hooks
 */
class WizardItems implements NewContentElementWizardHookInterface {

    /**
     * Processes the items of the new content element wizard
     * and inserts necessary default values for items created within a grid
     *
     * @param array $wizardItems : The array containing the current status of the wizard item list before rendering
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject : The parent object that triggered this hook
     *
     * @return void
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject) {
        $extKey = 'px_shopware';

        $wizardItems['px_shopware'] = array();

        // set header label
        $wizardItems['px_shopware']['header'] = $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xml:tx_pxshopware_wizard_header');

        $pluginSignatures = array(
            0 => str_replace('_', '', $extKey) . '_pi1',
            1 => str_replace('_', '', $extKey) . '_pi2'
        );

        foreach ($pluginSignatures as $pluginSignature) {
            if (!GeneralUtility::inList($GLOBALS['BE_USER']->groupData['explicit_allowdeny'],'tt_content:CType:' . $pluginSignature . ':DENY')) {
                $wizardItems[$pluginSignature] = array(
                    'title' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xml:tt_content.CType.' . $pluginSignature . '.title'),
                    'iconIdentifier' => str_replace('_', '-', $pluginSignature),
                    'params' => '&defVals[tt_content][CType]='. $pluginSignature,
                    'description' => $this->getLanguageService()->sL('LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xml:tt_content.CType.' . $pluginSignature . '.description'),
                    'tt_content_defValues' => array(
                        'CType' => $pluginSignature
                    )
                );
            }
        }
    }

    /**
     * @return LanguageService
     */
    public function getLanguageService() {
        return $GLOBALS['LANG'];
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
}

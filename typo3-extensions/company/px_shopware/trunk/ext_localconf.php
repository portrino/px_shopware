<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Portrino.' . $_EXTKEY,
        'Pi1',
        array(
            'Article' => 'list',
        ),
        // non-cacheable actions
        array(
            'Article' => 'list',
        )
    );

    /** @var array $version */
    $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(TYPO3_version);

    /**
     * For TYPO3 Versions newer than 7.2.x
     */
    if ($version['version_main'] >= 7) {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

        $iconRegistry->registerIcon(
            'px-shopware-tx-pxshopware-domain-model-item',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            array(
                'source' => 'EXT:' . $_EXTKEY . '/ext_icon.svg'
            )
        );
    }

};

$boot($_EXTKEY);
unset($boot);
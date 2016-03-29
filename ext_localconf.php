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

    /**
     * create one cache for each endpoint
     */
    $endpoints = array('articles', 'categories', 'media');
    foreach ($endpoints as $endpoint) {
        if (FALSE === is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_' . $endpoint])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['px_shopware_' . $endpoint] = array(
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\StringFrontend::class,
                'options' => array(
                    'defaultLifetime' => 3600 // 1 hour cache lifetime
                ),
                'groups' => array('pages', 'all')
            );
        }
    }

};

$boot($_EXTKEY);
unset($boot);
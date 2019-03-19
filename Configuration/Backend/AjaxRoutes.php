<?php

/**
 * Definitions for routes provided by EXT:px_shopware
 * Contains all AJAX-based routes for entry points
 *
 * Currently the "access" property is only used so no token creation + validation is made
 * but will be extended further.
 */
return [
    // Clear Cache
    'tx_pxshopware::clearCache' => [
        'path' => 'tx_pxshopware::clearCache',
        'target' => Portrino\PxShopware\Backend\Hooks\Ajax::class . '::clearCache'
    ],
    // Search Shopware Items
    'tx_pxshopware::searchAction' => [
        'path' => 'tx_pxshopware::searchAction',
        'target' => Portrino\PxShopware\Backend\FormEngine\FieldControl\SuggestWizardControl::class . '::searchAction'
    ]
];

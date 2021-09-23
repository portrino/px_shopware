<?php

return [
    'px_shopware_clear_cache' => [
        'path' => '/px-shopware/clear-cache',
        'target' => Portrino\PxShopware\Backend\Hooks\Ajax::class . '::clearCache'
    ],
];

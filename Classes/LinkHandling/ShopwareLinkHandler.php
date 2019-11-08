<?php

namespace Portrino\PxShopware\LinkHandling;

use TYPO3\CMS\Core\LinkHandling\LinkHandlingInterface;

/**
 * Class ShopwareLinkHandler
 * @package Portrino\PxShopware\LinkHandling
 * @author Christian Hellmund <ch@marketing-factory.de>
 */
class ShopwareLinkHandler implements LinkHandlingInterface
{
    /**
     * @var string
     */
    protected $type;

    public function asString(array $parameters): string
    {
        $urn = 't3://shopware';

        if (!empty($parameters['article'])) {
            $urn .= '?article=' . $parameters['article'];
        } else if (!empty($parameters['category'])) {
            $urn .= '?category=' . $parameters['category'];
        }

        return $urn;
    }

    public function resolveHandlerData(array $data): array
    {
        $result = [];

        if (isset($data['article'])) {
            $result['article'] = $data['article'];
        } else if (isset($data['category'])) {
            $result['category'] = $data['category'];
        }

        return $result;
    }
}
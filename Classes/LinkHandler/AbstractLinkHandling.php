<?php

namespace Portrino\PxShopware\LinkHandler;

use TYPO3\CMS\Core\LinkHandling\LinkHandlingInterface;

class AbstractLinkHandling implements LinkHandlingInterface
{
    protected const BASE_URN = 't3://shopware_';

    protected const TYPE = '';

    /**
     * @inheritDoc
     */
    public function asString(array $parameters): string
    {
        return static::BASE_URN . static::TYPE . '?id=' . (int)$parameters[static::TYPE];
    }

    /**
     * @inheritDoc
     */
    public function resolveHandlerData(array $data): array
    {
        return [
            static::TYPE => (int)$data['id'],
        ];
    }
}

<?php

namespace Portrino\PxShopware\LinkHandler;

use Portrino\PxShopware\Domain\Model\Article;
use Portrino\PxShopware\Domain\Model\Category;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;
use TYPO3\CMS\Frontend\Typolink\LinkResult;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class AbstractLinkBuilder extends AbstractTypolinkBuilder
{
    protected const TYPE = '';

    public function build(array &$linkDetails, string $linkText, string $target, array $conf)
    {
        $id = (int)$linkDetails[static::TYPE];
        if ($id < 1) {
            throw new UnableToLinkException(
                '"' . $id . '" is not a valid shopware ' . static::TYPE . ' id.',
                // Use the Unix timestamp of the time of creation of this message
                1682511897,
                null,
                $linkText
            );
        }
        $client = GeneralUtility::makeInstance(
            'Portrino\\PxShopware\\Service\\Shopware\\' . ucfirst(static::TYPE) . 'Client'
        );
        /** @var Article|Category $object */
        $object = $client->findById($id);

        return (new LinkResult(static::TYPE, $object->getUri()))
            ->withTarget('_blank')
            ->withLinkConfiguration($conf)
            ->withLinkText($linkText)
            ->withAttribute('title', $object->getName());
    }
}

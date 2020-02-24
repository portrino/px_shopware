<?php

namespace Portrino\PxShopware\LinkHandling;

use Portrino\PxShopware\LinkResolver\ArticleLinkResolver;
use Portrino\PxShopware\LinkResolver\CategoryLinkResolver;
use Portrino\PxShopware\Service\Shopware\ArticleClientInterface;
use Portrino\PxShopware\Service\Shopware\CategoryClientInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;

/**
 * Class ShopwareLinkBuilder
 * @package Portrino\PxShopware\LinkHandling
 * @author Christian Hellmund <ch@marketing-factory.de>
 */
class ShopwareLinkBuilder extends AbstractTypolinkBuilder
{
    public function __construct(ContentObjectRenderer $contentObjectRenderer, TypoScriptFrontendController $typoScriptFrontendController = null)
    {
        parent::__construct($contentObjectRenderer, $typoScriptFrontendController);


    }

    /**
     * @param array $linkDetails
     * @param string $linkText
     * @param string $target
     * @param array $conf
     * @return array
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $client = null;
        $objectId = null;
        if (isset($linkDetails['article'])) {
            /** @var ArticleClientInterface $client */
            $client = $objectManager->get(ArticleClientInterface::class);
            $objectId = $linkDetails['article'];
        } else if (isset($linkDetails['category'])) {
            /** @var CategoryClientInterface $client */
            $client = $objectManager->get(CategoryClientInterface::class);
            $objectId = $linkDetails['category'];
        }

        if ($client && $objectId) {
            /** @var \Portrino\PxShopware\Domain\Model\ShopwareModelInterface $object */
            $object = $client->findById((int)$objectId);
            if ($object && method_exists($object, 'getUri') && method_exists($object, 'getName')) {
                return [(string)$object->getUri(), $linkText, $target];
            }
        }

        return ['', $linkText, $target];
    }
}
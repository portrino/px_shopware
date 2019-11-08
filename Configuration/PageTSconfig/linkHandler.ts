TCEMAIN.linkHandler.shopware_category {
    handler = Portrino\PxShopware\Recordlist\LinkHandler\CategoryLinkHandler
    label = LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:link_handler.category
    displayAfter = page
    scanAfter = page
    configuration {
    }
}

TCEMAIN.linkHandler.shopware_article {
    handler = Portrino\PxShopware\Recordlist\LinkHandler\ArticleLinkHandler
    label = LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:link_handler.article
    displayAfter = page
    scanAfter = page
    configuration {
    }
}
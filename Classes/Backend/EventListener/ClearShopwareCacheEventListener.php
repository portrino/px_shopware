<?php

namespace Portrino\PxShopware\Backend\EventListener;

use TYPO3\CMS\Backend\Backend\Event\ModifyClearCacheActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ClearShopwareCacheEventListener
{
    public function __invoke(ModifyClearCacheActionsEvent $event): void
    {
        if ($this->getBackendUser()->isAdmin()) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            $event->addCacheAction([
                'id' => 'px_shopware',
                'title' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:clear_cache_menu.title',
                'description' => 'LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:clear_cache_menu.description',
                'href' => (string)$uriBuilder->buildUriFromRoute('px_shopware_clear_cache'),
                'iconIdentifier' => 'px-shopware-clear-cache',
            ]);
        }
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns the icon for the cache menu, depending on the TYPO3 version
     *
     * @return string
     */
    protected function getIcon(): string
    {
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon('px-shopware-clear-cache', Icon::SIZE_SMALL)->render();
    }
}

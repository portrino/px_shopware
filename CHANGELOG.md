# PxShopware Changelog

3.1.0 - 2018-01-18
------------------
* [TASK] updates dependencies in composer.json and ext_emconf.php for TYPO3 8.7
* [TASK] updates license in composer.json for packagist compatibility
* [TASK] updates Cache/CacheChain.php with new method for TYPO3 8.7

3.0.5 - 2017-06-21
------------------
* [BUGFIX] removes hard-coded field name in ItemsProcFunc->getItemsSelected() 

3.0.4 - 2017-05-08
------------------
* [BUGFIX] fix BE suggest after T3 core update to 7.6.18

3.0.3 - 2017-01-25
------------------
* [BUGFIX] remove deleted item from IndexQueue AND core index, to prevent dead entries 

3.0.2 - 2017-01-24
------------------
* [BUGFIX] do not throw exceptions in TYPO3_MODE "FE"

3.0.1 - 2017-01-24
------------------
* [BUGFIX] updates AbstractShopwareApiClient->findAll to allow more than 1000 items (shopware api default limit)
* [BUGFIX] change EAN field from _stringS to _textS

3.0.0 - 2016-11-09
------------------
**TYPO3 6.2 Compatibility Release**

* [TASK] Add compatibility6 layer for TYPO3 6.2 legacy support
* [TASK] Add compatibility for tt_content plugin / contentElement
* [TASK] Add custom page_ts for TYPO3 6.2 compatibility of newContentElement wizardItems registration
* [TASK] Transform svg to png images for some icons for 6.2 compatibility
* [TASK] Display warning if `SuggestWizard` was used under TYPO3 < 7.6
* [TASK] Add override if Category domain model for TYPO3 6.2 compatibility
* [TASK] Add override of /PageLayoutViewDraw Hook class for TYPO3 6.2 compatibility
* [TASK] Add registerAjaxHandler to ext_localconf.php for TYPO3 6.2 compatibility
* [TASK] Add clear_cache.png for TYPO3 6.2 compatibility
* [TASK] Change dep for TYPO3 6.2 also in composer.json

**Refactoring / Improvements / Bugfixes**
* [FEATURE] Add PageLayoutViewDraw also for category plugin
* [TASK] Add page_ts which adds the header to the newContentElement wizard
* [TASK] In conjunction we add Pi1.html and Pi2.html into PageLayoutViewDrawItem folder for better SoC
* [TASK] Add PxShopware.xsd for better viewhelper IDE integration
* [TASK] Remove `WizardItems` class which registers newContentElement wizardItems via php, instead we do this 
   completely via page_ts
* [BUGFIX] Uses instance of instead of get_class comparison to determine the relevant cacheTag in 
  `AbstractController`
* [BUGFIX] Add `if (ExtensionManagementUtility::isLoaded('solr') === true)` to `NotificationController` to prevent problems for users which are using 
   px_shopware without `EXT:solr`
* [TASK] Renames `Article`- and `CategoryIntializer` 
* [BUGFIX] Adds condition to `AbstractIntializer` to prevent exception if parent class has no `__construct()` method
* [BUGFIX] Adds check for table to prevent sql exception if table not exists 
* [BUGFIX] Adds condition to prevent exception when not using TransientMemoryCache
* [BUGFIX] Removes `$this->articleClient->findById(...)` from `NotificationController` to prevent filling cache with not 
   updated data from rest api, because the postUpdate, postPersist and preRemove events which we are using in the TYPO3Connector
   are triggered before the real persist takes place
* [TASK] adds multiple shopware fields to solr index: EAN, additionalText, descriptionLong
* [TASK] adds descriptionLong and details (ObjectStorage of model Detail)to article model and solr index
* [TASK] adds $additionalText to Detail model
   
2.0.0 - 2016-09-22
------------------
* [FEATURE] Cleanup solr indexing
* [FEATURE] Add cachetags for api request
* [FEATURE] Add cache tags for articles and categories in list action
* [TASK] Add service to get settings
* [FEATURE] Add notification interface for shopware
* [TASK] Replace setting with hook
* [FEATURE] Add link handler for products and categories
* [BUGFIX] Fix typoscript setting for cache lifetime
* [BUGFIX] Review feedback
* Merge pull request #1 from netlogix/develop
* [BUGFIX] Fix message for missing configuration
* [BUGFIX] Fix configuration overlay
* [FEATURE] Add feature to retrieve orderNumber directly from raw response improve performance
* [TASK] Fix PSR1 / PSR2 codestyle
* [TASK] Fix traditional array syntax and replaces it with shorthand syntax
* [BUGFIX] Fix missing pxShopwareUrl in article model

1.5.5 - 2016-08-22
------------------
* fixes a bug which results in PHP-Warning if caching is disabled
* fixes #77584 
* https://forge.typo3.org/issues/77584

1.5.4 - 2016-06-24
------------------
* fixes a bug in `Pi1PageLayoutViewDraw` which leads to exception in backend if no template or not existent template 
  was selected

1.5.3 - 2016-06-22
------------------
* adds rowDescription to content elements for px_shopware 
* fixes multi-language support for categories

1.5.2 - 2016-06-21
------------------
* bugfix release
* fixes multi-language support for solr indexing of articles

1.5.1 - 2016-06-21
------------------
* bugfix release
* prevents `Pi1PageLayoutViewDraw` from default drawing procedures

1.5.0 - 2016-06-17
------------------
* complete refactoring of localization features
* adds `Pi1PageLayoutViewDraw` for previewing of px_showare plugin configuration

1.4.1 - 2016-06-17
------------------
* adds `LocaleToShopMappingService` which replaces `LocaleMappingService` because we need to map the locale to the 
  related shop
  * e.g.: "de_DE -> 1" or "it_IT -> 3"
  * this means if the TYPO3 language is german(de_DE) the API-Call will use ?language=1 and when the language is italian
    (it_IT) the API-Call will be appended with ?language=3 to get the correct translation of an article for example
* this changes could be reverted if the bug described here: https://issues.shopware.com/#/issues/SW-15388 is fixed   

1.4.0 - 2016-06-15
------------------
* enables solr indexing for shopware articles
  * adds `IndexQueueCommandController` to populate Solr IndexQueue
  * adds different `SolrIndexer` to index specific shopware resources
      
1.3.1 - 2016-06-14
------------------
* fixes performance problem in SuggestWizard
    * we do not display breadcrumb path anymore, because this leads to many requests during suggest call

1.3.0 - 2016-05-06
------------------
* adds possibility to set specific template for plugins via typoscript or flexform
    * new templates could be added or removed easily via pageTS
    * Example:
    <pre>
    <code class="typoscript">
        TCEFORM.tt_content.pi_flexform.pxshopware_pi1.sDEF.settings\.template {
        
            removeItems = default
        
            addItems {
                teaser = LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tt_content.pi_flexform.pxshopware_pi1.settings.template.teaser
                slider = LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tt_content.pi_flexform.pxshopware_pi1.settings.template.slider
            }
            
        }
    </code>
    </pre>
    * more configurations are described here: https://docs.typo3.org/typo3cms/TSconfigReference/PageTsconfig/TCEform/Index.html

* removes `$detailedResult = $detailedResult = $shopwareApiClient->findById($result->getSuggestId());` call from 
  `SuggestWizard` to prevent calling the API too often (uncached) which in turn means high response times for autosuggest

1.2.0 - 2016-05-04
------------------
* enables localization feature
  * if we are TYPO3 frontend on every API request the current selected sys_language will be transmitted to shopware
    and the correct translation will be returned from your shopware instance

1.1.0 - 2016-05-02
------------------
* removes `findById()` from `getSubCategories()` method in category model to prevent many requests when building menu
* moves breadcrumb menu generation from `initializeObject()` to `getBreadCrumbPath()`
* adds cache chain feature which adds a transient memory cache before typo3 db cache which should improve performance
* adds phpunit tests for cacheChain class

0.0.1 - 2016-03-23
------------------
* initial extension structure
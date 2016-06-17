# PxShopware Change log

2.0.0 - 2016-06-17
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
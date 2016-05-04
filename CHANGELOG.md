# PxShopware Change log

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
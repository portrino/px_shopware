# px_shopware 3.1.0 (stable)
[![Latest Stable Version](https://poser.pugx.org/portrino/px_shopware/v/stable)](https://packagist.org/packages/portrino/px_shopware)
[![Total Downloads](https://poser.pugx.org/portrino/px_shopware/downloads)](https://packagist.org/packages/portrino/px_shopware)

Shopware Integration for TYPO3

This extension connects [Shopware](https://www.shopware.com "Shopware") with [TYPO3 CMS](https://typo3.org/ "TYPO3"). 
It retrieves data from shopware [REST API](https://developers.shopware.com/developers-guide/rest-api/ "Shopware REST API") 
and provides many frontend plugins to show or list shopware data like articles, categories, etc on your TYPO3 website.

The extension was build from scratch and is based on a clean extensible architecture. Extbase and Fluid were used for
the implementation of the frontend plugins. Shopware data will be cached via TYPO3`s caching framework to prevent multiple
uneccessary API-Calls and also boost up the frontend rendering.

To get access to all features you have to install our shopware plugin "TYPO3-Connector" on your shop instance. Get it [here](https://github.com/portrino/shopware-typo3-connector) for free. Otherwise your extension some features are not available 
or limited.

The complete documentation could be found [here](https://docs.typo3.org/typo3cms/extensions/px_shopware/ "PxShopware Documentation")

## Authors

![](https://avatars0.githubusercontent.com/u/726519?s=40&v=4)

* **André Wuttig** - *Initial work* - [aWuttig](https://github.com/aWuttig)
* **Thomas Griessbach** - *Solr Integration* - [tgriessbach](https://github.com/tgriessbach)
* **Sascha Nowak** - *Notification Feature, Cache Invalidation, Bugfixes* - [nlx-sascha](https://github.com/nlx-sascha)

See also the list of [contributors](https://github.com/portrino/px_shopware/graphs/contributors) who participated in this project.

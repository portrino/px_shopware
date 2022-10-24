# TYPO3 extension `px_shopware`

[![Latest Stable Version](https://poser.pugx.org/portrino/px_shopware/v/stable)](https://packagist.org/packages/portrino/px_shopware)
[![TYPO3 10](https://img.shields.io/badge/TYPO3-10-orange.svg)](https://get.typo3.org/version/10)
[![TYPO3 9](https://img.shields.io/badge/TYPO3-9-orange.svg)](https://get.typo3.org/version/9)
[![Total Downloads](https://poser.pugx.org/portrino/px_shopware/downloads)](https://packagist.org/packages/portrino/px_shopware)
[![Monthly Downloads](https://poser.pugx.org/portrino/px_shopware/d/monthly)](https://packagist.org/packages/portrino/px_shopware)

Shopware Integration for TYPO3

This extension connects [Shopware 5.x](https://www.shopware.com "Shopware") with [TYPO3 CMS](https://typo3.org/ "TYPO3"). 
It retrieves data from the Shopware [REST API](https://developers.shopware.com/developers-guide/rest-api/ "Shopware REST API") 
and provides many frontend plugins to show or list Shopware data like articles, categories, etc. on your TYPO3 website.

The extension was build from scratch and is based on a clean extensible architecture. Extbase and Fluid were used for
the implementation of the frontend plugins. The Shopware data will be cached via TYPO3`s caching framework to prevent multiple
unnecessary API-Calls and also boost up the frontend rendering.

To get access to all features you have to install our Shopware plugin ["TYPO3-Connector"](https://github.com/portrino/shopware-typo3-connector "TYPO3-Connector") 
on your shop instance. Get it [here](https://github.com/portrino/shopware-typo3-connector) for free. Otherwise, your 
some extension features are not available or limited.


|                  | URL                                                           |
|------------------|---------------------------------------------------------------|
| **Repository:**  | https://github.com/portrino/px_shopware                       |
| **Read online:** | https://docs.typo3.org/typo3cms/extensions/px_shopware/3.2.2/ |
| **TER:**         | https://extensions.typo3.org/extension/px_shopware/           |

## Compatibility

| News       | TYPO3     | PHP       | Support / Development       |
|------------|-----------|-----------|-----------------------------|
| 5.x        | 9.5 - 10  | 7.2 - 7.4 | bugfixes, security updates  |
| 4.x        | 8.7 - 9.5 | 7.0 - 7.2 | none                        |
| 3.x        | 6.2 - 8.7 | 5.6 - 7.2 | none                        |
| 2.x        | 7.x       | 5.6 - 7.2 | none                        |
| 1.x        | 7.x       | 5.5 - 5.6 | none                        |

## Authors

* **André Wuttig** - *Initial work* - [aWuttig](https://github.com/aWuttig)
* **Axel Böswetter** - *Upgrades* - [EvilBMP](https://github.com/EvilBMP)
* **Thomas Griessbach** - *Solr Integration* - [tgriessbach](https://github.com/tgriessbach)
* **Sascha Nowak** - *Notification Feature, Cache Invalidation, Bugfixes* - [nlx-sascha](https://github.com/nlx-sascha)

See also the list of [contributors](https://github.com/portrino/px_shopware/graphs/contributors) who participated in this project.

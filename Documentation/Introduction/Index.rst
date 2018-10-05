.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


Introduction to Shopware connector
==================================

This extension connects the shopware shopsystem with TYPO3 CMS. It retrieves data from shopware REST-API and provides
many frontend plugins to show or list shopware data like articles, categories, etc on your TYPO3 website.

The extension was build from scratch and is based on a clean extensible architecture. Extbase and Fluid were used for
the implementation of the frontend plugins. Shopware data will be cached via TYPO3`s caching framework to prevent multiple
uneccessary API-Calls and also boost up the frontend rendering.

.. figure:: ../Images/shopware-add-products-and-categories.png
   :alt: Shopware-Plugins: add Articel und Categories
   :width: 500px

    PxShopware provides new content elements to show articles, categories etc. from shopware

.. important::

    The px_shopware TYPO3 extension needs the Shopware plugin "TYPO3-Connector" in your shop system. Download the plugin here_ from our github reposititory.

**Feature List**

* list and show articles and categories
* link articles and categories (only with shopware plugin "TYPO3-Connector" possible)
* backend toolbar to show the current state of the shopware connection
* autosuggest wizard to search shopware articles and categories from TYPO3-Backend
* caching of API requests based on TYPO3 caching framework
* TYPO3 menu rendering from shopware category tree
* solr-indexer to index and display shopware articles and categories with typo3-solr
* automatic invalidation of cache entries after modification of articles or categories on SW side
* automatic update of solr index after modification of articles or categories on SW side

**Coming-Soon**

* list and show more shopware resources like manufacturers
* adding more unit tests to increase quality

.. _shopware: https://www.shopware.com/
.. _REST-API: https://developers.shopware.com/developers-guide/rest-api/
.. _here: https://github.com/portrino/shopware-typo3-connector
.. _typo3-solr: https://typo3.org/extensions/repository/view/solr
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

    To get access to all features you have to install our shopware plugin "TYPO3-Connector"
    on your shop instance. Buy and download it from store here_. Otherwise your extension will work as a trial version and
    some features are not available or limited.

**Feature List**

* list and show articles and categories
* link articles and categories (only with shopware plugin "TYPO3-Connector" possible)
* backend toolbar to show the current state of the shopware connection
* autosuggest wizard to search shopware articles and categories from TYPO3-Backend
* caching of API requests based on TYPO3 caching framework
* TYPO3 menu rendering from shopware category tree
* solr-indexer to index and display shopware articles and categories with typo3-solr

**Coming-Soon**

* list and show more shopware resources like manufacturers
* adding more unit tests to increase quality

.. _shopware: https://www.shopware.com/
.. _REST-API: https://developers.shopware.com/developers-guide/rest-api/
.. _here: https://store.shopware.com/en/port116496663052/typo3-connector.html
.. _typo3-solr: https://typo3.org/extensions/repository/view/solr
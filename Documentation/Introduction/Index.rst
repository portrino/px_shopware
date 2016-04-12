.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


Introduction
============

This extension connects the shopware_ shopsystem with TYPO3 CMS. It retrieves data from shopware REST-API_ and provides
many frontend plugins to show or list shopware data like articles, categories, etc on your TYPO3 website.

To get access to all features you have to install our shopware plugin "portrino TYPO3-Connector"
on your shop instance. Buy and download it from store here_. Otherwise your extension will work as a trial version and
some features are not available or limited.

The extension was build from scratch and is based on a clean extensible architecture. Extbase and Fluid were used for
the implementation of the frontend plugins. Shopware data will be cached via TYPO3`s caching framework to prevent multiple
uneccessary API-Calls and also boost up the frontend rendering.

.. figure:: ../Images/shopware-add-products-and-categories.png
    :width: 500px
    :alt: Shopware-Plugins: Artikel und Kategorien hinzufügen

    PxShopware provides new content elements to show articles, categories etc. from shopware

**Feature List**

* list and show articles and categories
* link articles and categories (only with shopware plugin "portrino TYPO3-Connector")
* backend toolbar to show the current state of the shopware connection
* autosuggest wizard to search shopware articles and categories from TYPO3-Backend
* caching of API requests with TYPO3 caching framework
* TYPO3 menu rendering from shopware categories

**Coming-Soon**

* solr-indexer to index and display shopware articles and categories with typo3-solr_
* list and show more shopware resources like manufacturers

.. _shopware: https://www.shopware.com/
.. _REST-API: https://developers.shopware.com/developers-guide/rest-api/
.. _here: https://shop.shopware.com/
.. _typo3-solr: https://typo3.org/extensions/repository/view/solr
.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


Introduction
============

This extension connects the shopware_ shopsystem with TYPO3 CMS. It retrieves data from shopware REST-API_ and provides
many frontend plugins to show or list shopware data like articles, categories, etc on your TYPO3 website.

The extension was build from scratch and is based on a clean extensible architecture. Extbase / Fluid were used for
implementation of frontend plugins. Shopware data will be cached via TYPO3`s caching framework to prevent multiple
uneccessary API-Calls and also boost up the frontend rendering.

To make this extension work with your shopware system, you have to install our shopware plugin "portrino TYPO3-Connector"
on your shop instance. Buy and download it from store here_.

.. _shopware: https://www.shopware.com/
.. _REST-API: https://developers.shopware.com/developers-guide/rest-api/
.. _here: https://shop.shopware.com/

.. figure:: ../Images/shopware-add-products-and-categories.png
    :width: 500px
    :alt: Shopware Content Elements

    PxShopware provides new content elements to show articles, categories etc. from shopware

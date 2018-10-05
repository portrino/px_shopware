.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _for-administrators:

For Users
=========

Show Article(s)
---------------

First of all you have to add a new content element of type "Article(s)".

.. figure:: ../Images/ForUsers/px_shopware_add-plugin.png
   :alt: Shopware-Plugins: Artikel und Kategorien
   :width: 500px

    TYPO3 frontend plugins to list and show shopware articles and categories

After that you are able to choose the articles you want to display in frontend.

.. figure:: ../Images/ForUsers/px_shopware_add-shopware-articles-fetch-all-items.png
   :alt: Choose article from list of all articles
   :width: 800px

    Choose article from list of all articles available in shop

.. figure:: ../Images/ForUsers/px_shopware_add-shopware-articles-autocomplete-wizard.png
   :width: 800px
   :alt: Choose article via autocomplete wizard

    Choose article via autocomplete wizard (default behaviour)

.. note::

    The default behaviour is to choose articles and categories via autocomplete-wizard. If you wish to change this and
    list all available articles and categories in your frontend plugin configuration you have to configure it in your
    extension configuration in extension manager.

.. important::

    Please note that you to override the article partial for an optimized frontend output (:ref:`typoscript-configuration`).

.. note::

    With the expression ``{article.url}`` you can link articles within your fluid template. This feature is only
    available in the full version.

Show Categories(s)
------------------

Similiar to articles you can show categories in the frontend by adding a content element of type "Category(s)".

.. figure:: ../Images/ForUsers/px_shopware_add-shopware-categories-fetch-all-items.png
   :width: 500px
   :alt: Choose category from list of all categories

    Choose category from list of all categories

.. figure:: ../Images/ForUsers/px_shopware_add-shopware-categories-autocomplete-wizard.png
   :width: 800px
   :alt: Choose category via autocomplete wizard

    Choose category via autocomplete wizard (default behaviour)

.. note::

    With the expression ``{category.subCategories}`` you can render a menu structure based on shopware categories within
    your fluid template.

.. note::

    With ``{category.url}`` you can link categories within your fluid template. This feature is only
    available in the full version.
﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt



.. _typoscript-configuration:

Konfiguration im TypoScript
^^^^^^^^^^^^^^^^^^^^^^^^^^^

TypoScript ins Root-Template einbinden
--------------------------------------

Bitte fügen Sie zunächst px_shopware zu Ihrem Root Template hinzu.

Dazu in der Listenansicht auf der Root-Page den Default-Template-Record auswählen und im Reiter "Includes" "Shopware Integration (px_shopware)"
zu den "Include static (from extensions)" hinzufügen

.. figure:: ../../../Images/ForAdministrators/px_shopware_ts-root-template-integration.png
   :width: 800px
   :align: left
   :alt: px_shopware ins Root Template integrieren

| Um px_shopware direkt in Ihr Template Setup zu integrieren nutzen Sie bitte folgenden Code:
| ``<INCLUDE_TYPOSCRIPT: source="FILE:EXT:px_shopware/Configuration/TypoScript/setup.txt">``
| ``<INCLUDE_TYPOSCRIPT: source="FILE:EXT:px_shopware/Configuration/TypoScript/constants.txt">``


Sofern das TypoScript nicht richtig konfiguriert wurde, erscheint in der px_shopware-Topbar folgende Fehlermeldung:

.. figure:: ../../../Images/ForAdministrators/px_shopware_ts-error.png
   :width: 300px
   :align: left
   :alt: Warning wenn TypoScript Konfiguration fehlt


TypoScript Werte
----------------

Die folgenden Werte können in Ihrem TypoScript definiert werden.


======================================  ==========  ===============================================================================================================================  ====================================================
TypoScript value                        Data type   Description                                                                                                                      Default
======================================  ==========  ===============================================================================================================================  ====================================================
settings.api.url                        string      Die URL Ihrer Shopware-API (z.B. http://www.my-online-shop.com/api/)
settings.api.username                   string      Der Shopware API-Nutzer
settings.api.key                        string      Der API-Key des API-Nutzers
settings.cacheLifeTime                  int         Die Lebensdauer des Caches in Sekunden                                                                                                     3600
settings.noImage.path                   string      Pfad zum Default-Bild (Wenn kein Bild dem Artikel übergeben wird, dann wird ein Default-Bild angezeigt.)                         EXT:px_shopware/Resources/Public/Images/
settings.noImage.filename               string      Name des Default-Bildes                                                                                                          no_image_available.jpg
view.templateRootPaths                  array       Wird genutzt um verschiedene Pfade für Templates zu konfigurieren. Diese werden in umgekehrter Reihenfolge überschrieben.        0 = EXT:px_shopware/Resources/Private/Templates/
view.partialRootPaths                   array       Wird genutzt um verschiedene Pfade für Partials zu konfigurieren. Diese werden in umgekehrter Reihenfolge überschrieben.         0 = EXT:px_shopware/Resources/Private/Partials/
view.layoutRootPaths                    array       Wird genutzt um verschiedene Pfade für Layouts zu konfigurieren. Diese werden in umgekehrter Reihenfolge überschrieben.          0 = EXT:px_shopware/Resources/Private/Layouts/
======================================  ==========  ===============================================================================================================================  ====================================================

.. important::

   Im der TYPO3-Toolbar wird entweder die API-Konfiguration aus dem Extension Manager oder die von der Rootseite gesehen
   erste im TypoScript gefundene API-Konfiguration genutzt. Insofern sie in Ihrem TYPO3 CMS mehere Shop-Systeme anbinden
   wollen ist somit nicht immer im Backend (Toolbar) ersichtlich ob alle Shops verbunden sind.

Beispiele
---------

**Partial für Artikel überschreiben**

::

    plugin.tx_pxshopware {
        view {
            partialRootPaths {
                20 = EXT:foo_bar/Resources/Private/Partials/
            }
        }
    }


foo_bar/Resources/Private/Partials/Article/Item.html

::

   <f:if condition="{article.url}">
        <f:then>
            <a href="{article.url}">
                <f:render section="article" arguments="{_all}" />
            </a>
        </f:then>
        <f:else>
            <f:render section="article" arguments="{_all}" />
        </f:else>
   </f:if>

   <f:section name="article">
       <div class="article item">
           <figure>
               <f:render section="image" arguments="{_all}" />
               <figcaption>
                   <f:if condition="{article.name}">
                       <div class="title">{article.name}</div>
                   </f:if>
                   <f:if condition="{article.description}">
                       <div class="description">{article.description -> f:format.raw()}</div>
                   </f:if>
               </figcaption>
           </figure>
       </div>
   </f:section>

   <f:section name="image">
       <f:if condition="{article.firstImage}">
           <f:then>
               <img src="{article.firstImage.url}" width="200" height="200" title="{article.name}" alt="{article.name}" >
           </f:then>
           <f:else>
               <f:alias map="{height: 200, width: 200}">
                   <f:render partial="NoImage" arguments="{_all}" />
               </f:alias>
           </f:else>
       </f:if>
   </f:section>


**Partial für Kategorien überschreiben**

foo_bar/Resources/Private/Partials/Category/Item.html

::

    <f:if condition="{category.url}">
        <f:then>
            <a href="{category.url}">
                <f:render section="category" arguments="{_all}" />
            </a>
        </f:then>
        <f:else>
            <f:render section="category" arguments="{_all}" />
        </f:else>
    </f:if>

    <f:section name="category">
        <div class="category item">
            {category.name}

            <f:comment>
                <!-- use this for each loop to iterate through the sub categories -->
                <f:for each="{category.subCategories}" as="subCategory"></f:for>
            </f:comment>

        </div>
    </f:section>
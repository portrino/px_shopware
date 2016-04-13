.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Einleitung
==========

Die Extension verbindet das Shopware-Shopsystem mit dem TYPO3 CMS. Sie erhält ihre Daten von der Shopware REST-API und
bietet verschiedene Frontend-Plugins um Shopware-Daten wie Artikel, Kategorien, etc. als Listen oder Detail-Ansichten
anzuzeigen.

Um alle Möglichkeiten der Erweiterung zu nutzen raten wir Ihnen unser Shopware-Plugin "TYPO3-Connector" in
Ihrer Shopware-Instanz zu installieren. Sie können es hier_ aus dem Shopware Store erwerben.

.. figure:: ../../Images/shopware-add-products-and-categories.png
   :width: 500px
   :alt: Shopware-Plugins: Artikel und Kategorien hinzufügen

   Die PxShopware Extension bietet neue Content-Elemente um Artikel, Kategorien, etc. aus Shopware einzubinden und
   anzuzeigen.

Die Extension kann nach Installation als Trial- oder Vollversion benutzt werden.

.. important::

    Damit die Extension optimal mit Ihrem Shopware-System zusammen arbeitet, sollten Sie das Plugin "TYPO3-Connector"
    in Ihrer Shop-Instanz einbinden. Sie können das TYPO3-Plugin_ im Shopware-Store erwerben.

Die Extension wurde von Grund auf neu implementiert und basiert auf einer variablen und erweiterbaren Architektur.
Die Frontend-Plugins wurden mit Extbase und Fluid erstellt. Alle Shopware-Daten werden mit dem TYPO3-Caching-Framework
gecached um unnötig häufige API-Aufrufe zu vermeiden und dadurch das Frontend-Rendering zu beschleunigen.

**Feature List**

* Auflisten bzw. Anzeigen von Shopware Artikeln und Kategorien
* Verlinken von Shopware Artikel und Kategorien (nur mit Shopware-Plugin "TYPO3-Connector" möglich)
* Backend-Toolbar um aktuellen Status der Shopware-Verbindung anzuzeigen
* Autosuggest-Wizard um beim Anlegen eines Frontend-Plugins schnell Artikel / Kategorien im Shopware zu suchen
* Zwischenspeichern von API-Aufrufen mit Hilfe des TYPO3 Caching Frameworks
* Rendern eines Menüs auf Basis des Shopware-Kategorie Baum

**Coming-Soon**

* Solr-Indexer zum Indexieren der Shopware-Artikel und -Kategorien auf Seiten von TYPO3 zur Integration mit typo3-solr_
* Anzeigen und Auflisten weiterer Resourcen wie Lieferanten etc.
* Weitere Unit Tests um die Qualität zu erhöhen

.. _shopware: https://www.shopware.com/
.. _REST-API: https://developers.shopware.com/developers-guide/rest-api/
.. _hier: https://store.shopware.com/portrino/portrino-typo3connector.html
.. _typo3-solr: https://typo3.org/extensions/repository/view/solr
.. _TYPO3-Plugin: https://store.shopware.com/portrino/portrino-typo3connector.html

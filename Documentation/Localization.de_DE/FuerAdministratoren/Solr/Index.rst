.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _solr-configuration:

Solr Konfiguration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Installieren Sie zuerst das Solr Plugin wie in der Dokumentation_ beschrieben.
Wenn Sie das Statische Typoscript hinzugefügt haben (:ref:`typoscript-configuration`), ist darin bereits eine Standard Konfiguration für die Indexierung von Shopware Artikeln enthalten.


Indexierung starten
---------------------------

Sie müssen nun die Artikel zur Solr Index Queue hinzufügen. Dazu öffnen Sie das Backend Modul "Suche" und wählen dort "Index Queue" im linken Inhaltsverzeichnis.
Wählen Sie nun "Portrino_PxShopware_Domain_Model_Article" und/oder "pages" aus und klicken auf "Queue Selected Content for Indexing".

.. figure:: ../../../Images/ForAdministrators/sol_index_queue.png
   :alt: Add Articles to solr index queue
   :width: 1200px
.. :align: left

   Artikel zur Solr Index Queue hinzufügen


Danach muss noch ein Task im Planer angelegt werden. Öffnen Sie dazu das gleichnamige Backend Modul und suchen Sie nach einem Task namens "Index Queue Worker (solr)".

Wenn dieser bereits existiert müssen Sie nichts tun, ansonsten legen Sie ihn an wie hier_ beschrieben.
Starten Sie den Task und die ersten Ergebnisse sollten im Frontend zu sehen sein.


Suchanfrage Konfigurieren
---------------------------

Welche Felder des Index durchsucht und wie diese gewichtet werden, kann im TypoScript konfiguriert werden. Hier eine Beispiel:

::

    plugin.tx_solr {
        search {
            ## qf parameter http://wiki.apache.org/solr/DisMaxQParserPlugin#qf_.28Query_Fields.29
            queryFields = productNumber_textS^49.0, title^25.0, descriptionLong_textS^15.0, content^10.0, keywords^2.0, tagsH1^5.0, tagsH2H3^3.0, tagsH4H5H6^2.0, tagsInline^1.0, details_textM^3.0, ean_textS^3.0, category_textM^3.0, details_textM^3.0, compatibleProducer_textM^2.0, supplier_textS^5.0
        }
    }

Die meisten Textfelder werden in 2 Version gespeichert: als ``*_stringS`` (Groß- und Kleinschreibung wird beachtet) und ``*_textS`` (Groß- und Kleinschreibung wird NICHT beachtet)
So besitzen Sie maximale Flexibilität für Ihre Suchanfrage.

Um Shopware Artikel immer am Anfang der Suchergebnisse anzuzeigen, hilft dieses TypoScript:

::

    plugin.tx_solr {
        search {
            ## see http://wiki.apache.org/solr/DisMaxQParserPlugin#bq_.28Boost_Query.29
            boostQuery = (type:Portrino_PxShopware_Domain_Model_Article)^1000
        }
    }


Facettierung
---------------------------

Sie können jedes Feld im Frontend als Solr Facette benutzen um das Suchergebnis zu filtern. Hier sind einige Beispiele:

::

    plugin.tx_solr {
        search {
            faceting = 1
            faceting.facets {

                type {
                    label = Type
                    label.insertData = 1
                    showEvenWhenEmpty = 1
                    renderingInstruction = CASE
                    renderingInstruction {
                        key.field = optionValue

                        pages = TEXT
                        pages.value = Page
                        pages.insertData = 1
                        Portrino_PxShopware_Domain_Model_Article < .pages
                        Portrino_PxShopware_Domain_Model_Article.value = Shopware Article
                    }
                }
                category {
                    field = category_stringM
                    label = Shopware Category
                    label.insertData = 1
                    showEvenWhenEmpty = 0
                    keepAllOptionsOnSelection = 1
                    operator = OR
                    sortBy = alpha
                }
                supplier {
                    field = supplier_stringS
                    label = Shopware Supplier
                    label.insertData = 1
                    showEvenWhenEmpty = 0
                    keepAllOptionsOnSelection = 1
                    operator = OR
                    sortBy = alpha
                }
            }
        }
    }



Shopware Plugin konfigurieren
-----------------------------

Durch die obigen Schritte für Queue und Task erhalten Sie einen Suchindex der aktuellen Artikel.
Wenn Sie das Plugin "TYPO3-Connector" aus dem `Shopware Store`_ verwenden, werden Ihre Änderungen im Shopware Backend direkt auch im Solr Index aktualisiert und spiegeln sich sofort im Suchergebnis wieder.

.. note::

   Seit Version **2.0.0** des Shopware Plugins TYPO3-Connector können sie die API-URL (http://domain.tld/?type=1471426941) ihres PxShopware Endpoints
   im Backend User auf SW Seite eintragen, damit dieser über Änderungen in Artikeln, Kategorien usw. TYPO3 benachrichtigen kann und somit auch der
   Solr Index aktualisiert werden kann.

.. figure:: ../../../Images/ForAdministrators/shopware_api_url.png
   :alt: Hinzufügen der API-URL zum SW Backend Benutzer
   :width: 800px
.. :align: left

   Hinzufügen der API-URL zum SW Backend Benutzer


Shopware aktualisiert den Solr Index
--------------------------------------


.. _Dokumentation: https://docs.typo3.org/typo3cms/extensions/solr/
.. _hier: https://docs.typo3.org/typo3cms/extensions/solr/GettingStarted/IndexTheFirstTime.html#started-index
.. _Shopware Store: https://store.shopware.com/en/port116496663052/typo3-connector.html
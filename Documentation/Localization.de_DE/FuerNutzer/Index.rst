.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _for-administrators:

Für Nutzer
==========

Artikel anzeigen
----------------

Zunächst muss im Page-Modus ein neues Content-Element hinzugefügt und das Article(s)-Plugin ausgewählt werden.

.. figure:: ../../Images/ForUsers/px_shopware_add-plugin.png
   :width: 500px
   :alt: Shopware-Plugins: Artikel und Kategorien

    TYPO3 Frontend-Plugins zur Anzeige von Shopware Artikeln / Kategorien

Anschließend hat man die Möglichkeit jene Artikel über den Reiter "Plugin" auszuwählen, welche anschließend im
Frontend angezeigt werden sollen.

.. figure:: ../../Images/ForUsers/px_shopware_add-shopware-articles-fetch-all-items.png
   :width: 500px
   :alt: Artikel auswählen im Frontend-Plugin

    Shopware-Artikel aus Liste aller Artikel des Shops auswählen

.. figure:: ../../Images/ForUsers/px_shopware_add-shopware-categories-autocomplete-wizard.png
   :width: 800px
   :alt: Artikel via Autocomplete-Wizard auswählen

    Shopware-Artikel via Autocomplete-Wizard auswählen


.. note::

    Standardmäßig wird der Autocomplete-Wizard für die Auswahl von Artikeln und Kategorien im Frontend-Plugin verwendet.
    Sie können alle Artikel des Shops anzeigen, indem sie dies entsprechend im Extension Manager in der Extension
    Konfiguration aktivieren.

.. important::

   Bitte beachten Sie, dass für eine optimale Ausgabe im Frontend zunächst ein Artikel-Partial angelegt werden sollte. (:ref:`typoscript-configuration`)

.. note::

    Mit ``{article.url}`` kann man im Fluid-Template auf den jeweilige Artikel im Shopware verlinken. Dieses Feature steht nur in der
    Full-Version zur Verfügung!


Kategorien einbinden
--------------------

Ähnlich zu den Artikel(n) kann eine Kategorie-Liste im Frontend angezeigt werden. Dazu wieder im Page-Modus
ein neues Content-Element hinzugefügen und das Categories(s)-Plugin ausgewählen.

Anschließend im Reiter "Plugin" die anzuzeigenden Kategorien auswählen.

.. figure:: ../../Images/ForUsers/px_shopware_add-plugin.png
   :width: 500px
   :alt: Shopware-Plugins: Artikel und Kategorien

    TYPO3 Frontend-Plugins zur Anzeige von Shopware Artikeln / Kategorien

Anschließend hat man die Möglichkeit jene Kategorien über den Reiter "Plugin" auszuwählen, welche anschließend im
Frontend angezeigt werden sollen.

.. figure:: ../../Images/ForUsers/px_shopware_add-shopware-categories-fetch-all-items.png
   :width: 500px
   :alt: Kategorien auswählen im Frontend-Plugin

    Kategorien auswählen im Frontend-Plugin

.. note::

    Mit Hilfe des Ausdrucks von ``{category.subCategories}`` kann man im Fluid-Template Menüstrukturen aus Shopware
    Kategorien auf Seiten von TYPO3 generieren.

.. note::

    Mit ``{category.url}`` kann man im Fluid-Template auf die jeweilige Kategorie im Shopware verlinken. Dieses Feature steht nur in der
    Full-Version zur Verfügung.
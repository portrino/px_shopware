.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _extension-manager-configuration:

Konfiguration im Extension Manager
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Nachdem die Installation (:ref:`for-administrators`) abgeschlossen wurde, können Sie die Konfiguration vornehmen indem
dem Sie auf das "Zahnradsymbol" klicken.

API freischalten
----------------

Zunächst muss im Shopware-Backend ein Nutzer angelegt und dieser für die API-Nutzung freigeschalten werden.
(Für weiterführende Informationen zur Shopware-REST-API wird folgender Artikel empfohlen: https://developers.shopware.com/developers-guide/rest-api/ )

.. figure:: ../../../Images/ForAdministrators/shopware-api-add-user.png
    :width: 600px
    :align: left
    :alt: API-Nutzer in Shopware hinzufügen

    Shopware-Benutzer hinzufügen und die API-Nutzung freischalten

Anschließend können Sie die API-Zugangsdaten im TYPO3 Extension Manager in der px_shopware Konfiguration hinterlegen.
Die Zugangsdaten sind notwendig um die Kommunikation zwischen Ihrer Shopware-Instanz und TYPO3 zu gewährleisten.

.. figure:: ../../../Images/ForAdministrators/px_shopware-configure-basic-api.png
    :width: 600px
    :align: left
    :alt: API-Konfiguration in der px_shopware

Nach dem Ändern der API-Konfiguration muss das TYPO3-Backend mit ``Strg + F5`` neu geladen werden, damit die Toolbar den
Verbindungsstatus korrekt wieder gibt.

Shopware-Connector: Status-Anzeige in der Toolbar
------------------------------------------------

In der TYPO3-Toolbar wird nach erfolgreicher Installation der Extension das px_shopware Icon angezeigt. Direkt im Icon
wird der Verbindungsstatus farblich wieder gegeben.

.. figure:: ../../../Images/ForAdministrators/toolbar-disconnected.png
   :width: 500px
   :align: left
   :alt: Shopware-Plugins: Produkte und Kategorien hinzufügen

   TYPO3-Toolbar zeigt Disconnected-Status in rot für px_shopware

Mit einem Klick auf das Icon in der Toolbar öffnet sich ein Overlay, welches weitere Informationen zur Anbindung an
Shopware aufzeigt.

.. figure:: ../../../Images/ForAdministrators/toolbar-trial-version.png
   :width: 500px
   :align: left
   :alt: TYPO3-Toolbar zeigt Connected-Status und Informationen zur Anbindung

   Nachdem die Shopware-Daten korrekt eingegeben wurden, zeigt sich der Vergbindungsstatus für die Trial-Version in gelb.

Folgende Informationen werden in der Toolbar angezeigt:

* Verbindungsstatus: disconnected (rot), connected trial version (gelb), connected full version (grün)
* Shops: Anbindung zu einer oder mehreren Shopware-Instanzen
* Version und Revision der angebundenen Shopware-Instanzen
* Cache-Status und Anzahl der Cache-Einträge

.. important::

   Im der TYPO3-Toolbar wird entweder die API-Konfiguration aus dem Extension Manager oder die von der Rootseite gesehen
   erste im TypoScript (:ref:`typoscript-configuration`) gefundene API-Konfiguration genutzt. Insofern sie in Ihrem TYPO3 CMS mehere Shop-Systeme anbinden
   wollen ist somit nicht immer im Backend ersichtlich ob alle Shops verbunden sind.


Caching
-------

Die API-Aufrufe werden über das interne Caching-Framework gecached um die Performance der Erweiterung zu steigern.

Die Konfiguration des Caching kann ebenfalls im Extension Manager vorgenommen werden.

.. figure:: ../../../Images/ForAdministrators/px_shopware-configure-cache.png
   :width: 600px
   :align: left
   :alt: Caching für px_shopware konfigurieren

Folgende Einstellungen können vorgenommen werden:

* Cache Lifetime: Die Gültigkeitsdauer von Cacheeinträgen kann hier in Sekunden angegeben werden
* Cache deaktivieren (nicht empfohlen)


Über das Blitz-Symbol in der TYPO3-Toolbar kann der Shopware-Cache komplett geleert werden.

.. figure:: ../../../Images/ForAdministrators/px_shopware-flush-cache.png
   :width: 300px
   :align: left
   :alt: px_shopware Cache leeren

Weitere Informationen zum TYPO3-Caching finden Sie hier:

* https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/Index.html
* http://typo3blog.at/blog/artikel/typo3-caching-grundlagen/


Logging
-------

Das Logging von Backend-Fehlern kann ebenfalls über den Extension Manager deaktiviert (oder aktiviert) werden.

.. figure:: ../../../Images/ForAdministrators/px_shopware-configure-logging.png
   :width: 600px
   :align: left
   :alt: Logging für px_shopware konfigurieren

Geloggt wird in den TYPO3 Systemlog_ und via Logging-API_ in das Logfile der px_shopware (dieses wird standardmäßig im Ordner ``typo3temp/logs/`` abgelegt).

.. _Systemlog: https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/SystemLog/Index.html
.. _Logging-API: https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Logging/Index.html

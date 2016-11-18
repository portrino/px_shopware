.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _admin-manual:

Für Administratoren
===================


Importieren und installieren
----------------------------

Die px_shopware Erweiterung kann auf zwei Arten auf Ihrem Server importiert werden:

- Aus dem TYPO3 Extension Repository (TER) oder
- Via Composer

Aus dem TER
^^^^^^^^^^^

**Direkt über den Extension-Manager**

Wählen Sie bitte "*Get Extensions*" im TYPO3 Extension-Manager und aktualisieren Sie die Liste der Extensions. Suchen Sie anschließend nach "px_shopware"
und klicken Sie "Import and Install" um die aktuellste Version zu erhalten.

.. note::

    Die Erweiterung benötigt keine weiteren Extensions. Die einzige Abhängigkeit ist TYPO3 7.6.

**Zip-Datei herunter laden**

Die Extension kann auch als Zip-File unter https://typo3.org/extensions/repository/view/px_shopware heruntergeladen und anschließend installiert werden.

.. figure:: ../../Images/ForAdministrators/px_shopware-install-zip-file_2.png
   :width: 500px
.. :align: left

    Bitte nutzen Sie das das Icon "*Upload Extension .t3x / .zip*" unterhalb des Dropdown-Felds um die Shopware-Extension zu installieren.

.. figure:: ../../Images/ForAdministrators/px_shopware-install-zip-file_4.png
   :width: 500px
.. :align: left

    Anschließend erscheint die Status-Meldung, dass px_shopware erfolgreich installiert wurde.



Via Composer
^^^^^^^^^^^^

Seit TYPO3 7.x können Erweiterungen via composer_ installiert werden. Wenn Sie die px_shopware Erweiterungen via composer installieren möchten,
müssen Sie TYPO3 entsprechend im COMPOSER MODE verwenden. Weitere Informationen hierzu finden Sie unter: https://wiki.typo3.org/Composer#Composer_Mode

Fügen Sie bitte folgende Zeile in den ``require`` Abschnitt Ihrer  ``composer.json`` hinzu:

.. code-block:: json

    "typo3-ter/px-shopware": "dev-master",

Bitte anschließend ``composer install`` bzw. ``composer update`` durchführen.

Wenn Sie eine spezifische Version der Erweiterung installieren wollen, ändern Sie bitte ``dev-master`` in die entsprechende Versionsnummer (z.B.: ``^1.0``)

Installation
------------

Nach erfolgreichem Import der Erweiterung erscheint diese im Extension Manager in der Liste der "Installed Extensions". Sofern diese noch nicht aktiviert ist können Sie dies über den Button in der Spalte "A/D" vornehmen.


Anschließend muss die Extension *konfiguriert* werden:

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   ExtensionManager/Index
   TypoScript/Index
   Solr/Index

.. _composer: https://getcomposer.org/
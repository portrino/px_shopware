.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Import
------

There are two ways of installing the extension. As described `here <https://wiki.typo3.org/Composer#Composer_Mode>`_

Import the extension to your server from the

- TYPO3 Extension Repository (TER) or
- via GIT

From TER (Classic Mode)
^^^^^^^^^^^^^^^^^^^^^^^

Select "*Get Extensions*" in the extension manager and update your extension list. Search for "px_shopware" and click "Import and Install" to get the latest version.
There are no other dependencies than TYPO3 7.6.

.. figure:: ../Images/AdministratorManual/GetExtensionPxShopware.png
    :width: 500px
    :align: left

::todo

    @Andreas: Screeshot
    Falls vorhanden mal ein normales TYPO3 7.6 nehmen und versuchen die EXT ohne Composer zu installieren
    Also einfach die ZIP herunterladen und in das Classic TYPO3 hochladen

Via composer
^^^^^^^^^^^^

Since TYPO3 7.x you are able to get extension via composer. As described `here <https://wiki.typo3.org/Composer#Composer_Mode>`_ here you just have to user TYPO3 in Composer Mode
and add this line to your require section within the composer.json file and run composer install / composer update.

.. code-block:: json

    "typo3-ter/px-shopware": "dev-master",

If you want a specific version than change "dev-master" to the version you need.

Installation
------------

Wether you run your TYPO3 in Classic Mode or Composer Mode you should install the extension via ExtensionManager or via Composer. Click `here <https://wiki.typo3.org/Composer>`_ for more details

After the installation is finished open the Extension Configuration by clicking on the "Configure" gear.


Configuration
-------------

First of all you have to read this article_ about enabling of shopware API for third party usage.

.. _article: https://developers.shopware.com/developers-guide/rest-api/

After you have done this you should have your API credentials ready which are neccessary for communcation of "px_shopware"
extension with your shopware instance.


::todo

    @Andreas:
    Bitte wie bei PxHybridAuth in 2 Parts hier unterteilen (ExtensionManager und TypoScript)
    Beide auch in Unterordner packen


Shopware Connector Status Toolbar
---------------------------------

::todo

    @Andreas:
    Screenshot, Status beschreiben (Connected Full, Connected Trial und Disconnected)
    Strg + F5 muss gemacht werden damit es sich aktualisiert
    Cache-Status

Caching
-------

::todo

    @Andreas:
    API-Request werden über das interne Caching-Framework gecached
    (https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/Index.html)
    (http://typo3blog.at/blog/artikel/typo3-caching-grundlagen/)
    Per Default wird Database Backend (https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/FrontendsBackends/Index.html#database-backend)
    und String Frontend (https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/FrontendsBackends/Index.html#string-frontend)
    für Caching genutzt

    Vorteile vom Caching erklären

    CacheLifeTime kann via Extension Manager geändert werden

    Cache Clear "Blauer Blitz" erklären und Screenshot

    Cache deaktivieren via Extension Manager


Logging
-------

::todo

    @Andreas:
    Geloggt wird in system log (https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/SystemLog/Index.html)
    und in via Logging-API (https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Logging/Index.html) ins px_shopware LogFile welches unter typo3temp liegt
    Logging ist nur im BE aktiv, im Frontend wird Fehler geworfen (ob das so gut ist weiss ich nicht :)
    Logging kann via Extension Manager deaktiviert werden

FAQ
^^^

Possible subsection: FAQ

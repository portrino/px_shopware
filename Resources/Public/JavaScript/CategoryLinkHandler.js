/**
 * Module: TYPO3/CMS/PxShopware/CategoryLinkHandler
 * Article link interaction
 */
define(['jquery', 'TYPO3/CMS/Recordlist/LinkBrowser', 'TYPO3/CMS/Backend/LegacyTree'], function($, LinkBrowser, Tree) {
	'use strict';

	/**
	 *
	 * @type {{currentLink: string}}
	 * @exports TYPO3/CMS/Recordlist/FileLinkHandler
	 */
	var FileLinkHandler = {
		currentLink: ''
	};

	/**
	 *
	 * @param {Event} event
	 */
	FileLinkHandler.linkFile = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction($(this).data('category'));
	};

	/**
	 *
	 * @param {Event} event
	 */
	FileLinkHandler.linkCurrent = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction(FileLinkHandler.currentLink);
	};

	Tree.ajaxID = 'sc_alt_file_navframe_expandtoggle';

	$(function() {
		FileLinkHandler.currentLink = $('body').data('currentLink');

		$('a.t3js-fileLink').on('click', FileLinkHandler.linkFile);
		$('input.t3js-linkCurrent').on('click', FileLinkHandler.linkCurrent);
	});

	return FileLinkHandler;
});

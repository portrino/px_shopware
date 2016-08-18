/**
 * Module: TYPO3/CMS/PxShopware/CategoryLinkHandler
 * Article link interaction
 */
define(['jquery', 'TYPO3/CMS/Recordlist/LinkBrowser', 'TYPO3/CMS/Backend/LegacyTree'], function($, LinkBrowser, Tree) {
	'use strict';

	/**
	 *
	 * @type {{currentLink: string}}
	 * @exports TYPO3/CMS/PxShopware/CategoryLinkHandler
	 */
	var CategoryLinkHandler = {
		currentLink: ''
	};

	/**
	 *
	 * @param {Event} event
	 */
	CategoryLinkHandler.linkCategory = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction($(this).data('category'));
	};

	/**
	 *
	 * @param {Event} event
	 */
	CategoryLinkHandler.linkCurrent = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction(CategoryLinkHandler.currentLink);
	};

	Tree.ajaxID = 'sc_alt_file_navframe_expandtoggle';

	$(function() {
		CategoryLinkHandler.currentLink = $('body').data('currentLink');

		$('a.t3js-fileLink').on('click', CategoryLinkHandler.linkCategory);
		$('input.t3js-linkCurrent').on('click', CategoryLinkHandler.linkCurrent);
	});

	return CategoryLinkHandler;
});

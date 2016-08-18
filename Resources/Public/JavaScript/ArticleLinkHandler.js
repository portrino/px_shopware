/**
 * Module: TYPO3/CMS/PxShopware/ArticleLinkHandler
 * Article link interaction
 */
define(['jquery', 'TYPO3/CMS/Recordlist/LinkBrowser', 'TYPO3/CMS/Backend/LegacyTree'], function($, LinkBrowser, Tree) {
	'use strict';

	/**
	 *
	 * @type {{currentLink: string}}
	 * @exports TYPO3/CMS/PxShopware/ArticleLinkHandler
	 */
	var ArticleLinkHandler = {
		currentLink: ''
	};

	/**
	 *
	 * @param {Event} event
	 */
	ArticleLinkHandler.linkArticle = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction($(this).data('article'));
	};

	/**
	 *
	 * @param {Event} event
	 */
	ArticleLinkHandler.linkCurrent = function(event) {
		event.preventDefault();

		LinkBrowser.finalizeFunction(ArticleLinkHandler.currentLink);
	};

	Tree.ajaxID = 'sc_alt_file_navframe_expandtoggle';

	$(function() {
		ArticleLinkHandler.currentLink = $('body').data('currentLink');

		$('a.t3js-fileLink').on('click', ArticleLinkHandler.linkArticle);
		$('input.t3js-linkCurrent').on('click', ArticleLinkHandler.linkCurrent);
	});

	return ArticleLinkHandler;
});

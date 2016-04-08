/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/PxShopware/FormEngineSuggest
 * Functionality to load suggest functionality
 */
define(['jquery', 'jquery/autocomplete', 'TYPO3/CMS/Backend/FormEngine'], function ($) {

    $(document).ready(function() {
        TYPO3.FormEngine.reinitialize();
        require(['TYPO3/CMS/PxShopware/FormEngineSuggest'], function(PxShopwareSuggest) {
            PxShopwareSuggest($('.t3-form-suggest-px-shopware'));
        });
    });

    var initialize = function($searchField) {
        var $containerElement = $searchField.closest('.t3-form-suggest-container');
        var type = $searchField.data('type'),
            minimumCharacters = $searchField.data('minchars'),
            url = TYPO3.settings.ajaxUrls['tx_pxshopware::searchAction'],
            params = {
                'type': type
            };

        $searchField.autocomplete({
            // ajax options
            serviceUrl: url,
            params: params,
            type: 'POST',
            paramName: 'value',
            dataType: 'json',
            minChars: minimumCharacters,
            groupBy: 'typeLabel',
            containerClass: 'autocomplete-results',
            appendTo: $containerElement,
            forceFixPosition: false,
            preserveInput: true,
            showNoSuggestionNotice: true,
            noSuggestionNotice: '<div class="autocomplete-info">No results</div>',
            minLength: minimumCharacters,
            // put the AJAX results in the right format
            transformResult: function(response) {
                return {
                    suggestions: $.map(response, function(dataItem) {
                        return { value: dataItem.text, data: dataItem };
                    })
                };
            },
            // Rendering of each item
            formatResult: function(suggestion, value) {
                return $('<div>').append(
                    $('<a class="autocomplete-suggestion-link" href="#">' +
                        suggestion.data.sprite + suggestion.data.text +
                        '</a></div>').attr({
                        'data-label': suggestion.data.label,
                        'data-type': suggestion.data.type,
                        'data-uid': suggestion.data.uid
                    })).html();
            },
            onSearchComplete: function() {
                $containerElement.addClass('open');
            },
            beforeRender: function(container) {
                // Unset height, width and z-index again, should be fixed by the plugin at a later point
                container.attr('style', '');
                $containerElement.addClass('open');
            },
            onHide: function() {
                $containerElement.removeClass('open');
            }
        });

        // set up the events
        $containerElement.on('click', '.autocomplete-suggestion-link', function(evt) {
            evt.preventDefault();
            var insertData = '';
            insertData = $(this).data('uid');



            var formEl = $searchField.data('fieldname');

            console.debug(formEl);

            var labelEl = $('<div>').html($(this).data('label'));
            var label = labelEl.text();
            var title = labelEl.find('span').attr('title') || label;
            setFormValueFromBrowseWin(formEl, insertData, label, title);
            // TBE_EDITOR.fieldChanged(table, uid, field, formEl);
        });
    };

    /**
     * Return a function that gets DOM elements that are checked if suggest is already initialized
     * @exports TYPO3/CMS/PxShopware/FormEngineSuggest
     */
    return function(selectorElements) {
        $(selectorElements).each(function(key, el) {
            if (!$(el).data('t3-suggest-px-shopware-initialized')) {
                initialize($(el));
                $(el).data('t3-suggest-px-shopware-initialized', true);
            }
        });
    };
});

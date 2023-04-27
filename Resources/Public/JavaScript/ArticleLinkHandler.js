/**
 * Module: TYPO3/CMS/PxShopware/ArticleLinkHandler
 * Article link interaction
 */

TYPO3.settings.FormEngine = {'formName': 'editform'};
define(['jquery', 'jquery/autocomplete', 'TYPO3/CMS/Backend/FormEngine', 'TYPO3/CMS/Recordlist/LinkBrowser'], function($, autocomplete, FormEngine, LinkBrowser) {

    /**
     * @type {{}}
     * @exports TYPO3/CMS/PxShopware/ArticleLinkHandler
     */
    var ArticleLinkHandler = {};

    var initialize = function($searchField) {
        var $containerElement = $searchField.closest('.t3-form-suggest-container');
        var $fieldContainer = $containerElement.closest('.formengine-field-item');

        /**
         * we have to hide the item to select wrapper div manually via JS, because there is no chance to
         * hide the selector wrapper div via CSS
         */
        $fieldContainer.find('.form-wizards-element .form-multigroup-wrap .form-multigroup-item:nth-child(2)').children().hide();

        var $loader = $('#loader');
        var type = $searchField.data('type'),
            language = $searchField.data('language'),
            minimumCharacters = $searchField.data('minchars'),
            url = TYPO3.settings.ajaxUrls['px_shopware_search'],
            params = {
                'type': type,
                'language': language
            },
            insertValue = function(element) {
                var insertData = $(element).data('uid');

                var formEl = $searchField.data('fieldname');
                var labelEl = $('<div>').html($(element).data('label'));
                var label = labelEl.text();
                var title = labelEl.find('span').attr('title') || label;
                TYPO3.FormEngine.setSelectOptionFromExternalSource(formEl, insertData, label, title);
            };

        var timeoutId = 0;
        $searchField.keyup(function() {
            if ($searchField.autocomplete().disabled === true) {
                $searchField.autocomplete('disable');
                clearTimeout(timeoutId); // doesn't matter if it's 0
                timeoutId = setTimeout(function() {
                    $searchField.autocomplete('enable');
                    $searchField.keyup();
                }, 1000);
            }
        });

        $searchField.autocomplete('disable');

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
                if (response != null) {
                    return {
                        suggestions: $.map(response, function(dataItem) {
                            return {value: dataItem.text, data: dataItem};
                        })
                    };
                } else {
                    return {
                        suggestions: []
                    };
                }
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
            onSearchStart: function() {
                $loader.show();
            },
            onSearchComplete: function() {
                $loader.hide();
                $containerElement.addClass('open');
                $searchField.autocomplete('disable');
            },
            beforeRender: function(container) {
                // Unset height, width and z-index again, should be fixed by the plugin at a later point
                container.attr('style', '');
                $containerElement.addClass('open');
            },
            onSelect: function() {
                insertValue($containerElement.find('.autocomplete-selected a')[0]);
            },
            onHide: function() {
                $containerElement.removeClass('open');
            }
        });

        $searchField.autocomplete('disable');
        // set up the events
        $containerElement.on('click', '.autocomplete-suggestion-link', function(evt) {
            evt.preventDefault();
        });
    };

    $(function() {
        $('#lshopware-article-wizard').on('focus', function(event) {
            if (!$(this).data('t3-suggest-px-shopware-initialized')) {
                initialize($(this));
                $(this).data('t3-suggest-px-shopware-initialized', true);
            }
        });
        $('#lshopware-article-form').on('submit', function(event) {
            event.preventDefault();

            var value = $(this).find('[name="lshopware-article"]').val();
            if (value === 't3://shopware_article?id=') {
                return;
            }
            if (value.indexOf('t3://shopware_article?id=') === 0) {
                value = value.substr(25);
            }
            LinkBrowser.finalizeFunction('t3://shopware_article?id=' + value);
        });
    });

    return ArticleLinkHandler;
});

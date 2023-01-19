import { autofillData } from "../data/data";
import { autofillTemplates } from "../templates/templates";

jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            this.initEvents();
            this.initAutofill();
            this.initAutofillFilter();
        },

        initCache() {
            this.selectors = {
                autofillOption: '.js-autofill-option',
                autofillNoResults: '.js-autofill-no-results',
                smoothScroll: '.js-smooth-scroll',
                bcc: '.js-bcc',
                openMap: '.js-open-map',
                assessmentMap: '#assessment-map',
            };

            this.$body = $('body');
            this.$jsWrap = $('.js-wrap');
            this.$autofill = $('.js-autofill');
            this.$autofillFilter = $('.js-autofill-filter');
            this.$autofillInput = $('.js-autofill-input');
            this.$autofillOptionsList = $('.js-autofill-options-list');
            this.$autofillSearchButton = $('.js-autofill-search-button');
            this.$getCoordinatesButton = $('.js-get-coordinates');
            this.$assessmentMap = $($_.selectors.assessmentMap);

            this.constructedOptions = {};

            this.hereApiKey = 'DRoLaDHSmAieGcegl41-VD7sPrKAnFVLzmmqquZPlAQ';
        },

        initEvents() {
            $_.$body.on('click', $_.selectors.openMap, (e) => {
                const { id, selectedAddress, userSearch } = $_._getAutofillDataBySelectedOption(e);

                $_._getCoordsByLocationId({
                    id,
                    callback: (props) => {
                        const { displayPosition } = props;
                        
                        $_._openMap({
                            displayPosition,
                            selectedAddress,
                            userSearch,
                        });
                    }
                });
            });

            $_.$getCoordinatesButton.on('click', (e) => {
                const
                    $currentButton = $(e.currentTarget),
                    $wrap = $currentButton.closest($_.$jsWrap),
                    $relatedInput = $wrap.find($_.$autofillInput);

                $_._getCoordsByLocationId({
                    id: $relatedInput.data('id'),
                    callback: (props) => {
                        const { displayPosition, address } = props;

                        $relatedInput.trigger('coordinates-loaded', {
                            coordinates: displayPosition,
                            address,
                        });

                        $_._resetInputData($relatedInput);
                    }
                });
            });

            $_.$autofillSearchButton.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const
                    $currentTarget = $(e.currentTarget),
                    $wrap = $currentTarget.closest($_.$jsWrap),
                    $relatedAutofill = $wrap.find($_.$autofill);

                $relatedAutofill.trigger('trigger:search');
            });
        },

        initAutofillFilter() {
            function filter(props) {
                const
                    { $relatedInput, $relatedOptions, $autofillNoResults } = props,
                    inputValue = $relatedInput.val();

                let matches = false;

                $autofillNoResults.removeClass('_show');

                $relatedOptions.each((key, item) => {
                    const
                        $currentOption = $(item),
                        dataVal = $currentOption.data('value'),
                        dataName = $currentOption.data('name'),
                        valHighlight = $_._getHighlightString({ text: dataVal, replace: inputValue }),
                        nameHighlight = $_._getHighlightString({ text: dataName, replace: inputValue }),
                        hasMatches = valHighlight || nameHighlight,
                        isHighlighted = $currentOption.data('highlight');

                    if (isHighlighted) $currentOption.html(dataVal).data('highlight', false);

                    if (hasMatches) {
                        matches = true;
                        $currentOption.data('highlight', true);

                        if (valHighlight) $currentOption.html(valHighlight);
                        if (nameHighlight) $currentOption.append(`<div class="option-label">${nameHighlight}</div>`);
                    }

                    $currentOption[hasMatches ? 'removeClass' : 'addClass']('_hide');
                });

                if (!matches) $autofillNoResults.addClass('_show');
            }

            function reset($options) {
                $options.removeClass('_hide');

                $options.each((key, item) => {
                    const
                        $currentOption = $(item),
                        dataVal = $currentOption.data('value');

                    if ($currentOption.data('highlight')) $currentOption.html(dataVal).data('highlight', false);
                });
            }

            function constructOptions(props) {
                const
                    { $currentAutofill, $autofillOptionsList, dataJsonName } = props,
                    dataNoResultText = $currentAutofill.data('no-result-text');

                if (!$_.constructedOptions[dataJsonName]) {
                    $_.constructedOptions[dataJsonName] = autofillTemplates.optionsList({
                        array: autofillData[dataJsonName],
                        noResult: {
                            text: dataNoResultText,
                        },
                    });
                }

                $autofillOptionsList.append($_.constructedOptions[dataJsonName]);
            }

            function bindEvents(props) {
                const
                    { $currentAutofill, $relatedInput } = props,
                    $scroll = $currentAutofill.find($_.selectors.smoothScroll),
                    $autofillNoResults = $currentAutofill.find($_.selectors.autofillNoResults),
                    $relatedOptions = $currentAutofill.find($_.selectors.autofillOption);

                let timer = null;

                $relatedOptions.on('click', (e) => {
                    const
                        $currentTarget = $(e.currentTarget),
                        dataValue = $currentTarget.data('value');

                    $relatedInput.val(dataValue).valid();
                    $currentAutofill.removeClass('_active');
                    reset($relatedOptions);
                });

                $relatedInput.on('blur', (e) => {
                    if (!$(e.relatedTarget).closest($_.selectors.bcc).length) $currentAutofill.removeClass('_active');
                });

                $relatedInput.on('keyup change', () => {
                    clearTimeout(timer);
                    $currentAutofill.addClass('_active');

                    timer = setTimeout(() => {
                        if ($relatedInput.val().length) {
                            filter({ $relatedInput, $relatedOptions, $autofillNoResults })
                        } else {
                            reset($relatedOptions);
                        }

                        $scroll.trigger('trigger:scroll-top', { onlyCurrent: true }).trigger('trigger:update-scroll');
                    }, 500);
                });
            }

            $_.$autofillFilter.each((key, item) => {
                const
                    $currentAutofill = $(item),
                    $relatedInput = $currentAutofill.find($_.$autofillInput),
                    $autofillOptionsList = $currentAutofill.find($_.$autofillOptionsList),
                    dataJsonName = $autofillOptionsList.data('json-name');

                let optionsAdded = false;

                if (dataJsonName) {
                    const phoneCodes = autofillData[dataJsonName].map(({ code, dial_code }) => `${code} ${dial_code}`);
                    $relatedInput[0]._validPhoneCode = (val) => phoneCodes.indexOf(val) !== -1;
                }

                $relatedInput.on('focus click', () => {
                    $currentAutofill.addClass('_active');

                    if (!optionsAdded) {
                        optionsAdded = true;
                        if (dataJsonName) constructOptions({ $currentAutofill, $autofillOptionsList, dataJsonName });
                        bindEvents({ $currentAutofill, $relatedInput });
                    }
                });
            });
        },

        initAutofill() {
            function addCategoryOptions(obj) {
                const
                    { data, inputVal, $currentAutofill, $autofillOptionsList } = obj,
                    optionsObj = {};

                if (data.length) {
                    for (let i = 0; i < data.length; i++) {
                        const
                            { facet, facet_type, url } = data[i],
                            highlightedText = $_._getHighlightString({ text: facet, replace: inputVal });

                        if (highlightedText) {
                            if (!optionsObj[facet_type]) optionsObj[facet_type] = [];

                            optionsObj[facet_type].push(autofillTemplates.option({
                                text: highlightedText,
                                url: url,
                            }));
                        }
                    }

                    $autofillOptionsList.html(autofillTemplates.optionsCategories(optionsObj));
                    $currentAutofill.addClass('_active').data('has-options', true);
                } else {
                    clear({ $currentAutofill, $autofillOptionsList, noResults: false });
                }
            }

            function addHereOptions(obj) {
                const
                    { data={}, $currentAutofill, $autofillOptionsList, dataProps={} } = obj,
                    { optionPresetMod } = dataProps,
                    { suggestions } = data;

                if (suggestions && suggestions.length) {
                    let itemsAdded = 0;

                    $autofillOptionsList.html('');

                    suggestions.forEach(item => {
                        const { address, locationId, matchLevel } = item;

                        if (matchLevel === 'houseNumber') {
                            const
                                { houseNumber, street, district, city, state, country } = address,
                                text = [
                                    houseNumber,
                                    street,
                                    district,
                                    city,
                                    state,
                                    country,
                                ].filter(item => item && item.length).join(', '),
                                mod = $_.selectors[optionPresetMod] ? $_.selectors[optionPresetMod].replace('.','') : '',
                                $option = $(`<span class="autofill-option ${mod}">${text}</span>`).data('props', {
                                    locationId,
                                });

                            $autofillOptionsList.append($option);
                            itemsAdded++;
                        }
                    });

                    if (itemsAdded) $currentAutofill.addClass('_active').data('has-options', true);
                } else {
                    clear({ $currentAutofill, $autofillOptionsList, noResults: false });
                }
            }

            function search(obj) {
                const
                    { $currentAutofill, $autofillOptionsList, inputVal, dataRequestOptions, $scroll, dataProps } = obj,
                    { action, type, spec } = dataRequestOptions,
                    defaultAjaxParameters = {
                        url: action,
                        type: type,
                        data: {
                            text: inputVal
                        },
                    },
                    specificAjaxParameters = {
                        here: {
                            requestProps: {
                                url: `https://autocomplete.geocoder.ls.hereapi.com/6.2/suggest.json?apiKey=${$_.hereApiKey}&query=${inputVal}&beginHighlight=<span>&endHighlight=</span>`,
                                type: 'GET',
                            },
                            func: addHereOptions,
                        }
                    },
                    useParameters = spec ? specificAjaxParameters[spec].requestProps : defaultAjaxParameters,
                    useFunc = spec ? specificAjaxParameters[spec].func : addCategoryOptions;

                $.ajax(useParameters)
                    .done((data) => {
                        useFunc({ data, inputVal, $currentAutofill, $autofillOptionsList, dataProps });
                        $scroll.trigger('trigger:scroll-top', { onlyCurrent: true }).trigger('trigger:update-scroll');
                    })
                    .fail((error) => {
                        _errorHandler(error);
                    });
            }

            function clear(obj) {
                const
                    { $currentAutofill, $autofillOptionsList, noResults, $scroll } = obj,
                    dataNoResultText = $currentAutofill.data('no-result-text');

                $autofillOptionsList.html('');

                if (noResults && dataNoResultText) {
                    $autofillOptionsList.append(autofillTemplates.noResult({
                        text: dataNoResultText,
                        mod: '_show',
                    }));
                    $currentAutofill.addClass('_active');
                } else {
                    $currentAutofill.removeClass('_active');
                    $currentAutofill.data('has-options', false);
                }

                if ($scroll && $scroll.length) $scroll.trigger('trigger:scroll-top', { onlyCurrent: true }).trigger('trigger:update-scroll');
            }

            $_.$autofill.each((key, item) => {
                const
                    $currentAutofill = $(item),
                    $wrap = $currentAutofill.closest($_.$jsWrap),
                    $relatedButton = $wrap.find($_.$getCoordinatesButton),
                    $scroll = $currentAutofill.find($_.selectors.smoothScroll),
                    $relatedInput = $currentAutofill.find($_.$autofillInput),
                    $autofillOptionsList = $currentAutofill.find($_.$autofillOptionsList),
                    dataRequestOptions = $currentAutofill.data('request-options'),
                    dataProps = $currentAutofill.data('props');

                let timer = null,
                    lastVal = null;

                $currentAutofill.on('trigger:search', () => {
                    const inputVal = $relatedInput.val();
                    search({ $currentAutofill, $autofillOptionsList, inputVal, dataRequestOptions, $scroll });
                });

                $relatedInput.on('focus click', () => {
                    if ($currentAutofill.data('has-options')) $currentAutofill.addClass('_active');
                });

                $autofillOptionsList.on('click', '.autofill-option', (e) => {
                    const $currentTarget = $(e.currentTarget);

                    clearTimeout(timer);

                    if (!$currentTarget.is('a')) {
                        const
                            { locationId } = $currentTarget.data('props') || {},
                            optionText = $currentTarget.text().trim(),
                            inputText = $relatedInput.val().trim();

                        if ($relatedButton.length) $relatedButton.removeClass('_disable');

                        $relatedInput.data('id', locationId);
                        $relatedInput.data('text', inputText);

                        $relatedInput.val(optionText);
                        $currentAutofill.removeClass('_active');
                    }
                });

                $relatedInput.on('input change', () => {
                    clearTimeout(timer);

                    $_._resetInputData($relatedInput);

                    timer = setTimeout(() => {
                        const inputVal = $relatedInput.val();

                        if (inputVal.length) {
                            const searchProps = {
                                $currentAutofill,
                                $autofillOptionsList,
                                inputVal,
                                dataRequestOptions,
                                $scroll,
                                dataProps
                            };

                            if (lastVal) {
                                if (lastVal !== inputVal) {
                                    search(searchProps);
                                }
                            } else {
                                search(searchProps);
                            }
                        } else {
                            clear({ $currentAutofill, $autofillOptionsList, $scroll });
                        }

                        lastVal = inputVal;
                    }, 1000);
                });
            });
        },

        _resetInputData($input) {
            const
                $wrap = $input.closest($_.$jsWrap),
                $relatedButton = $wrap.find($_.$getCoordinatesButton);

            $relatedButton.addClass('_disable');
            $input.data('id', false);
            $input.data('text', false);
        },

        _getHighlightString(props) {
            const
                { text, replace } = props,
                from = text.toLowerCase().indexOf(replace.toLowerCase()),
                to = from + replace.length;

            return from !== -1 ? (
                text.substring(0, from) +
                `<span>${text.substring(from, to)}</span>` +
                text.substring(to, text.length)
            ) : false
        },
        
        _getAutofillDataBySelectedOption(e) {
            const
                $currentTarget = $(e.currentTarget),
                $parentAutofill = $currentTarget.closest($_.$autofill),
                $relatedInput = $parentAutofill.find($_.$autofillInput),
                { locationId } = $currentTarget.data('props'),
                dataText = $relatedInput.data('text');
            
            return {
                $currentTarget,
                $parentAutofill,
                $relatedInput,
                id: locationId,
                selectedAddress: $currentTarget.text().trim(),
                userSearch: dataText || $relatedInput.val().trim(), 
            }
        },

        _getCoordsByLocationId(props) {
            // MatchLevel: [country, state, county, city, district, street, intersection, houseNumber, postalCode, landmark]
            const { id, callback } = props;

            $.ajax({
                url: `https://geocoder.ls.hereapi.com/6.2/geocode.json?locationid=${id}&jsonattributes=1&gen=9&apiKey=${$_.hereApiKey}`,
                type: 'GET',
            })
            .done((data) => {
                const { matchLevel, location } = $_._getGeoResponseResult(data);

                if ( matchLevel === 'houseNumber') {
                    const { displayPosition, address } = location;

                    if (callback) callback({
                        displayPosition,
                        address,
                    });
                } else {
                    alert('Please specify the full address');
                }
            })
            .fail((error) => {
                _errorHandler(error);
            });
        },

        _getGeoResponseResult(data) {
            try {
                return data.response.view[0].result[0]
            } catch (e) {
                return {}
            }
        },

        _openMap(props) {
            const
                { displayPosition, selectedAddress, userSearch } = props,
                { latitude, longitude } = displayPosition,
                coords = {
                    lat: latitude,
                    lon: longitude,
                },
                setInputsValues = [
                    {
                        name: 'selectedAddress',
                        value: selectedAddress,
                    },
                    {
                        name: 'userSearch',
                        value: userSearch,
                    },
                ];

            if (!$_.$assessmentMap.hasClass('map-initialized')) {
                $_.$assessmentMap.attr('data-params', JSON.stringify({
                    center: coords,
                    initialMarker: true,
                    zoom: 15,
                    minZoom: 10,
                    controlsPosition: 'topright',
                }));

                $_.$body.trigger('trigger:init-map', $_.selectors.assessmentMap);
            } else {
                $_.$assessmentMap.trigger('trigger:set-center', coords);
            }

            $_.$body.trigger('trigger:open-popup', {
                target: 'assessment',
                show_overlay: true,
                set_inputs_value: setInputsValues,
                set_props_button_data: {
                    set_inputs_value: setInputsValues
                }
            });
        },
    };

    $(document).ready(() => {
        $_.init();
    });
});

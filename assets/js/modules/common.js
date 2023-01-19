jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            this.initMagic();
            this.initResizeTrigger();
            this.initBodyClickClose();
            this.initMenu();
            this.initScrollTopButton();
            this.initScrollEvents();
            this.initSmoothScroll();
            this.initSimpleScroll();
            this.initNavLinks();
            this.initLazyLoad();
            this.initTriggerSlider();
            this.initDefaultSlider();
            this.initBreakpointSlider();
            this.initToggleActive();
            this.initEstateGallerySlider();
            this.initStickyBlock();
            this.initConfCollapseForm();
            this.initShowMore();
            this.initSlideMenu();
            this.initFormatInput();
            this.initPrintListing();
            this.initPrintFavorites();
            this.initContentTabs();
            this.initAjaxSendButton();
            this.initFullSearch();
            this.initDropdownButton();
            this.initSvgMap();
            this.initToggleNext();
            this.initKeywordsInput();
            this.initClickPrevent();
            this.initTwinFields();
            this.initInputOnlyNumber();
            this.initMaxHeight();
            this.initMaxWidth();
            this.initCheckInWindow();
            this.initHideOnTargetReaching();
        },

        initCache() {
            this.selectors = {
                smoothScroll: '.js-smooth-scroll',
                simpleScroll: '.js-simple-scroll',
                lazyLoad: '.js-lazy',
                jsWrap: '.js-wrap',
                sliderNav: '.js-slider-nav',
                arrowLeft: '.js-arrow-left',
                arrowRight: '.js-arrow-right',
                current: '.js-current',
                total: '.js-total',
                estateGallerySliderImg: '.js-estate-gallery-slider-img',
                hiddenInput: '.js-hidden-input',
                addToFavorites: '.js-favorite-listing',
                ajaxSendButton: '.js-ajax-send-button',
                bcc: '.js-bcc',
                bccSibling: '.js-bcc-sibling',
                bccClose: '.js-bcc-close',
            };

            this.$page = $('html, body');
            this.$window = $(window);
            this.$body = $('body');

            this.$overlay = $('.js-overlay');
            this.$menuBtn = $('.js-menu-btn');
            this.$header = $('.js-header');
            this.$scrollTop = $('.js-scroll-top');
            this.$navLink = $('.js-nav-link');
            this.$checkInWindow = $('.js-check-in-window');

            this.$toggleActive = $('.js-toggle-active');

            this.$estateGallerySlider = $('.js-estate-gallery-slider');
            this.$estateGallerySliderImg = $(this.selectors.estateGallerySliderImg);

            this.$stickyContainer = $('.js-sticky-container');
            this.$stickyBlock = $('.js-sticky-block');

            this.$collapse = $('.js-collapse');
            this.$confCollapseForm = $('.js-conf-collapse-form');

            this.$showMoreButton = $('.js-show-more-btn');

            this.$map = $('#y-map');
            this.mapIsInit = false;

            this.$breakpointSlider = $('.js-bp-slider');
            this.$defaultSlider = $('.js-default-slider');
            this.$estateCard = $('.js-estate-card');

            this.$formatInput = $('.js-format-input');

            this.$contentTab = $('.js-content-tab');
            this.$contentTabNav = $('.js-content-tab-nav');
            this.$tabImg = $('.js-tab-img');

            this.$slideMenu = $('.js-slide-menu');
            this.$slideMenuItem = $('.js-slide-menu-item');
            this.$slideMenuWrap = $('.js-slide-menu-wrap');
            this.$slideMenuBtnLeft = $('.js-slide-menu-btn-left');
            this.$slideMenuBtnRight = $('.js-slide-menu-btn-right');
            this.$slideMenuLink = $('.js-slide-menu-link');

            this.$listingsTile = $('.js-listings-tile');
            this.$printListing = $('.js-print-listing');
            this.$printFavorites = $('.js-print-favorites');
            this.$listingPrintPopup = $('.js-listing-print-popup');
            this.$favoritesPrintPopup = $('.js-favorites-print-popup');
            this.$dataContent = $('.js-data-content');

            this.$fullSearch = $('.js-full-search');
            this.$fullSearchTypeLink = $('.js-full-search-type-link');
            this.$fullSearchTypeInput = $('.js-full-search-type');
            this.$fullSearchTab = $('.js-fs-tab');

            this.$dropdownButton = $('.js-dropdown-button');
            this.$dropdownSelected = $('.js-dropdown-selected');
            this.$dropdownDemography = $('.js-dropdown-demography');

            this.$svgMap = $('.js-svg-map');
            this.$svgMapLink = $('.js-svg-map-link');

            this.$toggleNext = $('.js-toggle-next');

            this.$keywords = $('.js-keywords');
            this.$keywordsArray = $('.js-keywords-array');
            this.$keywordsInsert = $('.js-keywords-insert');
            this.$addKeyword = $('.js-add-keyword');
            this.$keywordsList = $('.js-keywords-list');

            this.$twinFields = $('.js-twin-fields');

            this.$onlyNumbers = $('.js-only-numbers');

            this.$maxHeight = $('.js-max-height');
            this.$maxWidth = $('.js-max-width');
            this.$maxWidthContainer = $('.js-max-width-container');

            this.$formRow = $('.js-form-row');

            this.$hideOnTargetReaching = $('.js-hide-on-target-reaching');

            this.windowWidth = window.outerWidth;
            this.windowHeight = $_.$window.height();

            this.breakpoints = {
                b1700: 1700,
                b1500: 1500,
                b1300: 1300,
                b1200: 1200,
                b1000: 1000,
                b900: 900,
                b700: 700,
                b500: 500,
            };

            this.animationEvents = 'animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd';
        },

        initMagic () {
            document.addEventListener("touchstart", function(){}, true);

            if (!("ontouchstart" in document.documentElement)) {
                document.documentElement.className += " no-touch";
            }

            function is_touch_device() {
                return !!('ontouchstart' in window);
            }
            is_touch_device();
        },

        initInputOnlyNumber() {
            $_.$onlyNumbers.on('input keyup change', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    val = $currentTarget.val(),
                    fixVal = _getPureNumber(val);

                if (val !== fixVal) $currentTarget.val(fixVal).trigger('change');
            });
        },

        initTwinFields() {
            function getSelectOption(obj) {
                const { $wrap, name, val } = obj;
                return $wrap.find(`[data-name="${name}"]`).find(`[data-value="${val}"]`);
            }

            $_.$twinFields.each((key, item) => {
                const
                    $currentModule = $(item),
                    $relatedFields = $currentModule.find('input, select');

                $relatedFields.on('change', () => {
                    const
                        values = $relatedFields.serializeArray(),
                        { name: fistInputName, value: fistInputValue } = values[0],
                        { name: secondInputName, value: secondInputValue } = values[1];

                    if ((+fistInputValue && +secondInputValue) && (+fistInputValue > +secondInputValue)) {
                        if ($relatedFields.is('input')) {
                            const
                                firstInputVal = $relatedFields.eq(0).val(),
                                secondInputVal = $relatedFields.eq(1).val();

                            $relatedFields.eq(0).val(secondInputVal);
                            $relatedFields.eq(1).val(firstInputVal);
                        }

                        if ($relatedFields.is('select')) {
                            const
                                $setFirstSelectOption = getSelectOption({
                                    $wrap: $currentModule,
                                    name: fistInputName,
                                    val: secondInputValue,
                                }),
                                $setSecondSelectOption = getSelectOption({
                                    $wrap: $currentModule,
                                    name: secondInputName,
                                    val: fistInputValue,
                                });

                            if ($setFirstSelectOption.length && $setSecondSelectOption.length) {
                                $setFirstSelectOption.add($setSecondSelectOption).click();
                            }
                        }
                    }
                });
            });
        },

        initClickPrevent() {
            $_.$body.on('click', (e) => {
                const
                    $target = $(e.target),
                    targetIsPrevent = $target.hasClass('js-prevent'),
                    closestPrevent = $target.closest('.js-prevent');

                if (targetIsPrevent || closestPrevent.length) e.preventDefault();
            })
        },

        initKeywordsInput() {
            function pasteKeywords(obj) {
                const { keywords, $keywordsList } = obj;

                $keywordsList.html('');

                keywords.forEach(item => {
                    $keywordsList.append($(`<div class="keyword"><span class="text">${item}</span> <span class="remove"></span></div>`));
                });
            }

            function setValue(obj) {
                const
                    { $input, keywords } = obj,
                    val = keywords.length ? JSON.stringify(keywords) : '';

                $input.val(val).trigger('change');
            }

            function getKeywords($input) {
                const currentVal = $input.val();
                return currentVal.length ? _safeParseJson(currentVal) : [];
            }

            function addKeyword(props) {
                const
                    { $keywordsInsert, $keywordsArray, $keywordsList } = props,
                    keywords = getKeywords($keywordsArray),
                    addVal = $keywordsInsert.val().trim();

                if (addVal.length) {
                    addVal.split(',').forEach(item => {
                        const text = item.trim();

                        if (text.length && keywords.indexOf(text) === -1) {
                            keywords.push(text);
                            setValue({$input: $keywordsArray, keywords});
                            pasteKeywords({$keywordsList, keywords});
                        }
                    })
                }

                $keywordsInsert.val('');
            }

            $_.$keywords.each((key, item) => {
                const
                    $currentModule = $(item),
                    $keywordsArray = $currentModule.find($_.$keywordsArray),
                    $keywordsInsert = $currentModule.find($_.$keywordsInsert),
                    $addKeyword = $currentModule.find($_.$addKeyword),
                    $keywordsList = $currentModule.find($_.$keywordsList);

                $currentModule.on('click', '.remove', (e) => {
                    const
                        $currentTarget = $(e.currentTarget),
                        $relatedKeyword = $currentTarget.closest('.keyword'),
                        text = $relatedKeyword.find('.text').text(),
                        keywords = getKeywords($keywordsArray),
                        index = keywords.indexOf(text);

                    if (index !== -1) {
                        keywords.splice(index, 1);
                        setValue({ $input: $keywordsArray, keywords });
                        $relatedKeyword.fadeOut(300, () => {
                            $relatedKeyword.remove();
                        });
                    }
                });

                $keywordsInsert.on('keyup', (e) => {
                    if (e.keyCode === 13) addKeyword({ $keywordsInsert, $keywordsArray, $keywordsList });
                })

                $addKeyword.on('click', () => {
                    addKeyword({ $keywordsInsert, $keywordsArray, $keywordsList })
                });

                $keywordsArray.on('trigger:list:update', () => {
                    pasteKeywords({$keywordsList, keywords: getKeywords($keywordsArray)});
                });

                pasteKeywords({$keywordsList, keywords: getKeywords($keywordsArray)});
            });
        },

        initSimpleScroll() {
            function initScroll(el) {
                const
                    $currentTarget = $(el),
                    dataTriggerOnScroll = $currentTarget.data('trigger-on-scroll');

                const scroll = new SimpleBar(el, {
                    autoHide: false,
                    scrollbarMinSize: 100,
                });

                if (scroll && scroll.getScrollElement) {
                    const scrollElement = scroll.getScrollElement();

                    $currentTarget.on('trigger:scroll-top', () => {
                        scrollElement.scrollTop = 0;
                    });

                    if (dataTriggerOnScroll) {
                        scrollElement.addEventListener('scroll', () => {
                            $currentTarget.trigger(dataTriggerOnScroll);
                        });
                    }
                }
            }

            $($_.selectors.simpleScroll).each((key, el) => {
                initScroll(el);
            });
        },

        initToggleNext() {
            $_.$toggleNext.on('click', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    $next = $currentTarget.next(),
                    isActive = $currentTarget.hasClass('_active'),
                    nextIsHidden = $next.is(':hidden');

                if (isActive && !nextIsHidden) {
                    $currentTarget.removeClass('_active');
                    $next.stop().slideUp(300);
                }

                if (!isActive && nextIsHidden) {
                    $currentTarget.addClass('_active');
                    $next.stop().slideDown(300);
                }
            });

            $_.$body.on('body:resize:width', () => {
                $_.$toggleNext.removeClass('_active').next().attr('style', '');
            });
        },

        initSvgMap() {
            function filterByEventItemId($links, event) {
                return $links.filter(`[data-id="${$(event.currentTarget).data('id')}"]`);
            }

            $_.$body.on('trigger:init-svg-map', () => {
                $_.$svgMap.each((key, item) => {
                    const
                        $currentMap = $(item),
                        $citiesGroup = $currentMap.find('.js-cities-group'),
                        $cities = $citiesGroup.find('.js-svg-map-city'),
                        $links = $_.$svgMap.find($_.$svgMapLink);

                    $links.on('mouseenter', (e) => {
                        $links.removeClass('_active');
                        filterByEventItemId($cities, e).addClass('_active');
                    });

                    $links.on('mouseleave', (e) => {
                        filterByEventItemId($cities, e).removeClass('_active');
                    });

                    $links.on('trigger:click', (e) => {
                        location.href = e.currentTarget.href;
                    });

                    $cities.on('mouseenter', (e) => {
                        $links.removeClass('_active');
                        filterByEventItemId($links, e).addClass('_active');
                    });

                    $cities.on('mouseleave', (e) => {
                        filterByEventItemId($links, e).removeClass('_active');
                    });

                    $cities.on('click', (e) => {
                        filterByEventItemId($links, e).trigger('trigger:click');
                    });
                });
            });
        },

        initDropdownButton() {
            function toggleButton($btn, selected) {
                $btn[selected ? 'addClass' : 'removeClass']('_selected');
            }

            function getElementText($btn, valueObj) {
                const { name, value } = valueObj;

                if (value && value.length) {
                    const
                        $innerItem = $btn.find(`[data-name="${name}"]`),
                        dataText = ($innerItem.length ? $innerItem : $btn).find(`[data-value="${value}"]`).data('text');

                    return dataText || value;
                }

                return false;
            }

            function showSelected(data) {
                const
                    { $currentButton, $selectedContainer, $innerFields, dataProps } = data,
                    { patternSimple, patternTwin, patternMulti, patternReplace, patternFixed } = dataProps,
                    values = $innerFields.serializeArray();

                if (patternFixed) {
                    const hasValues = values.map(item => !!item.value.length).indexOf(true) !== -1;
                    $selectedContainer.html(patternFixed);
                    toggleButton($currentButton, hasValues);
                }

                if (patternSimple) {
                    const value = getElementText($currentButton, values[0]);
                    if (value) $selectedContainer.html(patternSimple.replace(patternReplace, value));
                    toggleButton($currentButton, !!value);
                }

                if (patternTwin) {
                    const
                        { first, last, both } = patternTwin,
                        fistInputValue = getElementText($currentButton, values[0]),
                        secondInputValue = getElementText($currentButton, values[1]);

                    if (fistInputValue && secondInputValue) {
                        $selectedContainer.html(
                            both
                                .replace(patternReplace, fistInputValue)
                                .replace(patternReplace, secondInputValue)
                        );
                    } else if (fistInputValue) {
                        $selectedContainer.html(first.replace(patternReplace, fistInputValue));
                    } else {
                        $selectedContainer.html(last.replace(patternReplace, secondInputValue));
                    }

                    toggleButton($currentButton, !!(fistInputValue || secondInputValue));
                }

                if (patternMulti) {
                    const { single, multi } = patternMulti;

                    if (values.length > 1) {
                        $selectedContainer.html(multi.replace(patternReplace, values.length));
                    } else if (values.length > 0) {
                        const value = getElementText($currentButton, values[0]);
                        if (value) $selectedContainer.html(single.replace(patternReplace, value));
                    }

                    toggleButton($currentButton, !!values.length);
                }
            }

            $_.$dropdownDemography.each((key, item) => {
                const $currentButton = $(item);

                $currentButton.on('click', (e) => {
                    let $currentTarget = $(e.currentTarget);
                    const buttonValue = $currentTarget.data('val');
                    let $dropdown = $(document).find(`#${buttonValue}-dropdown-select`)
                    if ($currentTarget.hasClass('_active') ) {
                       $dropdown.toggleClass('_active')
                    }

                    $_.$dropdownDemography.each((key, item) => {
                        const disableButtonValue = $(item).data('val');
                        let $dropdown = $(document).find(`#${disableButtonValue}-dropdown-select`)
                        if ($dropdown) {
                            if (disableButtonValue !== buttonValue) {
                                $dropdown.removeClass('_active')
                            }
                        }
                    })
                })
            })
            $_.$dropdownButton.each((key, item) => {
                const
                    $currentButton = $(item),
                    $selectedContainer = $currentButton.find($_.$dropdownSelected),
                    $innerFields = $currentButton.find('input, select'),
                    dataProps = $currentButton.data('props');

                if (dataProps) {
                    const props = {
                        $currentButton,
                        $selectedContainer,
                        $innerFields,
                        dataProps,
                    };

                    $currentButton.on('trigger:reset', () => {
                        showSelected(props);
                    });

                    $innerFields.on('change', () => {
                        showSelected(props);
                    });

                    showSelected(props);
                }
            });
        },

        initMaxWidth() {
            function setMaxWidth($block, $wrap) {
                $block.css('max-width', $wrap.width());
            }

            $_.$maxWidth.each((key, item) => {
                const
                    $currentItem = $(item),
                    $wrap = $currentItem.closest($_.$maxWidthContainer);

                if ($wrap.length) {
                    setMaxWidth($currentItem, $wrap);

                    $_.$body.on('body:resize', () => {
                        setMaxWidth($currentItem, $wrap);
                    });
                }
            });
        },

        initMaxHeight() {
            function setMaxHeight(props) {
                const
                    { $item, settings={} } = props,
                    { stopBp=0 } = settings,
                    enableMaxHeight = $_.windowWidth > stopBp;

                if (enableMaxHeight) {
                    const
                        { stickyOffset } = props,
                        { offset=0, fromPosition=false } = settings,
                        { top } = $item[0].getBoundingClientRect(),
                        { height: headerHeight } = $_.$header[0].getBoundingClientRect(),
                        totalHeightOffset = fromPosition ? top : headerHeight + stickyOffset;

                    $item.css('max-height', `${$_.windowHeight - totalHeightOffset - offset}px`);
                    $_.$body.trigger('body:trigger:init:scrollbars');
                } else {
                    $item.attr('style', '');
                }
            }

            $_.$maxHeight.each((key, item) => {
                const
                    $currentItem = $(item),
                    $parentStickyBlock = $currentItem.closest($_.$stickyBlock),
                    settings = $currentItem.data('mh-settings'),
                    { refreshOnScroll } = settings,
                    props = {
                        $item: $currentItem,
                        settings: settings,
                        stickyOffset: $parentStickyBlock.length ? ($parentStickyBlock.data('offset') || 0) : 0,
                    };

                setMaxHeight(props);

                $_.$body.on('body:resize', () => {
                    setMaxHeight(props);
                });

                if (refreshOnScroll) {
                    $_.$window.on('scroll', () => {
                        setMaxHeight(props);
                    });
                }
            });
        },

        initFullSearch() {
            $_.$fullSearch.each((key, item) => {
                const
                    $fullSearch = $(item),
                    $relatedTypeLinks = $fullSearch.find($_.$fullSearchTypeLink),
                    $relatedTypeInput = $fullSearch.find($_.$fullSearchTypeInput),
                    $relatedTabs = $fullSearch.find($_.$fullSearchTab);

                $relatedTypeLinks.on('click', (e) => {
                    const
                        $currentTarget = $(e.currentTarget),
                        $dataVal = $currentTarget.data('val'),
                        $dataToggle = $currentTarget.data('toggle') || 'default',
                        $matchedTabs = $relatedTabs.filter(`[class*="-${$dataToggle}"]`);

                    $relatedTabs.addClass('_hide');
                    $matchedTabs.removeClass('_hide');
                    $relatedTypeInput.attr('value', $dataVal);

                    $_.$body.trigger('body:trigger:init:scrollbars');
                });
            })
        },

        initContentTabs() {
            function switchTabs($wrap, dataContentId) {
                const
                    $tabImages = $wrap.find($_.$tabImg),
                    $relatedTab = $wrap.find($_.$contentTab).filter(`[data-content-id="${dataContentId}"]`);

                $relatedTab.addClass('_active').siblings().not($relatedTab).removeClass('_active');

                if ($tabImages.length) {
                    $tabImages.each((key, item) => {
                        const
                            $item = $(item),
                            dataSrc = $item.data('src');

                        $item.attr('src', dataSrc);
                    });
                }
            }

            $_.$contentTabNav.on('click', (e) => {
                const
                    $currentLink = $(e.currentTarget),
                    dataContentId = $currentLink.data('content-id'),
                    dataInitMap = $currentLink.data('init-map'),
                    dataTrigger = $currentLink.data('trigger'),
                    $wrap = $currentLink.closest($_.selectors.jsWrap);

                if (dataInitMap) {
                    $_.$body.trigger('trigger:init-map', dataInitMap);
                    $currentLink.trigger('trigger:click');
                }

                if (dataTrigger) {
                    const { target, event } = dataTrigger;
                    $(target).trigger(event);
                }

                if ($.isArray(dataContentId)) {
                    dataContentId.forEach(item => switchTabs($wrap, item));
                } else {
                    switchTabs($wrap, dataContentId);
                }
            });
        },

        initPrintFavorites() {
            if (!$_.$printFavorites.length) return false;

            $_.$printFavorites.on('click', () => {
                $_.$favoritesPrintPopup.html($_.$listingsTile.clone());
                window.print();
            });

            window.onbeforeprint = () => {
                $_.$body.addClass('_print _print-favorites');
            };

            window.onafterprint = () => {
                $_.$body.removeClass('_print _print-favorites');
            };
        },

        initPrintListing() {
            if (!$_.$printListing.length) return false;

            let firstLoad = true;

            $_.$printListing.on('click', () => {
                const $map = $('#listing-print-map');

                if (firstLoad) {
                    firstLoad = false;

                    $map.on('trigger:open-street-map-lite-loaded', () => {
                        setTimeout(() => {
                            window.print();
                        }, 1000);
                    });
                } else {
                    window.print();
                }

                pasteContent();
            });

            window.onbeforeprint = () => {
                $_.$body.addClass('_print _print-listing');
            };

            window.onafterprint = () => {
                $_.$body.removeClass('_print _print-listing');
            };

            function pasteContent() {
                const $dataContentItems = $_.$listingPrintPopup.find($_.$dataContent);

                $dataContentItems.each((key, item) => {
                    const
                        $currentItem = $(item),
                        dataContent = $currentItem.data('content'),
                        dataSrc = $currentItem.data('src');

                    if (typeof dataContent !== "undefined") $currentItem.html(dataContent);
                    if (typeof dataSrc !== "undefined") $currentItem.attr('src', dataSrc);
                });

                $_.$body.trigger('trigger:init-map', '#listing-print-map');
            }
        },

        initAjaxSendButton() {
            const ajaxCallbacks = {
                toggle_active: ($btn) => {
                    $btn.toggleClass('_active');
                },
                toggle_active_s: ($btn) => {
                    $btn.toggleClass('_active').siblings().removeClass('_active');
                },
                remove_wrap: ($btn) => {
                    $btn.closest($_.selectors.jsWrap).remove();
                },
            }

            function clickHandler(props) {
                const { $btn, requestParameters, callback } = props;

                if (requestParameters) {
                    $.ajax(requestParameters).done(() => {
                        if (callback && ajaxCallbacks[callback]) {
                            ajaxCallbacks[callback]($btn);
                        }
                    })
                }
            }

            $_.$body.on('click', $_.selectors.ajaxSendButton, (e) => {
                e.preventDefault();

                const
                    $currentTarget = $(e.currentTarget),
                    url = $currentTarget.data('url'),
                    requestProps = $currentTarget.data('request-props') || {},
                    requestParameters = url ? {
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        ...requestProps
                    } : false,
                    props = {
                        requestParameters: requestParameters,
                        callback: $currentTarget.data('callback'),
                        $btn: $currentTarget,
                    };

                _authenticationRequiredRequest({
                    customAuthenticatedRequest: () => { clickHandler(props) },
                    requestParameters,
                });
            });
        },

        initFormatInput() {
            $_.$formatInput.each((key, item) => {
                const
                    $currentInput = $(item),
                    dataFormatParams = $currentInput.data('format-props');

                $currentInput.val(_formatInputVal({
                    dataFormatParams,
                    val: $currentInput.val(),
                })).trigger('trigger:formatted');

                $currentInput.on('change', () => {
                    $currentInput.val(_formatInputVal({
                        dataFormatParams,
                        val: $currentInput.val(),
                    })).trigger('trigger:formatted');
                });

                $currentInput.on('trigger:set-val', (e, data) => {
                    const { val, change=false } = data;

                    $currentInput.val(_formatInputVal({
                        dataFormatParams,
                        val,
                    })).trigger('trigger:formatted');

                    if (change) $currentInput.change();
                });
            });
        },

        initSlideMenu() {
            function moveMenu(params) {
                const { fixOffset, $currentMenu, $relatedWrap } = params;

                $currentMenu.stop(true, true).animate({'left': -fixOffset}, 300, () => {
                    checkDrag({
                        $currentMenu,
                        $relatedWrap,
                    });
                });
            }

            function checkDrag(params) {
                const
                    { $currentMenu, $relatedWrap, ui } = params,
                    { right: wrapRight, left: wrapLeft, width: wrapWidth } = $relatedWrap[0].getBoundingClientRect(),
                    { right: menuRight, left: menuLeft, width: menuWidth } = $currentMenu[0].getBoundingClientRect(),
                    widthDiff = Math.min(wrapWidth - menuWidth, 0);

                $relatedWrap[menuRight - 1 > wrapRight ? 'removeClass' : 'addClass']('_end');
                $relatedWrap[menuLeft + 1 >= wrapLeft ? 'addClass' : 'removeClass']('_start');

                if (ui && ui.position) {
                    ui.position.left = Math.min(0, ui.position.left);
                    ui.position.left = Math.max(widthDiff, ui.position.left);
                }
            }

            function bindLeftButton(params) {
                const { $currentMenu, $relatedWrap, $relatedMenuItems, $relatedButtonLeft } = params;

                $relatedButtonLeft.on('click', () => {
                    const
                        { left: wrapLeft } = $relatedWrap[0].getBoundingClientRect(),
                        { left: menuLeft } = $currentMenu[0].getBoundingClientRect();

                    $relatedMenuItems.each((key, item) => {
                        const
                            $item = $(item),
                            { left: itemLeft } = item.getBoundingClientRect(),
                            { left: nextItemLeft } = $item.next()[0].getBoundingClientRect();

                        if (nextItemLeft >= wrapLeft) {
                            const
                                diffItemLeft = wrapLeft - itemLeft,
                                diffWrapsLeft = wrapLeft - menuLeft,
                                paddingRight =  parseInt($item.css('padding-right')),
                                offset = diffWrapsLeft - diffItemLeft - $relatedButtonLeft.width() - paddingRight,
                                fixOffset = Math.max(offset, 0);

                            if (fixOffset === 0) $relatedWrap.addClass('_start');
                            moveMenu({ fixOffset, $currentMenu, $relatedWrap });
                            return false;
                        }
                    });
                }).addClass('_init');
            }

            function bindRightButton(params) {
                const { $currentMenu, $relatedWrap, $relatedMenuItems, $relatedButtonRight } = params;

                $relatedButtonRight.on('click', () => {
                    const
                        { right: wrapRight, left: wrapLeft, width: wrapWidth } = $relatedWrap[0].getBoundingClientRect(),
                        { left: menuLeft, width: menuWidth } = $currentMenu[0].getBoundingClientRect(),
                        widthDiff = menuWidth - wrapWidth;

                    $relatedMenuItems.each((key, item) => {
                        const { right: itemRight } = item.getBoundingClientRect();

                        if (itemRight > wrapRight) {
                            const
                                diffItemRight = itemRight - wrapRight,
                                diffWrapsLeft = menuLeft - wrapLeft,
                                offset = diffWrapsLeft - diffItemRight - $relatedButtonRight.width(),
                                fixOffset = Math.min(Math.abs(offset), Math.abs(widthDiff));

                            if (fixOffset === widthDiff) $relatedWrap.addClass('_end');
                            moveMenu({ fixOffset, $currentMenu, $relatedWrap });
                            return false;
                        }
                    });
                }).addClass('_init');
            }

            $_.$slideMenu.each((key, item) => {
                const
                    $currentMenu = $(item),
                    $relatedWrap = $currentMenu.closest($_.$slideMenuWrap),
                    $relatedMenuItems = $relatedWrap.find($_.$slideMenuItem),
                    $relatedButtonLeft = $relatedWrap.find($_.$slideMenuBtnLeft),
                    $relatedButtonRight = $relatedWrap.find($_.$slideMenuBtnRight);

                $currentMenu.draggable({
                    axis: 'x',
                    create: () => {
                        checkDrag({
                            $currentMenu,
                            $relatedWrap,
                        })

                        bindLeftButton({
                            $currentMenu,
                            $relatedWrap,
                            $relatedMenuItems,
                            $relatedButtonLeft,
                        });

                        bindRightButton({
                            $currentMenu,
                            $relatedWrap,
                            $relatedMenuItems,
                            $relatedButtonRight,
                        });
                    }
                })
                    .on('drag', ( event, ui ) => {
                        checkDrag({
                            $currentMenu,
                            $relatedWrap,
                            ui,
                        });
                    });

                $_.$body.on('body:resize:width', () => {
                    checkDrag({
                        $currentMenu,
                        $relatedWrap,
                    });
                });
            });

            $_.$slideMenuLink.on('click', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    $relatedMenu = $currentTarget.closest($_.$slideMenu),
                    $relatedLinks = $relatedMenu.find($_.$slideMenuLink);

                $relatedLinks.removeClass('_active');
                $currentTarget.addClass('_active');
            });
        },

        initStickyBlock() {
            function setSticky($currentStickyBlock, $relatedStickyContainer) {
                const
                    { top: wrapTop, bottom: wrapBottom } = $relatedStickyContainer[0].getBoundingClientRect(),
                    { height: blockHeight } = $currentStickyBlock[0].getBoundingClientRect(),
                    { height: headerHeight } = $_.$header[0].getBoundingClientRect(),
                    offset = headerHeight + 20;

                if (wrapBottom <= (blockHeight + offset)) {
                    $currentStickyBlock.attr('style', '');
                    $currentStickyBlock.removeClass('_stick-to-top').addClass('_stick-to-bottom');
                } else if (wrapTop <= offset) {
                    $currentStickyBlock.css('top', offset);
                    $currentStickyBlock.removeClass('_stick-to-bottom').addClass('_stick-to-top');
                } else {
                    $currentStickyBlock.attr('style', '');
                    $currentStickyBlock.removeClass('_stick-to-bottom _stick-to-top');
                }
            }

            function setStickyTop($currentStickyBlock) {
                const
                    { height: headerHeight } = $_.$header[0].getBoundingClientRect(),
                    dataOffset = $currentStickyBlock.data('offset') || 0,
                    offset = headerHeight + dataOffset;

                $currentStickyBlock.css('top', offset);
                $currentStickyBlock.removeClass('_stick-to-bottom').addClass('_stick-to-top');
            }

            $_.$stickyBlock.each((key, item) => {
                const
                    $currentStickyBlock = $(item),
                    $relatedStickyContainer = $currentStickyBlock.closest($_.$stickyContainer);

                $_.$window.on('scroll', () => {
                    setSticky($currentStickyBlock, $relatedStickyContainer);
                });

                $currentStickyBlock.on('trigger:update', () => {
                    setSticky($currentStickyBlock, $relatedStickyContainer);
                });

                $currentStickyBlock.on('trigger:set-sticky-top', () => {
                    setStickyTop($currentStickyBlock);
                });

                setSticky($currentStickyBlock, $relatedStickyContainer);
            });
        },

        initConfCollapseForm() {
            $_.$confCollapseForm.each((key, item) => {
                const
                    $currentForm = $(item),
                    $innerCollapseBlock = $currentForm.find($_.$collapse),
                    $innerInputs = $currentForm.find('input[type="text"]').not($_.selectors.hiddenInput).not('.js-exclude'),
                    $parentStickyBlock = $currentForm.closest($_.$stickyBlock);

                $currentForm.on('change', (e) => {
                    if (!$(e.target).is($innerInputs)) return false;

                    const
                        values = $innerInputs.map((key, item) => item.value.length > 0).toArray(),
                        method = values.indexOf(true) !== -1 ? 'slideDown' : 'slideUp',
                        props = {
                            duration: 300,
                            complete: () => $_.$body.trigger('body:trigger:init:scrollbars'),
                        };

                    if ($parentStickyBlock.length) props.step = () => {
                        $parentStickyBlock.trigger('trigger:update');
                        $_.$body.trigger('body:trigger:init:scrollbars', {
                            preventDestroy: true,
                        });
                    };

                    $innerCollapseBlock[method](props);
                });
            });
        },

        initShowMore() {
            const $btn = $_.$showMoreButton,
                $hiddenElements = $btn.prevAll(':hidden'),
                show = $hiddenElements.length;

            if (!show) $_.$showMoreButton.hide();

            $_.$showMoreButton.on('click', (e) => {
                const
                    $wrap = $btn.closest($_.selectors.jsWrap),
                    $stickyBlocks = $wrap.find($_.$stickyBlock),
                    $elementsToToggle = show ? $hiddenElements : $btn.prevAll().filter(
                        (key, item) => $(item).data('showed')
                    ),
                    animationsProps = {
                        duration: 300,
                    };

                if ($stickyBlocks.length && !show) animationsProps.step = (step, data) => {
                    if ($elementsToToggle.eq(0).is(data.elem)) $stickyBlocks.trigger('trigger:update');
                }

                if ($stickyBlocks.length && show) {
                    $stickyBlocks.trigger('trigger:set-sticky-top');
                    animationsProps.complete = () => $stickyBlocks.trigger('trigger:update');
                }

                if (show) {
                    $elementsToToggle.each((key, item) => {
                        const
                            $item = $(item),
                            isAnimated = $item.hasClass('_animate');

                        if (!isAnimated) {
                            const delay = `${($elementsToToggle.length - key)*100 + 300}ms`;

                            $item.css('animation-delay', delay).addClass('_animate');
                        }
                    })
                }

                $elementsToToggle.slideToggle(animationsProps).data('showed', !!show);
                $btn.toggleClass('_active');
            });

            $_.$body.on('body:resize:width', () => {
                $_.$showMoreButton.removeClass('_active').prevAll().attr('style', '').data('showed', false);
            });
        },

        initEstateGallerySlider() {
            function initSlider($slider) {
                const
                    dataLazyInner = $slider.data('lazy-inner'),
                    dataScrollNav = $slider.data('scroll-nav'),
                    { $arrowLeft, $arrowRight, $current, $total } = $_._getRelatedSliderNav($slider),
                    dots = $_.windowWidth <= $_.breakpoints.b1000;

                $slider.on('init', (event, slick) => {
                    if ($current.length && $total.length) $_._initSliderCounter(slick, $current, $total);
                    if (dataLazyInner) $_._initSliderLazyInner(slick);
                    if (dataScrollNav) $_._initSliderScrollNav($slider);
                    if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                })
                    .slick({
                        lazyLoad: 'ondemand',
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: !dots,
                        prevArrow: $arrowLeft.eq(0),
                        nextArrow: $arrowRight.eq(0),
                        fade: true,
                        speed: 150,
                        infinite: false,
                        dots: dots,
                    });
            }

            function constructSlider(obj) {
                const
                    { $currentSlider, $imagesCache } = obj,
                    isInit = $currentSlider.hasClass('slick-initialized'),
                    dataImgCount = $currentSlider.data('img-count'),
                    imgCount = getNumberOfSlideInnerImages();

                if (!isInit) {
                    $currentSlider.data('img-count', imgCount);
                    addImgWrappers({ $currentSlider, $imagesCache, imgCount });
                    initSlider($currentSlider);
                } else {
                    if (dataImgCount !== imgCount) {
                        $currentSlider.data('img-count', imgCount);
                        resetSlider($currentSlider);
                        addImgWrappers({ $currentSlider, $imagesCache, imgCount });
                        initSlider($currentSlider);
                    }
                }
            }

            function addImgWrappers(obj) {
                const
                    { $currentSlider, $imagesCache, imgCount } = obj,
                    baseClass = $currentSlider[0].classList[0],
                    slides = [];

                let imgCounter = 0;

                for (let i = 0; i < Math.ceil($imagesCache.length/imgCount); i++) {
                    slides.push(`
                    <div class="${baseClass}__item">
                        <div class="${baseClass}__container">
                            ${$imagesCache.slice(i*imgCount, i*imgCount + imgCount).toArray().map(
                        item => {
                            item.setAttribute('data-index', imgCounter++);
                            return item.outerHTML
                        }
                    ).join('')}
                        </div>
                    </div>
                `)
                }

                $currentSlider.html(slides);
            }

            function resetSlider($slider) {
                $slider.slick('unslick');
                $slider.off('init');
                $slider.off('beforeChange');
                $slider.html('');
            }

            function getNumberOfSlideInnerImages() {
                return $_.windowWidth <= $_.breakpoints.b1000 ? 1 : ($_.windowWidth <= $_.breakpoints.b1300 ? 3 : 5);
            }

            $_.$estateGallerySlider.each((key, item) => {
                const
                    $currentSlider = $(item),
                    $imagesCache = $currentSlider.find($_.$estateGallerySliderImg).clone(),
                    fullSizesArray = $imagesCache.map((key, item) => $(item).data('full-size')).toArray();

                $currentSlider.on('click', $_.selectors.estateGallerySliderImg, (e) => {
                    const
                        $currentImg = $(e.currentTarget),
                        index = $currentImg.data('index');

                    $_.$body.trigger('trigger:init-popup-slider', {
                        images: fullSizesArray,
                        index,
                        sliderProps: {
                            accessibility: true
                        }
                    });
                });

                constructSlider({ $currentSlider, $imagesCache });

                $_.$body.on('body:resize:width', () => {
                    constructSlider({ $currentSlider, $imagesCache });
                });
            });
        },

        initToggleActive() {
            $_.$toggleActive.on('click', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    dataToggleJsWrap = $currentTarget.data('toggle-js-wrap');

                $currentTarget.toggleClass('_active');

                if (dataToggleJsWrap) {
                    $currentTarget.closest($_.selectors.jsWrap).toggleClass('_active');
                }
            })
        },

        initTriggerSlider() {
            $_.$body.on('trigger:init-slider', (e, obj) => {
                const { $sliders, $slides, sliderParams={} } = obj;

                if ($sliders.length) {
                    $sliders.each((key, item) => {
                        const
                            $currentSlider = $(item),
                            dataScrollNav = $currentSlider.data('scroll-nav'),
                            dataFocus = $currentSlider.data('focus'),
                            dataSliderParams = $currentSlider.data('slider-parameters'),
                            isInit = $currentSlider.hasClass('slick-initialized'),
                            hasRelatedSlides = $slides && $slides.length && $slides[key];

                        if (hasRelatedSlides) {
                            if (isInit) $currentSlider.slick('unslick');
                            $currentSlider.off('init');
                            $currentSlider.off('beforeChange');
                            $currentSlider.off('breakpoint');
                            $currentSlider.html($slides[key]);
                        }

                        if (!isInit || (isInit && hasRelatedSlides)) {
                            const
                                dataLazyInner = $currentSlider.data('lazy-inner'),
                                { $arrowLeft, $arrowRight, $current, $total } = $_._getRelatedSliderNav($currentSlider);

                            $currentSlider.on('breakpoint', (event, slick) => {
                                if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                            });

                            $currentSlider
                                .on('init', (event, slick) => {
                                    $_.$body.trigger('update:lazy-load');
                                    if ($current.length && $total.length) $_._initSliderCounter(slick, $current, $total);
                                    if (dataScrollNav) $_._initSliderScrollNav($currentSlider);
                                    if (dataLazyInner) $_._initSliderLazyInner(slick);
                                    if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                                    if (dataFocus) slick.$list.attr('tabindex', 0).focus();
                                })
                                .slick({
                                    slidesToShow: 1,
                                    slidesToScroll: 1,
                                    arrows: true,
                                    prevArrow: $arrowLeft.eq(0),
                                    nextArrow: $arrowRight.eq(0),
                                    fade: false,
                                    infinite: false,
                                    dots: false,
                                    ...sliderParams,
                                    ...dataSliderParams
                                });
                        }
                    });
                }
            });
        },

        initLazyLoad() {
            function setLazyLoad() {
                $($_.selectors.lazyLoad).Lazy({
                    scrollDirection: 'vertical',
                    threshold: window.innerHeight / 2,
                    visibleOnly: true,
                    afterLoad: (element) => {
                        const
                            $currentEl = $(element),
                            hasObjectFit = $currentEl.hasClass('js-object-fit');

                        if (hasObjectFit) objectFitPolyfill($currentEl);
                    },

                    // data-loader="inlineSvg" data-src="path/name.svg"
                    inlineSvg: (element) => {
                        const
                            dataTrigger = element.data('trigger'),
                            dataSrc = element.data('src');

                        element.load(dataSrc, () => {
                            const reInitCIW = element.find('.js-check-in-window').length;

                            if (reInitCIW) $_.$checkInWindow = $('.js-check-in-window');
                            if (dataTrigger) setTimeout(() => {$_.$body.trigger(dataTrigger)}, 500);
                        });
                    },
                });
            }

            $_.$body.on('update:lazy-load', () => {
                setLazyLoad();
            });

            setLazyLoad();
        },

        initDefaultSlider() {
            $_.$defaultSlider.each((key, item) => {
                const
                    $currentSlider = $(item),
                    $estateCard = $currentSlider.find($_.$estateCard),
                    $preventChild = $currentSlider.find('[data-prevent-parent-swipe]'),
                    dataScrollNav = $currentSlider.data('scroll-nav'),
                    dataLazyInner = $currentSlider.data('lazy-inner'),
                    dataParameters = $currentSlider.data('slider-parameters') || {},
                    { $arrowLeft, $arrowRight, $current, $total } = $_._getRelatedSliderNav($currentSlider);

                $currentSlider.on('breakpoint', (event, slick) => {
                    if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                });

                $currentSlider
                    .on('init', (event, slick) => {
                        $_.$body.trigger('update:lazy-load');
                        if ($current.length && $total.length) $_._initSliderCounter(slick, $current, $total);
                        if ($preventChild.length) $_._preventParentSliderSwipe($currentSlider, $preventChild);
                        if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                        if (dataScrollNav) $_._initSliderScrollNav($currentSlider);
                        if (dataLazyInner) $_._initSliderLazyInner(slick);
                    })
                    .slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: true,
                        prevArrow: $arrowLeft.eq(0),
                        nextArrow: $arrowRight.eq(0),
                        fade: false,
                        infinite: false,
                        dots: false,
                        ...dataParameters
                    });
            });
        },

        initBreakpointSlider() {
            function init() {
                $_.$breakpointSlider.each((key, item) => {
                    const
                        $currentSlider = $(item),
                        dataBreakpoint = $currentSlider.data('breakpoint') || 1,
                        dataParameters = $currentSlider.data('slider-parameters') || {},
                        isInit = $currentSlider.hasClass('slick-initialized'),
                        enableSlider = $_.windowWidth <= dataBreakpoint;

                    if (!enableSlider && isInit) {
                        $currentSlider.slick('unslick');
                    }

                    if (enableSlider && !isInit) {
                        $currentSlider
                            .on('init', (event, slick) => {
                                if (slick.$dots) $_._initSliderDotsNav({slick, dotsCount: 5});
                            })
                            .slick({
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: false,
                                fade: false,
                                infinite: false,
                                dots: true,
                                ...dataParameters
                            });
                    }
                });
            }

            $_.$body.on('body:resize:width', () => {
                init();
            });

            init();
        },

        _initSliderScrollNav($slider) {
            $slider.on('mousewheel', (e) => {
                e.stopPropagation();
                e.preventDefault();

                const
                    { wheelDelta, deltaY, detail } = e.originalEvent,
                    scrollUp = (wheelDelta > 0) || (detail < 0) || (deltaY < 0),
                    sliderSwitchDirection = scrollUp ? 'slickPrev' : 'slickNext';

                $slider.slick(sliderSwitchDirection);
            });
        },

        _initSliderLazyInner(slick) {
            const
                { $slider, $slides } = slick,
                innerSliderSelector = $slider.data('inner-slider-selector'),
                dataImgSelector = $slider.data('img-selector');

            $_._initSliderInner({
                fromIndex: 0,
                slick,
                $slides,
                props: {
                    dataImgSelector,
                    innerSliderSelector,
                }
            });

            $slider.on('beforeChange', (event, slick, currentSlide, nextSlide) => {
                $_._initSliderInner({
                    fromIndex: nextSlide,
                    slick,
                    $slides,
                    props: {
                        dataImgSelector,
                        innerSliderSelector,
                    }
                });
            });
        },

        _initSliderInner(data) {
            const { slick, fromIndex, $slides, props } = data;

            for (let i = fromIndex; i < (fromIndex + slick.options.slidesToShow * 2); i++) {
                const $slide = $slides.eq(i);

                if ($slide.length) $_._initSliderInnerContent({
                    $slide,
                    ...props
                });
            }
        },

        _initSliderInnerContent(data) {
            const { $slide, dataImgSelector, innerSliderSelector } = data;

            if (dataImgSelector) {
                const $images = $slide.find(`[data-${dataImgSelector}]`);

                $images.each((key, item) => {
                    const $item = $(item);
                    $item.attr('src', $item.data(dataImgSelector));
                });
            }

            if (innerSliderSelector) {
                const $innerSliders = $slide.find(innerSliderSelector);

                $_.$body.trigger('trigger:init-slider', {
                    $sliders: $innerSliders
                })
            }
        },

        initSmoothScroll() {
            function setScrollBars(data) {
                const
                    $scrollbar = $($_.selectors.smoothScroll),
                    { preventDestroy } = data || {};

                $scrollbar.each((key, item) => {
                    const
                        $item = $(item),
                        breakpointEnable = $item.data('scroll-enable-bp'),
                        breakpointDisable = $item.data('scroll-disable-bp'),
                        dataInitIfOverflow = $item.data('init-if-overflow'),
                        isInit = Scrollbar.has(item);

                    if (dataInitIfOverflow) {
                        const { clientHeight, scrollHeight } = item;

                        if (scrollHeight > clientHeight) {
                            init(item, isInit);
                        } else {
                            if (isInit && !preventDestroy) destroy($item);
                        }
                    } else {
                        if (breakpointEnable || breakpointDisable) {
                            const
                                enable = breakpointEnable ? ($_.windowWidth <= breakpointEnable) : true,
                                disable = breakpointDisable ? ($_.windowWidth <= breakpointDisable) : false;

                            if (enable && !disable) {
                                init(item, isInit);
                            } else {
                                if (isInit) destroy($item);
                            }
                        } else {
                            init(item, isInit);
                        }
                    }
                });
            }

            function destroy($item) {
                $item.off('trigger:scroll-top');
                $item.off('trigger:update-scroll');
                $item.removeClass('_scroll-initialized');
                Scrollbar.destroy($item[0])
                $item.removeAttr('data-scrollbar');
            }

            function init(item, update) {
                const
                    $item = $(item),
                    $form = $item.find('form'),
                    $inputs = $form.find('input');

                if (update) {
                    Scrollbar.get(item).update();
                } else {
                    const
                        dataScrollOptions = $item.data('scroll-options') || {},
                        dataTriggerOnScroll = $item.data('trigger-on-scroll'),
                        dataScrollContentWrap = $item.data('scroll-content-wrap');

                    const scrollbar = Scrollbar.init(item, {
                        damping: 0.1,
                        thumbMinSize: 50,
                        alwaysShowTracks: true,
                        continuousScrolling: false,
                        ...dataScrollOptions
                    });

                    if (dataScrollContentWrap) {
                        $item.find('.scroll-content').wrap(`<div class="scroll-content-wrap"></div>`);
                    }

                    if (dataTriggerOnScroll) {
                        scrollbar.addListener((status) => {
                            $item.trigger(dataTriggerOnScroll);
                        });
                    }

                    $inputs.on('focus', (e) => {
                        const
                            $currentInput = $(e.currentTarget),
                            $closestFormRow = $currentInput.closest($_.$formRow);

                        if ($closestFormRow.length) {
                            const rowTop = $closestFormRow.position().top - scrollbar.scrollTop;

                            // const
                            //     rowBottom = rowTop + $closestFormRow.height(),
                            //     { height: scrollHeight } = item.getBoundingClientRect();

                            // if (rowBottom > scrollHeight) {
                            //     scrollbar.scrollTo(0, scrollbar.scrollTop + rowTop, 600);
                            // }

                            scrollbar.scrollTo(0, scrollbar.scrollTop + rowTop, 600)
                        }
                    });

                    $item.addClass('_scroll-initialized');

                    $item.on('trigger:scroll-top', (e, props={}) => {
                        const { onlyCurrent } = props;
                        if (!onlyCurrent || $(e.target).is($item)) scrollbar.scrollTop = 0;
                    });

                    $item.on('trigger:update-scroll', () => {
                        Scrollbar.get(item).update();
                    });
                }
            }

            $_.$body.on('body:resize', () => {
                setScrollBars();
            });

            $_.$body.on('body:trigger:init:scrollbars', (e, data) => {
                setScrollBars(data);
            });

            $_.$body.on('trigger:init-scrollbar', (e, data) => {
                const { el } = data;

                init(el);
            });

            setScrollBars();
        },

        initScrollTopButton () {
            if (!$_.$scrollTop.length) return true;

            function check() {
                const
                    windowHeight = $_.$window.height(),
                    pageOffsetTop = $(document).scrollTop(),
                    pageOffsetBottom = pageOffsetTop + windowHeight,
                    footerOffsetTop = $('.footer').offset().top,
                    firstScreenScrolled = pageOffsetTop >= windowHeight,
                    scrolledBelowFooter = pageOffsetBottom >= footerOffsetTop;

                if (firstScreenScrolled && !scrolledBelowFooter) {
                    $_.$scrollTop.addClass('_show');
                } else {
                    $_.$scrollTop.removeClass('_show');
                }
            }

            $_.$scrollTop.on('click', () => $_._scroll(0));
            $_.$window.scroll(() => check());

            check();
        },

        initBodyClickClose () {
            $_.$body.on('click', (e) => {
                const
                    $target = $(e.target),
                    $bccItems = $($_.selectors.bcc).filter('._active');

                if ($bccItems.length) {
                    const
                        isBcc = $target.hasClass($_.selectors.bcc),
                        $closestBcc = $target.closest($_.selectors.bcc),
                        $targetToPrevent = isBcc ? $target : $closestBcc,
                        $closestBccSibling = $target.closest($_.selectors.bccSibling),
                        $relatedBcc = $closestBccSibling.siblings($_.selectors.bcc),
                        $targetsToClose = $bccItems.not($targetToPrevent).not($relatedBcc);

                    $targetsToClose.each((key, item) => {
                        const
                            $currentItem = $(item),
                            dataTriggerOnClose = $currentItem.data('trigger-on-close');

                        if (dataTriggerOnClose) $currentItem.trigger(dataTriggerOnClose)
                        $currentItem.removeClass('_active');
                    });
                }
            });

            $_.$body.on('click', $_.selectors.bccClose, (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    bccSelector = `${$_.selectors.bcc},${$_.selectors.bccSibling}`,
                    $closestBcc = $currentTarget.closest(bccSelector),
                    $relatedBcc = $closestBcc.siblings(bccSelector);

                console.log('$closestBcc', $closestBcc)
                $closestBcc.add($relatedBcc).removeClass('_active');
            });
        },

        initResizeTrigger () {
            const resizeDelay = 300;

            let resizeTimer = null,
                windowWidth = window.outerWidth,
                windowHeight = $_.$window.height();

            $_.$window.resize(() => {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(() => {
                    const
                        currentWidth = window.outerWidth,
                        currentHeight = $_.$window.height(),
                        resizeWidth = windowWidth !== currentWidth,
                        resizeHeight = windowHeight !== currentHeight;

                    if (resizeWidth) {
                        windowWidth = currentWidth;
                        $_.windowWidth = currentWidth;

                        $_.$body.trigger('body:resize:width');
                        if (!resizeHeight) $_.$body.trigger('body:resize:only-width');
                    }

                    if (resizeHeight) {
                        windowHeight = currentHeight;
                        $_.windowHeight = currentHeight;

                        $_.$body.trigger('body:resize:height');
                        if (!resizeWidth) $_.$body.trigger('body:resize:only-height');
                    }

                    $_.$body.trigger('body:resize');
                }, resizeDelay);
            });
        },



        initScrollEvents () {
            const
                stopScrollingDelay = 100,
                whileScrollingDelay = 200;

            let checkIsReady = true,
                scrollTimer = null;

            $_.$window.scroll(() => {
                checkTimer();

                if (!$_.mapIsInit && $_.$map.length) $_.$map.trigger('trigger:check');
            });

            function callTimerFunctions() {
                $_.$body.trigger('trigger:check-in-window');
            }

            function checkTimer() {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => {
                    callTimerFunctions();
                }, stopScrollingDelay);

                if (checkIsReady) {
                    checkIsReady = false;
                    callTimerFunctions();

                    setTimeout(() => {
                        checkIsReady = true;
                    }, whileScrollingDelay)
                }
            }

            setTimeout(() => {
                checkTimer();
            }, 1000);

            $_.$map.on('trigger:is-init', () => {
                $_.mapIsInit = true;
            });
        },

        initMenu() {
            $_.$menuBtn.on('click', () => {
                $_.$header.toggleClass('_active');
            })
        },

        initNavLinks () {
            $_.$navLink.on('click', (e) => {
                e.preventDefault();

                const
                    $el = $(e.currentTarget),
                    anchor = $el.attr('href'),
                    id = anchor.indexOf('#') !== -1 ? anchor : "#" + anchor,
                    headerHeight = $_.$header.height(),
                    offset = $(id).offset().top - headerHeight;

                $_.$header.removeClass('_active');
                $_._scroll(offset);
            })
        },

        initHideOnTargetReaching() {
            function check() {
                $_.$hideOnTargetReaching.each((key, item) => {
                    const
                        $currentItem = $(item),
                        dataTarget = $currentItem.data('target'),
                        dataOffset = $currentItem.data('offset') || 0,
                        $target = $(dataTarget);

                    if ($target.length) {
                        const
                            targetReached = $currentItem.offset().top >= ($target.offset().top + dataOffset),
                            isHidden = $currentItem.hasClass('_hide');

                        if (targetReached) {
                            if (!isHidden) $currentItem.addClass('_hide');
                        } else {
                            if (isHidden) $currentItem.removeClass('_hide');
                        }
                    }
                });
            }

            $_.$body.on('trigger:check-in-window', () => {
                check();
            });

            check();
        },

        initCheckInWindow() {
            function check() {
                $_.$checkInWindow.each((key, item) => {
                    const
                        $el = $(item),
                        dataTriggerIn = $el.data('trigger-in'),
                        dataTriggerOut = $el.data('trigger-out'),
                        dataBodyTrigger = $el.data('trigger-body'),
                        dataActiveOutOfTop = $el.data('active-out-of-top'),
                        disableOut = $el.data('disable-out'),
                        dataFirstDelay = $el.data('first-inw-delay'),
                        preventDelay = $el.data('prevent-delay'),
                        delay = preventDelay ? 0 : (dataFirstDelay || 0);

                    setTimeout(() => {
                        $el.data('prevent-delay', true);

                        const
                            position = item.getBoundingClientRect(),
                            inWindowD = $el.data('in-window') || false,
                            outOfTop = position.bottom < 0,
                            outOfBottom = position.top > $_.windowHeight;

                        if (dataActiveOutOfTop && outOfTop && !inWindowD) $el.addClass('_in-window');

                        if (outOfTop || outOfBottom) {
                            if (inWindowD) {
                                $el.data('in-window', false);
                                if (!disableOut) $el.removeClass('_in-window');
                                if (dataTriggerOut) $el.trigger(dataTriggerOut);
                            }
                        } else {
                            if (!inWindowD) {
                                $el.data('in-window', true);
                                $el.addClass('_in-window');
                                if (dataTriggerIn) $el.trigger(dataTriggerIn);

                                if (dataBodyTrigger) {
                                    const { trigger, data='' } = dataBodyTrigger;

                                    $_.$body.trigger(trigger, data)
                                }
                            }
                        }
                    }, delay)
                });
            }

            $_.$body.on('trigger:check-in-window', () => {
                check();
            });

            check();
        },

        _getTranslateStyles(x = 0, y = 0) {
            return {
                "-webkit-transform": `translate3d(${x}px, ${y}px, 0)`,
                "-moz-transform": `translate3d(${x}px, ${y}px, 0)`,
                "-ms-transform": `translate3d(${x}px, ${y}px, 0)`,
                "-o-transform": `translate3d(${x}px, ${y}px, 0)`,
                transform: `translate3d(${x}px, ${y}px, 0)`,
            }
        },

        _initSliderDotsNav(obj) {
            const
                { slick, dotsCount: dotsCountParam } = obj,
                { $dots } = slick;

            if (!$dots.data('isInit')) {
                const
                    { options } = slick,
                    { slidesToScroll } = options,
                    dotsCount = dotsCountParam ? (dotsCountParam % 2 !== 0 ? dotsCountParam : dotsCountParam - 1) : 5,
                    dotsMid = (dotsCount - 1) / 2,
                    $dotsTrack = $(`<div class="dots-track"></div>`),
                    $slickDots = slick.$dots.children();

                slick.$dots.data('isInit', true).addClass('js-prevent').append($dotsTrack.append($slickDots));

                const
                    dotWidth = $slickDots.width(),
                    dotHeight = $slickDots.height(),
                    wrapWidth = dotWidth * dotsCount,
                    dotsAreFit = slick.slideCount < dotsCount;

                slick.$dots.css({'width': wrapWidth, 'height': dotHeight});

                if (dotsAreFit) {
                    slick.$dots.addClass('_center');
                } else {
                    slick.$slider.on('beforeChange', (event, slick, currentSlide, nextSlide) => {
                        const
                            offset = dotWidth * (nextSlide/slidesToScroll - dotsMid),
                            minOffset = 0,
                            maxOffset = $dotsTrack.width() - Math.abs(wrapWidth),
                            fixOffset = offset < 0 ? minOffset : (offset > maxOffset ? maxOffset : offset);

                        $dotsTrack.css($_._getTranslateStyles(-fixOffset));
                    });
                }
            }
        },

        _toggleActiveClasses($el) {
            $el.addClass('_active').siblings().removeClass('_active');
        },

        _scroll(val) {
            $_.$page.on('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove', () => {
                $_.$page.stop();
            });

            $('html, body').stop().animate({scrollTop: val}, 1000, () => {
                $_.$page.off('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove');
            })
        },

        _preventParentSliderSwipe($parent, $child) {
            $child.on('touchstart mousedown', () => {
                $parent.slick('slickSetOption', 'swipe', false, false);
            });

            $child.on('touchend mouseup mouseout', () => {
                $parent.slick('slickSetOption', 'swipe', true, false);
            });
        },

        _getRelatedSliderNav($slider) {
            const
                $wrap = $slider.closest($_.selectors.jsWrap),
                $sliderNav = $wrap.find($_.selectors.sliderNav);

            return {
                $sliderNav,
                $arrowLeft: $sliderNav.find($_.selectors.arrowLeft),
                $arrowRight: $sliderNav.find($_.selectors.arrowRight),
                $current: $sliderNav.find($_.selectors.current),
                $total: $sliderNav.find($_.selectors.total),
            }
        },

        _initSliderCounter(slick, $current, $total) {
            $current.html(slick.currentSlide + 1);
            $total.html(slick.slideCount);

            slick.$slider.on('beforeChange', (event, slick, currentSlide, nextSlide) => {
                $current.html(nextSlide + 1);
            });
        },
    };

    $(document).ready(() => {
        $_.init();
    });
});

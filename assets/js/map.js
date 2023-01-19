require('leaflet');
require('leaflet-responsive-popup');
require('leaflet-freedraw'); // TODO: remove?
require('paginationjs');
import { mapTemplates } from './templates/templates';
import { decode } from './utils/flexible-polyline';

class EstateMap {

    constructor(id) {
        this.initCache(id);
        this.initMap();
        this.initDrawButtons();
        this.initSliderCheckTimer();
        this.initEvents();
        this.initYelpNav();
        this.initLocationChange();
        this.initDemographyDropdownForm();
        this.initDropdownSubmenuButtons();
        this.initDropdownSubmenuBackButtons();
    }

    initCache(id) {
        this.$currentDemography = null;
        this.$body = $('body');
        this.$window = $(window);

        this.$map = $(id);
        this.$iw = this.$map.closest('.js-map-interface-wrap');

        this.$mapContainer = this.$iw.find('.js-map-container');
        this.$mapTitle = this.$iw.find('.js-map-title');
        this.$toggleView = this.$iw.find('.js-toggle-view');
        this.$resetButton = this.$iw.find('.js-form-reset-button');
        this.$locationInput = this.$iw.find('.js-location-input');
        this.$drawButton = this.$iw.find('.js-map-draw');
        this.$drawCancel = this.$iw.find('.js-draw-cancel');
        this.$drawApply = this.$iw.find('.js-draw-apply');
        this.$drawEdit = this.$iw.find('.js-draw-edit');
        this.$showSchools = this.$iw.find('.js-map-show-schools');
        this.$estateCardsWrap = this.$iw.find('.js-estate-cards-wrap');
        this.$estateCardsList = this.$iw.find('.js-estate-cards-list');
        this.$cardsScrollWrap = this.$iw.find('.js-cards-scroll-wrap');
        this.$estateCardsPagination = this.$iw.find('.js-estate-cards-pagination');
        this.$yelpSelect = this.$iw.find('.js-yelp-select');
        this.$yelpNavLink = this.$iw.find('.js-yelp-nav-link');
        this.$yelpCardsSlider = this.$iw.find('.js-yelp-cards-slider');
        this.$yelpCardsMenu = this.$iw.find('.js-yelp-cards-menu');
        this.$schoolsCardsMenu = this.$iw.find('.js-schools-cards-menu');
        this.$homesAvailable = this.$iw.find('.js-homes-available');
        this.$mapLayerOption = this.$iw.find('.js-map-layer-option');
        this.$layerOptionInput = this.$iw.find('.js-layer-option-input');
        this.$schoolsOption = this.$iw.find('.js-schools-option');
        this.$demographyOption = this.$iw.find('.js-demography-option');
        this.$filterForm = this.$iw.find('.js-filter-form');
        this.$sendFilterButton = this.$iw.find('.js-send-filter-button');
        this.$saveMapFilter = this.$iw.find('.js-save-map-filter');
        this.$toggleFilterPopup = this.$iw.find('.js-toggle-filter-popup');
        this.$filterPopup = this.$iw.find('.js-filter-popup');
        this.$demographyDropdownForms = this.$iw.find('.js-demography-dropdown-form');
        this.$totalFiltersCount = this.$iw.find('.js-total-filters-count');
        this.$markerRef = this.$iw.find('.js-marker-ref');
        this.$destinationInput = this.$iw.find('.js-destination-input');
        this.$commuteOption = this.$iw.find('.js-commute-option');
        this.$commuteRoutesMenu = this.$iw.find('.js-commute-routes-menu');
        this.$filterMoreToggle = this.$iw.find('.js-filter-more-toggle');
        this.$dropdownSubmenuButtons = this.$iw.find('.js-dropdown-submenu-button');
        this.$dropdownSubmenuBackButtons = this.$iw.find('.js-dropdown-submenu-back-button');

        this.dataParams = this.$map.data('params');

        this.map = null;
        this.estateCardsShowed = {from: 0, to: 0};
        this.timers = { resize: null, refresh: null, filter: null };
        this.boxState = { current: null, prev: null };
        this.layerOption = this.$mapLayerOption.length ? this.$mapLayerOption.filter('._active').data('val') : null;
        this.schoolOption = this.$schoolsOption.length ? this.$schoolsOption.filter('._active').data('val') : null;
        this.demographyOption = this.$demographyOption.length ? this.$demographyOption.filter('._active').data('val') : null;
        this.commuteOption = this.$commuteOption.length ? this.$commuteOption.filter('._active').data('val') : null;
        this.estateCardsWrapPosition = this.$estateCardsWrap.length && this.$estateCardsWrap[0].getBoundingClientRect();

        this.layers = {
            initial: { marker: null },
            listings: { layer: null, items: null, lastBox: null },
            schools: { layer: null, items: null, data: null, lastBox: null },
            busStops: { layer: null, items: null, data: null, lastBox: null },
            crime: { layer: null, items: null, lastBox: null },
            demography: { layer: null, items: null, lastBox: null },

            commute: { layer: null, items: null },

            yelp: { layer: null, items: null, lastBox: null },
            draw: { layer: null }
        };

        this.flags = {
            preventFilterSending: false,
            preventMapMoveEndHandler: false,
            fitMarkers: false,
            pasteCards: false,
        };

        this.settings = {
            refreshDelay: 1000,
            resizeUpdateDelay: 300,
            popupWidth: 400,
            yelpPopupWidth: 290,
            schoolPopupWidth: 290,
            busStopPopupWidth: 290,
        }

        this.selectors = {
            smoothScroll: '.js-smooth-scroll',
            preventOnChangeSend: '.js-prevent-send',
            selectModule: '.js-select-module',
            removeRoute: '.js-remove-route',
        }

        this.polygonStyles = {
            default: {
                color: 'red',
                fillOpacity: 0.1,
                opacity: 0.8,
                weight: 1,
            },

            highlight: {
                color: 'blue',
                opacity: 1,
                fillOpacity: 0.2,
                weight: 2,
            },

            high: {
                color: '#FF0000',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            },
            above_average: {
                color: '#FF6600',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            },
            average: {
                color: '#FFFF00',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            },
            below_average: {
                color: '#00FF00',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            },
            low: {
                color: '#0000FF',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            },
            no_data: {
                color: '#8E8D8D',
                fillOpacity: 0.3,
                opacity: 0.8,
                weight: 1,
            }
        }

        this.polylineStyles = {
            default: {
                color: '#CB009B',
                weight: 5,
            },
            car: {
                color: '#3452c9',
            },
            bus: {
                color: '#882800',
            },
            pedestrian: {
                color: '#249200',
            },
            bicycle: {
                color: '#a96400',
            },
            scooter: {
                color: '#00ab75',
            },
        }

        if (this.$layerOptionInput.length) this.$layerOptionInput.val(this.layerOption);

        this.hereApiKey = 'DRoLaDHSmAieGcegl41-VD7sPrKAnFVLzmmqquZPlAQ';
    }

    initLocationChange() {
        window.onpopstate = () => {
            this._urlToCriteria(window.location.pathname);
        };
    }

    _changeLocation(data) {
        const { url } = data;
        window.history.pushState(null, null, url);
    }

    initEvents() {
        this.$filterMoreToggle.on('trigger:apply-filter', () => {
            this._criteriaToUrl();
            this._loadListings();
        });

        this.$destinationInput.on('coordinates-loaded', (e, data) => {
            this._addRouteDestination(data);
            this._refreshRoutes();
            this.$destinationInput.val('');
        });

        this.$body.on('click', this.selectors.removeRoute, (e) => {
            this._removeRoute($(e.currentTarget).data('destination'));
            this._addRoutes();
            this._addRoutesCards();
            this.map.closePopup();
        });

        this.map.on('move', () => {
            clearTimeout(this.timers.refresh);
        });

        this.map.on('resize', () => {
            this.flags.preventMapMoveEndHandler = true;
        });

        this.map.on('moveend', () => {
            if (this.flags.preventMapMoveEndHandler) {
                this.flags.preventMapMoveEndHandler = false;
            } else {
                this._setBox();
                this._runRefreshTimer();
            }
        });

        this.$estateCardsWrap.on('trigger:check-sliders', () => {
            this._runSliderCheckTimer();
        });

        this.$yelpSelect.on('change', (e) => {
            this.yelpTerm = $(e.currentTarget).val();
            this._yelpSearch();
        });

        this.$showSchools.on('click', () => {
            this._loadSchools();
        });

        this.$mapLayerOption
            .add(this.$schoolsOption)
            .add(this.$commuteOption)
            .on('click trigger:click', (e) => {
                const $currentTarget = $(e.currentTarget);
                this._setLayerOption($currentTarget);
            });

        this.$window.resize(() => {
            this._setParamsOnResize();
        });

        this.$filterForm.on('change', (e) => {
            if (!this.flags.preventFilterSending) {
                this._showTotalFiltersCount();
                this._filterChangeHandler(e);
            }
        });

        this.$sendFilterButton.on('click', () => {
            this._criteriaToUrl();
            this._loadListings();
            this.$filterPopup.removeClass('_active');
        });

        this.$resetButton.on('trigger:reset:start', () => {
            this.flags.preventFilterSending = true;
        });

        this.$resetButton.on('trigger:reset:complete', () => {
            this._showTotalFiltersCount();
            this._criteriaToUrl();
            this._loadListings();
            this.flags.preventFilterSending = false;
        });

        this.$saveMapFilter.on('click', () => {
            if (!this.$saveMapFilter.hasClass('_active')) {
                this._saveFilter();
            }
        });

        this.$toggleFilterPopup.on('click', () => {
            this.$filterPopup.toggleClass('_active');

            if (!this.$filterPopup.hasClass('_active')) {
                this._criteriaToUrl();
                this._loadListings();
            }
        });

        this.$toggleView.on('click', () => {
            this.$toggleView.toggleClass('_active');
            this.$iw.removeClass('_map _list').addClass(this.$toggleView.hasClass('_active') ? '_map' : '_list');
        });

        this.$markerRef.on('mouseenter', (e) => {
            const
                $currentTarget = $(e.currentTarget),
                dataListingId = $currentTarget.data('listing-id'),
                $marker = this.layers.listings.items && this.layers.listings.items[dataListingId];

            if ($marker) {
                $marker.fire('mouseover');
                $($marker._icon).addClass('_force-hover-state');
            }
        });

        this.$map.on('trigger:set-center', (e, coords) => {
            const { lat, lon } = coords;
            this.map.removeLayer(this.layers.initial.marker);
            this.map.panTo(new L.LatLng(lat, lon));
            this._setInitialMarker(coords);
        });
    }

    initMap() {
        const
            {
                center,
                zoom=13,
                minZoom=10,
                initialMarker,
                disableControls=false,
                controlsPosition='topright'
            } = this.dataParams,

            OpenStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }),

            Here = L.tileLayer('https://2.aerial.maps.ls.hereapi.com/maptile/2.1/maptile/newest/satellite.day/{z}/{x}/{y}/512/png8?apiKey={accessToken}', {
                attribution: '&copy; HERE 2019',
                accessToken: this.hereApiKey,
            }),

            baseMaps = {
                "Streets": OpenStreetMap,
                "Satellite": Here,
            };

        OpenStreetMap.on('load', () => {
            this.$map.trigger('trigger:open-street-map-lite-loaded');
        });

        Here.on('load', () => {
            this.$map.trigger('trigger:here-lite-loaded');
        });

        this.map = L.map(this.$map[0], {
            zoom: zoom,
            zoomSnap: 0.1,
            minZoom: minZoom,
            zoomControl: false,
            layers: [OpenStreetMap]
        });

        if (center) {
            let { lat, lon, zoom } = center
            this.map.on('load', () => {
                this.flags.fitMarkers = true;
                this._setBox();
                this._runRefreshTimer();
            });

            this.map.setView([lat, lon], zoom);
        } else {
            this.flags.fitMarkers = true;
            this._setBox();
            this._runRefreshTimer();
        }

        if (initialMarker) {
            this._setInitialMarker(center);
        }

        if (!disableControls) {
            L.control.layers(baseMaps, null, {
                position: controlsPosition
            }).addTo(this.map);

            L.control.zoom({
                position: controlsPosition
            }).addTo(this.map);
        }

        this.map.on('popupclose', (e) => {
            if (e.popup._closeEvent) e.popup._closeEvent();
        });

        this.$map.addClass('map-initialized');
    }

    initYelpNav() {
        const $activeNavLink = this.$yelpNavLink.filter('._active');

        if ($activeNavLink.length) {
            this.yelpTerm = $activeNavLink.data('val');
        }

        this.$yelpNavLink.on('click', (e) => {
            this.yelpTerm = $(e.currentTarget).data('val');
            this._yelpSearch();
        });
    }

    initDrawButtons() {
        const { CREATE, EDIT, DELETE, NONE } = FreeDraw;

        const getDrawCoordinates = (freeDrawAll) => {
            let drawCoordinatesArray = [];

            for (let i = 0; i < freeDrawAll.length; i++) {
                drawCoordinatesArray.push(freeDrawAll[0]._latlngs);
            }

            console.dir(drawCoordinatesArray);
        };

        const toggleDraw = (enable) => {
            this._disableMapInteraction(enable);
            this.$iw[enable ? 'addClass' : 'removeClass']('_drawing');
            this.layers.draw.layer.mode(enable ? CREATE | EDIT | DELETE : NONE);
        };

        this._cancelDraw = () => {
            if (this.layers.draw.layer) {
                this.layers.draw.layer.cancel();
                this.layers.draw.layer.clear();
                toggleDraw(false);
                this._runRefreshTimer();
                this.$iw.removeClass('_draw-apply');
                this.layers.draw.layer = null;
            }
        }

        this.$drawButton.on('click', () => {
            if (!this.layers.draw.layer) this.layers.draw.layer = new FreeDraw();

            clearTimeout(this.timers.refresh);
            this._clearLayers('draw');
            this.map.addLayer(this.layers.draw.layer);
            toggleDraw(true);
        });

        this.$drawCancel.on('click', () => {
            this._cancelDraw();
        });

        this.$drawApply.on('click', () => {
            getDrawCoordinates(this.layers.draw.layer.all());
            toggleDraw(false);
            this.$iw.addClass('_draw-apply');
        });

        this.$drawEdit.on('click', () => {
            clearTimeout(this.timers.refresh);
            toggleDraw(true);
        });
    }

    initSliderCheckTimer() {
        const
            stopScrollingDelay = 100,
            whileScrollingDelay = 200;

        const callTimerFunctions = () =>  {
            this._checkSliders();
        };

        let
            checkIsReady = true,
            scrollTimer = null;

        this._runSliderCheckTimer = () => {
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
    }

    initDemographyDropdownForm() {
        this.$demographyDropdownForms.on('change', item => {
            let currentTarget = item.currentTarget
            let $type = $(currentTarget).data('type')
            switch ($type) {
                case 'crime':
                    this._loadCrime();
                    $(document).find('#' + $type + '-dropdown-select').toggleClass('_active')
                    break;
                case 'demography':
                    this._loadDemography();
                    $(document).find('#' + $type + '-dropdown-select').toggleClass('_active')
                    break;
            }
        })
    }

    initDropdownSubmenuButtons() {
        this.$dropdownSubmenuButtons.on('click', e => {
            e.preventDefault()

            let currentTarget = e.currentTarget
            let $submenu = $(currentTarget).data('submenu')
            this.$iw.find(`#js-submenu-${$submenu}`).toggleClass('_hide')
            this.$iw.find('.dropdown-submenu-button').toggleClass('_hide')
        })
    }

    initDropdownSubmenuBackButtons() {
        this.$dropdownSubmenuBackButtons.on('click', e => {
            e.preventDefault()

            let currentTarget = e.currentTarget
            let $submenu = $(currentTarget).data('submenu')
            this.$iw.find(`#js-submenu-${$submenu}`).toggleClass('_hide')
            this.$iw.find('.dropdown-submenu-button').toggleClass('_hide')
        })
    }

    _setLayerOption($option) {
        const isActive = $option.data('active-option');

        if (!isActive) {
            const val = $option.data('val');

            this._clearLayers();

            if ($option.is(this.$mapLayerOption)) {
                this.$mapLayerOption.data('active-option', false);
                this.layerOption = val;
                this.$layerOptionInput.val(val);
                this._runRefreshTimer(100);
            }

            if ($option.is(this.$schoolsOption)) {
                this.$schoolsOption.data('active-option', false);
                this.schoolOption = val;
                this._loadSchools();
            }

            if ($option.is(this.$commuteOption)) {
                this.$commuteOption.data('active-option', false);
                this.commuteOption = val;
                this._refreshRoutes();
            }

            $option.data('active-option', true);
        }
    }

    _saveFilter() {
        const
            url = this.$saveMapFilter.data('url'),
            method = this.$saveMapFilter.data('method') || 'POST',
            requestParameters = {
                type: method,
                url: url,
                contentType: 'application/json',
                dataType: 'json',
                data: _getJsonFormData(this.$filterForm, true),
                beforeSend: () => {
                    this.$saveMapFilter.addClass('_active');
                }
            };

        _authenticationRequiredRequest({
            requestParameters,
            callback: () => {
                setTimeout(() => {
                    this.$saveMapFilter.removeClass('_active')
                }, 2000);
            }
        });
    }

    _loadSchools() {
        const
            { schoolsPath } = this.dataParams,
            requestParameters = {
                url: schoolsPath,
                contentType: 'application/json',
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({ location: this._coordsObjectToString(this.map.getBounds()) })
            };

        const request = $.ajax(requestParameters).done((data) => {
            this._parseSchoolsData(data);
            this._addSchools();
            this._addCardsToMapMenu({
                $wrap: this.$schoolsCardsMenu,
                data: this.layers.schools.data,
                layer: 'schools',
                template: 'schoolCard',
            });
        });

        this._showPreloader(request, 'schools');
        this._errorHandler(request);
    }

    _loadBusStops() {
        const
          { busStopsPath } = this.dataParams,
          requestParameters = {
              url: busStopsPath,
              contentType: 'application/json',
              type: 'POST',
              dataType: 'json',
              data: JSON.stringify({ location: this._coordsObjectToString(this.map.getBounds()) })
          };

        const request = $.ajax(requestParameters).done((data) => {
            this._parseBusStopsData(data);
            this._addBusStops();
            this._addCardsToMapMenu({
                $wrap: this.$schoolsCardsMenu,
                data: this.layers.busStops.data,
                layer: 'busStop',
                template: 'busStopCard',
            });
        });

        this._showPreloader(request, 'busStops');
        this._errorHandler(request);
    }

    _loadCrime() {
        const
          { crimePath } = this.dataParams,
          requestParameters = {
              url: crimePath,
              contentType: 'application/json',
              type: 'POST',
              dataType: 'json',
              data: JSON.stringify({ location: this._coordsObjectToString(this.map.getBounds()) })
          };

        const request = $.ajax(requestParameters).done((data) => {
            this._parseCrimeData(data);
            this._addCrime();
        });

        this._showPreloader(request, 'crime');
        this._errorHandler(request);
    }

    _loadDemography() {
        const
          { demographyPath } = this.dataParams,
          requestParameters = {
              url: demographyPath,
              contentType: 'application/json',
              type: 'POST',
              dataType: 'json',
              data: JSON.stringify({ location: this._coordsObjectToString(this.map.getBounds()) })
          };

        const request = $.ajax(requestParameters).done((data) => {
            this._parseDemographyData(data);
            this._addDemography();
            this._addLegendWithLegendType('demography')
        });

        this._showPreloader(request, 'demography');
        this._errorHandler(request);
    }

    _parseCrimeData(data) {
        this.layers.crime.items = {};
        this.layers.crime.data = [];

        let value = this._getCurrentDemographyValue('crime')

        for (let i = 0; i < data.length; i++) {
            const {areas} = data[i]

            if (areas) {
                let {type, coordinates} = areas;
                let color = this._getColor(data[i], value);
                if (type === 'Polygon') {
                    if (coordinates.length <= 1) {
                        this.layers.crime.items[`${i}_polygon`] = L.polygon(coordinates[0].map(([latitude, longitude]) => [longitude, latitude]), {
                            ...color,
                            interactive: false,
                            bubblingMouseEvents: false,
                        });
                    } else {
                        coordinates.forEach((area, index) => {
                            this.layers.crime.items[`${i}_${index}_polygon`] = L.polygon(area.map(([latitude, longitude]) => [longitude, latitude]), {
                                ...color,
                                interactive: false,
                                bubblingMouseEvents: false,
                            });
                        });
                    }
                } else if (type === 'MultiPolygon') {
                    coordinates.forEach((area, index) => {
                        this.layers.crime.items[`${i}_${index}_polygon`] = L.polygon(area[0].map(([latitude, longitude]) => [longitude, latitude]), {
                            ...color,
                            interactive: false,
                            bubblingMouseEvents: false,
                        });
                    });
                }
            }
            this.layers.crime.data.push(data[i]);
        }
    }

    _parseSchoolsData(data) {
        this.layers.schools.items = {};
        this.layers.schools.data = [];

        for (let i = 0; i < data.length; i++) {
            const
                { level } = data[i],
                matchOption = level.toLowerCase().indexOf(this.schoolOption) !== -1;

            if (!this.schoolOption || (this.schoolOption === 'all') || matchOption) {
                const
                    { areas, coordinates } = data[i],
                    { lat, lon } = coordinates,
                    id = this._coordinatesToId(coordinates),
                    marker = L.marker(L.latLng(lat, lon), {
                        icon: this._constructYelpDivIcon({
                            term: 'school',
                            iconOptions: {
                                iconSize: [22, 22],
                                iconAnchor: [11, 11],
                            }
                        })
                    });

                let popup = null;

                marker.on('mouseover click', () => {
                    if (!popup) popup = this._constructMarkerPopup({
                        data: data[i],
                        template: 'schoolCard',
                        width: this.settings.schoolPopupWidth,
                        marker,
                        popupOptions: {
                            offset: [0, -11],
                        },
                        closeCallback: () => {
                            if (this.layers.schools.items[`${id}_polygon`]) {
                                this.layers.schools.items[`${id}_polygon`].setStyle(this.polygonStyles.default)
                            }
                        }
                    });

                    if (this.layers.schools.items[`${id}_polygon`]) {
                        this.layers.schools.items[`${id}_polygon`].setStyle(this.polygonStyles.highlight).bringToFront();
                    }

                    this._markerHoverHandler(marker, popup);
                });


                // TODO 'school polygons'
                // if (areas) {
                //     this.layers.schools.items[`${id}_polygon`] = L.polygon(areas.coordinates[0].map(([latitude, longitude]) => [longitude, latitude]), {
                //         ...this.polygonStyles.default,
                //         interactive: false,
                //         bubblingMouseEvents: false,
                //     });
                // }

                this.layers.schools.data.push(data[i]);
                this.layers.schools.items[id] = marker;
            }
        }
    }

    _parseBusStopsData(data) {
        this.layers.busStops.items = {};
        this.layers.busStops.data = [];

        const total = data.length;

        for (let i = 0; i < data.length; i++) {
            const
                  { coordinates } = data[i],
                  useSimpleIcon = total > _getBpVal([200, null, null, 30]),
                  iconOptions = {
                      iconSize: [0, 0],
                      iconAnchor: null,
                      baseClass: 'bus-stop',
                      html: `<div class="bus-stop-inner ${useSimpleIcon ? '' : '_big'}"></div>`,
                  },
                  { lat, lon } = coordinates,
                  id = this._coordinatesToId(coordinates),
                  marker = L.marker(L.latLng(lat, lon), {
                      icon: this._constructDivIcon(iconOptions)
                  });
            console.log(useSimpleIcon, 'useSimpleIcon');

                let popup = null;

                marker.on('mouseover click', () => {
                    if (!popup) popup = this._constructMarkerPopup({
                        data: data[i],
                        template: null,
                        width: this.settings.busStopPopupWidth,
                        marker,
                        popupOptions: {
                            offset: [0, -11],
                        },
                        closeCallback: () => {
                            if (this.layers.busStops.items[`${id}_polygon`]) {
                                this.layers.busStops.items[`${id}_polygon`].setStyle(this.polygonStyles.default)
                            }
                        }
                    });

                    if (this.layers.busStops.items[`${id}_polygon`]) {
                        this.layers.busStops.items[`${id}_polygon`].setStyle(this.polygonStyles.highlight).bringToFront();
                    }

                    this._markerHoverHandler(marker, popup);
                });

                this.layers.busStops.data.push(data[i]);
                this.layers.busStops.items[id] = marker;

        }
    }

    _parseDemographyData(data) {
        this.layers.demography.items = {};
        this.layers.demography.data = [];

        let value = this._getCurrentDemographyValue('demography')

        for (let i = 0; i < data.length; i++) {
            const {areas} = data[i]

            if (areas) {
                let {type, coordinates} = areas;
                let color = this._getColor(data[i], value);
                if (type === 'Polygon') {
                    if (coordinates.length <= 1) {
                        this.layers.demography.items[`${i}_polygon`] = L.polygon(coordinates[0].map(([latitude, longitude]) => [longitude, latitude]), {
                            ...color,
                            interactive: false,
                            bubblingMouseEvents: false,
                        });
                    } else {
                        coordinates.forEach((area, index) => {
                            this.layers.demography.items[`${i}_${index}_polygon`] = L.polygon(area.map(([latitude, longitude]) => [longitude, latitude]), {
                                ...color,
                                interactive: false,
                                bubblingMouseEvents: false,
                            });
                        });
                    }
                } else if (type === 'MultiPolygon') {
                    coordinates.forEach((area, index) => {
                        this.layers.demography.items[`${i}_${index}_polygon`] = L.polygon(area[0].map(([latitude, longitude]) => [longitude, latitude]), {
                            ...color,
                            interactive: false,
                            bubblingMouseEvents: false,
                        });
                    });
                }
            }
            this.layers.demography.data.push(data[i]);
            // this.layers.schools.items[id] = marker;
        }
    }

    _addSchools() {
        if (this.layers.schools.layer) this.layers.schools.layer.clearLayers();
        this.layers.schools.layer = L.layerGroup(Object.values(this.layers.schools.items));
        this.map.addLayer(this.layers.schools.layer);
    }

    _addCrime() {
        if (this.layers.crime.layer) this.layers.crime.layer.clearLayers();
        this.layers.crime.layer = L.layerGroup(Object.values(this.layers.crime.items));
        this.map.addLayer(this.layers.crime.layer);
    }

    _addBusStops() {
        if (this.layers.busStops.layer) this.layers.busStops.layer.clearLayers();
        this.layers.busStops.layer = L.layerGroup(Object.values(this.layers.busStops.items));
        this.map.addLayer(this.layers.busStops.layer);
    }

    _addDemography() {
        if (this.layers.demography.layer) this.layers.demography.layer.clearLayers();
        console.log(this.layers.demography.items);
        this.layers.demography.layer = L.layerGroup(Object.values(this.layers.demography.items));
        this.map.addLayer(this.layers.demography.layer);
        this._addLegendWithLegendType('demography')
    }

    _constructBigIcon(data={}) {
        const { mod='' } = data;

        return L.divIcon({
            iconSize: [42, 49],
            iconAnchor: [21, 49],
            popupAnchor: [0, -49],
            className: `big-icon ${mod}`,
        });
    }

    _setInitialMarker(coords) {
        const { lat, lon } = coords;

        this.layers.initial.marker = L.marker(
            L.latLng(lat, lon),
            {
                icon: this._constructBigIcon({
                    mod: 'icon-realestate'
                }),
                zIndexOffset: 99999
            }
        );

        this.layers.initial.marker.addTo(this.map);
    }

    _constructDivIcon(data={}) {
        const { baseClass='marker-icon', mod='', ...rest } = data;

        return L.divIcon({
            iconSize: _getBpVal([[10, 10], null, null, [16, 16]]),
            iconAnchor: _getBpVal([[5, 5], null, null, [8, 8]]),
            popupAnchor: [0, 0],
            className: `${baseClass} ${mod}`,
            ...rest
        });
    }

    _constructYelpDivIcon(data) {
        const
            { categories=[], term, iconOptions={} } = data,
            termMod = term ? `_ic-${term}` : '',
            categoryMod = categories.length ? categories.map(item => `_ic-${item.alias}`).join(' ') : '_ic-default';

        return L.divIcon({
            iconSize: [20, 20],
            iconAnchor: [10, 10],
            popupAnchor: [0, 0],
            className: `yelp-marker-icon ${termMod} ${categoryMod}`,
            ...iconOptions
        });
    }

    _constructMarkerPopup(props) {
        const
            { marker, data, template, closeCallback, width=this.settings.popupWidth, popupOptions={} } = props,
            { coordinates } = data,
            { lat, latitude, lon, longitude } = coordinates,
            popup = L.responsivePopup({
                maxWidth: width,
                closeButton: true,
                riseOnHover: true,
                riseOffset: 9999,
                keepInView: true,
                autoPan: false,
                offset: [0, 0],
                ...popupOptions
            });

        popup
            .setLatLng(L.latLng(lat||latitude, lon||longitude))
            .setContent(mapTemplates[template](data))
            ._closeEvent = () => {
                $(marker._icon).removeClass('_force-hover-state');
                if (closeCallback) closeCallback();
            };

        return popup;
    }

    _parseListingsData(data, from=0) {
        const
            { maxMarkersCount=4800 } = this.dataParams,
            to = Math.min(from + maxMarkersCount, data.length),
            total = data.length;

        this.layers.listings.items = {};
        this.estateCardsShowed = { from, to };

        for (let i = from; i < to; i++) {
            if (data[i]) {
                const
                    { coordinates, listingId, financials } = data[i],
                    useSimpleIcon = total > _getBpVal([60, null, null, 30]),
                    iconOptions = useSimpleIcon ? {} : {
                        iconSize: [0, 0],
                        iconAnchor: null,
                        baseClass: 'marker-price',
                        html: `
                            <div class="marker-price-inner">
                                <span class="marker-text">$${_formatBigNum(financials.listingPrice)}</span>
                            </div>
                        `,
                    },
                    popupOptions = useSimpleIcon ? {} : {
                        offset: [0, -20],
                        hasTip: false
                    };

                if (coordinates) {
                    const {lat, lon} = coordinates,
                      marker = L.marker(L.latLng(lat,lon), {
                          icon: this._constructDivIcon(iconOptions),
                          zIndexOffset: 100,
                      });

                    let popup = null;

                    marker.on('mouseover click', () => {
                        if (!popup) popup = this._constructMarkerPopup({
                            data: data[i],
                            template: 'listingMarkerPopup',
                            marker,
                            popupOptions,
                        });

                        this._markerHoverHandler(marker, popup);
                    });

                    this.layers.listings.items[listingId] = marker;
                }
            } else {
                break;
            }
        }
    }

    _setAvailableHomes(count) {
        this.$homesAvailable.html(_formatCurrency(count));
    }

    _addListings() {
        if (this.layers.listings.layer) this.layers.listings.layer.clearLayers();
        this.layers.listings.layer = L.featureGroup(Object.values(this.layers.listings.items));
        this.map.addLayer(this.layers.listings.layer);
    }

    _bindCardMouseEvents($item, $marker) {
        $item.on('mouseenter', () => {
            $marker.fire('mouseover');
            $($marker._icon).addClass('_force-hover-state');
        });
    }

    _markerHoverHandler(marker, popup) {
        $(marker._icon).addClass('_force-hover-state');
        this.map.openPopup(popup);
    }

    _addCards(data) {
        let cards = [];

        for (let i = 0; i < data.length; i++) {
            const
                { listingId } = data[i],
                $card = $(mapTemplates.estateCard(data[i]));

            this._bindCardMouseEvents($card, this.layers.listings.items[listingId]);
            cards.push($card);
        }

        this.$estateCardsList.html('').append(cards);
    }

    _parseYelpMarkers(data) {
        const { businesses } = data;
        this.layers.yelp.items = {};

        for (let i = 0; i < businesses.length; i++) {
            const
                { id, coordinates, categories } = businesses[i],
                { latitude, longitude } = coordinates;

            if (latitude && longitude) {
                const marker = L.marker(L.latLng(latitude, longitude), {
                    icon: this._constructYelpDivIcon({categories, term: this.yelpTerm})
                });

                let popup = null;

                marker.on('mouseover click', () => {
                    if (!popup) popup = this._constructMarkerPopup({
                        data: businesses[i],
                        template: 'yelpMarkerPopup',
                        marker,
                    });

                    this._markerHoverHandler(marker, popup);
                });

                this.layers.yelp.items[id] = marker;
            }
        }
    }

    _addYelpMarkers() {
        if (this.layers.yelp.layer) this.layers.yelp.layer.clearLayers();
        this.layers.yelp.layer = L.layerGroup(Object.values(this.layers.yelp.items));
        this.map.addLayer(this.layers.yelp.layer);
    }

    _addCardsToMapMenu(obj) {
        const { $wrap, data, template, layer } = obj;

        if ($wrap.length) {
            const $closestScroll = $wrap.closest(this.selectors.smoothScroll);

            let cardsArray = [];

            for (let i = 0; i < data.length; i++) {
                const
                    { id, coordinates } = data[i],
                    findId = id || this._coordinatesToId(coordinates),
                    $card = $(mapTemplates[template](data[i]));

                this._bindCardMouseEvents($card, this.layers[layer].items[findId]);
                cardsArray.push($card);
            }

            $wrap.html('').append(cardsArray);
            $closestScroll.trigger('trigger:scroll-top');
        }
    }

    _addYelpCardsToSlider(data) {
        if (this.$yelpCardsSlider.length) {
            const { businesses } = data;
            let yelpCardsArray = [];

            for (let i = 0; i < businesses.length; i++) {
                const
                    { id } = businesses[i],
                    $card = $(mapTemplates.yelpCard(businesses[i]));

                this._bindCardMouseEvents($card, this.layers.yelp.items[id]);
                yelpCardsArray.push($card);
            }

            this.$body.trigger('trigger:init-slider', {
                $sliders: this.$yelpCardsSlider,
                $slides: [yelpCardsArray],
                sliderParams: {
                    slidesToShow: 5,
                    slidesToScroll: 5,
                    responsive: [
                        {
                            breakpoint: 1000,
                            settings: {
                                slidesToShow: 4,
                                slidesToScroll: 4,
                                arrows: false,
                                dots: true,
                            }
                        },
                        {
                            breakpoint: 800,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3,
                                arrows: false,
                                dots: true,
                            }
                        },
                        {
                            breakpoint: 500,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2,
                                arrows: false,
                                dots: true,
                            }
                        },
                    ]
                }
            });
            document.querySelectorAll('.yelp-link').forEach(item => {
                item.addEventListener('click', (event) => {
                    event.preventDefault();
                    window.open($(item).attr('data-url'), '_blank');
                })
            })
        }
    }

    _showTotalFiltersCount() {
        const
            excludeValues = ['location', 'sort'],
            total = this.$filterForm.serializeArray().reduce((sum, { name, value }) => {
                return sum + (excludeValues.indexOf(name) === -1 && value.length ? 1 : 0)
            }, 0);

        this.$totalFiltersCount.attr('data-count', total);
    }

    _filterChangeHandler(event) {
        const
            $changeTarget = $(event.target),
            isPreventSend = $changeTarget.is(this.selectors.preventOnChangeSend),
            $closestPreventSend = $changeTarget.closest(this.selectors.preventOnChangeSend);

        if (isPreventSend || $closestPreventSend.length) {
            const dataPreventBp = isPreventSend ? $changeTarget.data('prevent-bp') : $closestPreventSend.data('prevent-bp');

            if (dataPreventBp) {
                if (window.outerWidth > dataPreventBp) {
                    this._criteriaToUrl();
                    this._loadListings();
                }
            }
        } else {
            this._criteriaToUrl();
            this._loadListings();
        }
    }

    _setFilterState(data) {
        this.flags.preventFilterSending = true;

        for (let key in data) {
            if (data.hasOwnProperty(key)) {
                const $formItem = this.$filterForm.find(`[name="${key}"]`);

                if ($formItem.length) {
                    const
                        isSelect = $formItem.is('select'),
                        isRadio = $formItem.is('[type="radio"]'),
                        isCheckbox = $formItem.is('[type="checkbox"]'),
                        isKeywords = key === 'keywordsArray',
                        value = data[key] || '';

                    console.log('isSelect', isSelect)
                    console.log('isRadio', isRadio)
                    console.log('isCheckbox', isCheckbox)
                    console.log('isKeywords', isKeywords)
                    console.log('value', value)

                    if (isSelect) {
                        $formItem.closest(this.selectors.selectModule).find(`[data-value="${value}"]`).click();
                    } else if (isRadio) {
                        $formItem.filter(`[value="${value}"]`).click();
                    } else if (isCheckbox) {
                        $formItem.prop('checked', false);

                        if ($.isArray(value)) {
                            value.forEach(item => {
                                $formItem.filter(`[value="${item}"]`).click();
                            })
                        } else {
                            $formItem.filter(`[value="${value}"]`).click();
                        }
                    } else if (isKeywords) {
                        $formItem.val(JSON.stringify(value)).trigger('trigger:list:update');
                    } else {
                        $formItem.val(value);
                    }
                }
            }
        }

        this.flags.preventFilterSending = false;
    }

    _urlToCriteria(url) {
        const
            { urlToCriteria } = this.dataParams,
            requestParameters = {
                url: urlToCriteria,
                contentType: 'application/json',
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    uri: url
                })
            };

        const request = $.ajax(requestParameters).done((data) => {
            this._setFilterState(data);
            this._loadListings();
        });

        this._showPreloader(request, 'utc');
        this._errorHandler(request);
    }

    _criteriaToUrl() {
        const
            { criteriaToUrl } = this.dataParams,
            { pathname } = window.location,
            regexp = '/for-sale/',
            requestParameters = {
                url: criteriaToUrl,
                contentType: 'application/json',
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    filters: _getFormDataObj(this.$filterForm),
                    location: this.boxState.current
                })
            };
        if (pathname.match(regexp)) {
            const request = $.ajax(requestParameters).done((data) => {
                this._changeLocation(data);
            });

            this._showPreloader(request, 'ctu');
            this._errorHandler(request);
        }
    }

    _loadListings() {
        const
            { path } = this.dataParams,
            requestParameters = {
                url: path,
                contentType: 'application/json',
                type: 'POST',
                dataType: 'json',
                data: _getJsonFormData(this.$filterForm, true)
            };
        const request = $.ajax(requestParameters).done((data) => {
            const { listings, total, center } = data;

            this._setAvailableHomes(total);
            this._parseListingsData(listings);
            this._addListings();
            this.map.closePopup();

            if (this.$estateCardsPagination.length) this._initPagination(listings);

            if (this.flags.fitMarkers && total && !center) {
                const bounds = this.layers.listings.layer.getBounds();

                this.flags.fitMarkers = false;

                if (Object.keys(bounds).length) {
                    this.flags.preventMapMoveEndHandler = true;

                    this.map.fitBounds(bounds, {
                        padding: [50, 50],
                    });
                }
            }

            this.$iw[total ? 'removeClass' : 'addClass']('_not-found');
            if (this.$mapTitle.length) {
                if (!window.location.pathname.includes('search-listings')) {
                    let locationLength = window.location.pathname.split('/')[2].split(',').length;

                    let text = this.$mapTitle.data(total ? 'default-text' : 'not-found-text').replace('{value}', total)
                    text = text.replace('{country}', locationLength === 4 ? '' : this.$mapTitle.data('country').replace(',', ', '));
                    this.$mapTitle.text(text);
                } else {
                    let text = this.$mapTitle.data(total ? 'default-text' : 'not-found-text').replace('{value}', total);
                    text = text.replace('{country}', '');
                    this.$mapTitle.text(text);
                }
            }

            // if (center) {
            //     let { lat, lng, zoom } = center
                // this.map.setView([lat, lng], zoom);
            // }
        });

        this._showPreloader(request, 'listing');
        this._errorHandler(request);
    }

    _yelpSearch() {
        const
            { yelpPath } = this.dataParams,
            mapBounds = this.map.getBounds(),
            center = this.map.getCenter(),
            { lat, lng } = center,
            centerEast = L.latLng(lat, mapBounds.getEast()),
            centerNorth = L.latLng(mapBounds.getNorth(), lng),
            distW = parseInt(center.distanceTo(centerEast)),
            distH = parseInt(center.distanceTo(centerNorth)),
            searchRadius = Math.min(Math.max(distW, distH), 40000),
            term = this.yelpTerm || 'all',
            requestParameters = {
                url: yelpPath,
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    term: term,
                    // location: 'Chicago, IL',
                    latitude: lat,
                    longitude: lng,
                    radius: searchRadius,
                    // categories: ['bars', 'french'],
                    // locale: 'en_US',
                    limit: 50,
                    // offset: 2,
                    sort_by: 'best_match',
                    // price: '1,2,3',
                    // open_now: true,
                    // open_at: 1234566,
                    // attributes: ['hot_and_new','deals']
                }),
            };

        this.map.closePopup();

        const request = $.ajax(requestParameters).done((data) => {
            this._parseYelpMarkers(data);
            this._addYelpMarkers();
            this._addYelpCardsToSlider(data);

            this._addCardsToMapMenu({
                $wrap: this.$yelpCardsMenu,
                data: data.businesses,
                layer: 'yelp',
                template: 'yelpSimpleCard',
            });
        });

        this._showPreloader(request, 'yelp');
        this._errorHandler(request);
    }

    _runRefreshTimer(delay) {
        clearTimeout(this.timers.refresh);

        this.timers.refresh = setTimeout(() => {
            const { current, prev } = this.boxState;

            if (current) {
                if (current !== prev) {
                    this._refreshMap();
                }
            } else {
                this._refreshMap();
            }
        }, delay || this.settings.refreshDelay);
    }

    _isBoxChanged(layer) {
        const changed = this.boxState.current !== layer.lastBox;
        layer.lastBox = this.boxState.current;
        return changed;
    }

    _refreshMap() {
        const
            { refreshLayers=[] } = this.dataParams,
            refreshArray = JSON.parse(JSON.stringify(refreshLayers));

        if (refreshArray.indexOf(this.layerOption) === -1) refreshArray.push(this.layerOption);
        this.map.closePopup();

        refreshArray.forEach(item => {
            switch (item) {
                case 'listings':
                    if (this._isBoxChanged(this.layers.listings)) {
                        this._criteriaToUrl();
                        this._loadListings();
                    }
                    this._removeLegend()
                    break;

                case 'schools':
                    if (this._isBoxChanged(this.layers.schools)) {
                        this._loadSchools();
                    } else {
                        this._addSchools(this.layers.schools.items);
                    }
                    this._removeLegend()
                    break;

                case 'busStops':
                    if (this._isBoxChanged(this.layers.busStops)) {
                        this._loadBusStops();
                    } else {
                        this._addBusStops(this.layers.busStops.items);
                    }
                    this._removeLegend()
                    break;

                case 'crime':
                    if (this._isBoxChanged(this.layers.crime)) {
                        this._loadCrime();
                    } else {
                        this._addCrime();
                    }
                    this._addLegend()
                    break;

                case 'demography':
                    if (this._isBoxChanged(this.layers.demography)) {
                        this._loadDemography();
                    } else {
                        this._addDemography(this.layers.demography.items);
                    }
                    break;
                case 'yelp':
                    if (this._isBoxChanged(this.layers.yelp)) {
                        this._yelpSearch();
                    } else {
                        this._addYelpMarkers();
                    }
                    this._removeLegend()
                    break;

                case 'commute':
                    if (this.layers.commute.items) this._addRoutes();
                    this._removeLegend()
                    break;
            }
        });
    }

    _initPagination(data) {
        const { cardsPerPage=48, maxMarkersCount=2400 } = this.dataParams;

        this.$estateCardsPagination.pagination({
            dataSource: data,
            pageSize: cardsPerPage,
            pageRange: 1,
            hideWhenLessThanOnePage: true,

            callback: (currentPageData, p) => {
                const
                    { pageNumber, pageSize, direction } = p,
                    pageCounterFrom = pageSize * (pageNumber - 1),
                    { from, to } = this.estateCardsShowed;

                this.$cardsScrollWrap.trigger('trigger:scroll-top');

                if ((to <= pageCounterFrom) && (direction === 1)) {
                    this._parseListingsData(data, pageCounterFrom);
                    this._addListings();
                } else if (direction === -1) {
                    if ((pageCounterFrom + pageSize) <= from) {
                        this._parseListingsData(data, Math.max((pageSize * pageNumber) - maxMarkersCount, 0));
                        this._addListings();
                    }
                }

                if (this.flags.pasteCards) {
                    this._addCards(currentPageData);
                } else {
                    this.flags.pasteCards = true;
                }

                this._checkSliders();
            }
        })
    }

    _setBox() {
        if (!this.boxState.current) {
            this.boxState.current = this.$locationInput.length ? this.$locationInput.val() : this.map.getBounds();
        } else {
            this.boxState.prev = this.boxState.current;
            this.boxState.current = this._coordsObjectToString(this.map.getBounds());
            if (this.$locationInput.length) this.$locationInput.val(this.boxState.current);
        }
    }

    _setParamsOnResize() {
        clearTimeout(this.timers.resize);

        this.timers.resize = setTimeout(() => {
            if (this.$estateCardsWrap.length) {
                this.estateCardsWrapPosition = this.$estateCardsWrap[0].getBoundingClientRect();
            }
        }, this.settings.resizeUpdateDelay);
    }

    _checkSliders() {
        const $sliders = this.$estateCardsList.find('.js-estate-card-slider:not(.slick-initialized)');

        $sliders.each((key, item) => {
            const
                { top } = item.getBoundingClientRect(),
                outOfBottom = top > this.estateCardsWrapPosition.bottom;

            if (!outOfBottom) {
                const
                    $currentSlider = $(item),
                    isInit = $currentSlider.data('init');

                if (!isInit) {
                    $currentSlider.data('init', true);

                    this.$body.trigger('trigger:init-slider', {
                        $sliders: $currentSlider
                    });
                }
            } else {
                return false;
            }
        })
    }

    _disableMapInteraction(disable) {
        const method = disable ? 'disable' : 'enable';

        this.map.dragging[method]();
        this.map.touchZoom[method]();
        this.map.doubleClickZoom[method]();
        this.map.scrollWheelZoom[method]();
        this.map.boxZoom[method]();
        this.map.keyboard[method]();
        if (this.map.tap) this.map.tap[method]();
    }

    _showPreloader(request, name='request') {
        const
            startTime = $.now(),
            mod = `_loading-${name}`;

        this.$mapContainer.addClass(mod);

        request.always(() => {
            setTimeout(() => {
                this.$mapContainer.removeClass(mod);
            }, Math.max(0, 1300 - ($.now() - startTime)))
        });
    }

    _errorHandler(request) {
        request.fail((error) => {
            _errorHandler(error);
        });
    }

    _clearLayers(exception='') {
        const { preventClear=[] } = this.dataParams;

        this.map.closePopup();

        for (let key in this.layers) {
            const
                currentLayer = this.layers[key],
                isException = key === exception,
                prevent = preventClear.indexOf(key) !== -1;

            if (currentLayer && !isException && !prevent) {
                if (key === 'draw') {
                    this._cancelDraw();
                } else {
                    const { layer } = currentLayer;
                    if (layer) currentLayer.layer.clearLayers();
                }
            }
        }
    }

    _coordinatesToId(coordinates) {
        return _getPureNumber(JSON.stringify(Object.values(coordinates))).split('.').join('');
    }

    _coordsStringToObj(string) {
        const array = string.split(',');

        return {
            northEast: {
                lat: array[0],
                lon: array[1]
            },
            southWest: {
                lat: array[2],
                lon: array[3]
            }
        }
    }

    _coordsObjectToString(obj) {
        const { _northEast, _southWest } = obj;
        return `${Object.values(_northEast).join(',')},${Object.values(_southWest).join(',')}`;
    }

    _addRoutesCards() {
        this.$commuteRoutesMenu.html('');

        Object.values(this.layers.commute.items).forEach(item => {
            const
                { loaded } = item,
                $routeCard = $(mapTemplates.routeCard(item));

            if (loaded) this._bindCardMouseEvents($routeCard, item.marker);
            this.$commuteRoutesMenu.append($routeCard);
        });
    }

    _setRoutesData(destinations, data) {
        data.forEach((item, destinationIndex) => {
            if (item !== 'prevent') {
                if (item && item.routes.length && item.routes[0].sections.length) {
                    const
                        destinationCoordinates = destinations[destinationIndex].split(','),
                        marker = L.marker(L.latLng(destinationCoordinates[0],destinationCoordinates[1]), {
                            icon: this._constructBigIcon({ mod: `icon-commute-${this.commuteOption}-big` }),
                            zIndexOffset: 100,
                        }),
                        address = this.layers.commute.items[destinations[destinationIndex]].address;

                    let totalTime = 0,
                        lines = [],
                        popup = null;

                    item.routes[0].sections.forEach(routeSection => {
                        const
                            { summary, polyline, transport } = routeSection,
                            polylineCoordinates = decode(polyline).polyline,
                            transportPolylineStyles = this.polylineStyles[transport.mode] || {};

                        lines.push(L.polyline(polylineCoordinates, {
                            interactive: false,
                            ...this.polylineStyles.default,
                            ...transportPolylineStyles
                        }));

                        totalTime += summary.duration;
                    });

                    marker.on('mouseover click', () => {
                        if (!popup) popup = this._constructMarkerPopup({
                            data: {
                                coordinates: {
                                    lat: destinationCoordinates[0],
                                    lng: destinationCoordinates[1],
                                },
                                ...this.layers.commute.items[destinations[destinationIndex]]
                            },
                            template: 'routeCard',
                            marker,
                            popupOptions: {
                                offset: [0, -55],
                            }
                        });

                        this._markerHoverHandler(marker, popup);
                    });

                    this.layers.commute.items[destinations[destinationIndex]] = {
                        lines,
                        marker,
                        address,
                        time: totalTime,
                        option: this.commuteOption,
                        destination: destinations[destinationIndex],
                        loaded: true,
                    };
                } else {
                    this.layers.commute.items[destinations[destinationIndex]] = {
                        ...this.layers.commute.items[destinations[destinationIndex]],
                        error: "Couldn't find a route.",
                        destination: destinations[destinationIndex],
                        option: this.commuteOption,
                        loaded: false,
                    };
                }
            }
        });
    }

    _refreshRoutes() {
        const destinations = this.layers.commute.items ? Object.keys(this.layers.commute.items) : [];

        if (destinations.length) {
            $.when(...destinations.map(item => {
                const route = this.layers.commute.items[item];

                if (route.loaded && (route.option === this.commuteOption)) {
                    return 'prevent';
                } else {
                    return this._getRoute(item);
                }
            })).done((...rest) => {
                if ($.isArray(rest[0]) || rest[0] === 'prevent') {
                    this._setRoutesData(destinations, rest.map(item => {
                        return item === 'prevent' ? 'prevent' : item[0];
                    }));
                } else {
                    this._setRoutesData(destinations, [rest[0]]);
                }

                this._addRoutes();
                this._addRoutesCards();
            });
        }
    }

    _removeRoute(destination) {
        delete this.layers.commute.items[destination];
    }

    _addRoutes() {
        if (this.layers.commute.layer) this.layers.commute.layer.clearLayers();

        const routesItems = [];

        Object.values(this.layers.commute.items).forEach(item => {
            if (item.loaded) {
                routesItems.push(item.marker);

                item.lines.forEach(item => {
                    routesItems.push(item);
                });
            }
        });

        if (routesItems.length) {
            this.layers.commute.layer = L.featureGroup(routesItems);
            this.map.addLayer(this.layers.commute.layer);
        }
    }

    _addRouteDestination(props) {
        const
            { coordinates, address } = props,
            { latitude, longitude } = coordinates,
            coordsString = `${latitude},${longitude}`;

        if (!this.layers.commute.items) this.layers.commute.items = {};

        if (!this.layers.commute.items[coordsString]) {
            this.layers.commute.items[coordsString] = {
                loaded: false,
                address: address,
            };
        }
    }

    _getRoute(coordsString) {
        const
            { center } = this.dataParams,
            origin = `${center.lat},${center.lon}`,
            request = $.ajax({
                url: `https://router.hereapi.com/v8/routes?apiKey=${this.hereApiKey}&transportMode=${this.commuteOption}&origin=${origin}&destination=${coordsString}&return=polyline,summary`,
                type: 'GET',
            });

        this._showPreloader(request, `route-${coordsString}`);
        this._errorHandler(request);

        return request;
    }

    _getColor(data, value) {
        switch (data[value.value]) {
            case 'Low':
                return this.polygonStyles.low;
            case 'Below Average':
                return this.polygonStyles.below_average;
            case 'Average':
                return this.polygonStyles.average;
            case 'Above Average':
                return this.polygonStyles.above_average;
            case 'High':
                return this.polygonStyles.high;
            default:
                return this.polygonStyles.no_data;
        }
    }

    _getCurrentDemographyValue(type) {
        let $form = this.$demographyDropdownForms.filter((key, element) => {
            return $(element).data('type') === type;
        })

        let $inputs = $form.find('input, select')
        let $data = $inputs.serializeArray()
        return $data[0]
    }

    _addLegendWithLegendType(type) {
        let $form = this.$demographyDropdownForms.filter((key, element) => {
            return $(element).data('type') === type;
        })

        this._addLegend($form.find(`input:checked`), true)
    }

    _addLegend(demography = null, remove = false) {
        if (remove) this._removeLegend();
        if (remove || !document.querySelector('.legend')) {
            let legend = L.control({position: 'bottomright'});
            legend.onAdd = function (map) {
                let div = L.DomUtil.create("div", "legend");
                if (demography) {
                    console.log(demography, 'this.$currentDemography')
                    div.innerHTML += `<span class="legend__title">${demography.data('legend')}</span>`
                }
                div.innerHTML += '<i style="background: #8E8D8D"></i><span>No data</span>';
                div.innerHTML += '<i style="background: #0000FF"></i><span>Low</span>';
                div.innerHTML += '<i style="background: #00FF00"></i><span>Below Average</span>';
                div.innerHTML += '<i style="background: #FFFF00"></i><span>Average</span>';
                div.innerHTML += '<i style="background: #FF6600"></i><span>Above Average</span>';
                div.innerHTML += '<i style="background: #FF0000"></i><span>High</span>';
                return div;
            }
            legend.addTo(this.map)
        }
    }

    _getLegendName(value) {
        let input = document.querySelector(`value=${value}`)
        return $(input).data('legend')
    }

    _removeLegend() {
        if (document.querySelector('.legend')) {
            document.querySelector('.legend').remove()
        }
    }
}

$(document).ready(() => {
    console.log('### MAP ###');

    if ($('#estate-map').length) new EstateMap('#estate-map');

    $('body').on('trigger:init-map', (e, id) => {
        const $map = $(id);
        if ($map.length && !$map.hasClass('map-initialized')) new EstateMap(id);
    });
});

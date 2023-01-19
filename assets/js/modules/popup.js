jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            this.initEvents();
        },
        
        initCache() {
            this.$document = $(document);
            this.$body = $('body');
            this.$popups = $('.js-popup');
            this.$overlay = $('.js-overlay');
            this.$btn_close = $('.js-close-popup');
            this.$popupPropsButton = $('.js-popup-props-button');
            this.overlayMods = [];
    
            this.templates = {
                estateSliderItem: (img) => `
                    <div class="fs-slider-item">
                        <div class="round-img-wrap">
                            <img src=${img} alt="#" class="of"/>
                        </div>
                    </div>
                `,
            }
        },

        initEvents() {
            $_.$btn_close.on('click', () => {
                $_._closePopup();
            });
            
            $_.$overlay.on('click', () => {
                $_._closePopup();
            });
            
            $_.$body.on('click', '.js-call-popup', (e) => {
                e.preventDefault();
                $_._openPopup($(e.currentTarget).data('popup'));
            });

            $_.$body.on('trigger:open-popup', (e, data) => {
                $_._openPopup(data);
            });
            
            $_.$body.on('show:ty-popup', (e, data, delay) => {
                $_._showTyPopup(data, delay);
            });
            
            $_.$body.on('trigger:init-popup-slider', (e, data) => {
                $_._initPopupSlider(data);
            });

            $_.$document.on('keyup', (e) => {
                if (e.key === 'Escape') $_._closePopup();
            });
        },
        
        _clearOverlay() {
            $_.$overlay.removeClass($_.overlayMods.join(' '));
        },
    
        _initPopupSlider(data) {
            const
                { images, index, sliderProps={} } = data,
                $popup = $('.js-slider-popup'),
                $popupSlider = $popup.find('.js-slider'),
                slides = images.map(item => $_.templates.estateSliderItem(item)).join('');

            $_.$body.trigger('trigger:init-slider', {
                $sliders: $popupSlider,
                $slides: [slides],
                sliderParams: {
                    initialSlide: index,
                    infinite: true,
                    speed: 150,
                    fade: true,
                    ...sliderProps
                }
            });

            $_.$popups.removeClass('_active');
            $_._clearOverlay();
            $popup.addClass('_active');
        },
    
        _showTyPopup(data, delay) {
            const
                { target, tyText } = data,
                { title, subtitle } = tyText || {},
                $popup = $('.js-popup-' + target),
                $title = $popup.find('.js-ty-title'),
                $subtitle = $popup.find('.js-ty-subtitle'),
                titleDefault = $title.data('default-text'),
                subtitleDefault = $subtitle.data('default-text'),
                showDelay = delay || 500,
                hideDelay = showDelay + 7000;

            $_.$body.trigger('trigger:disable-scroll');
            $_.$popups.removeClass('_active');
            
            $title.html(title || titleDefault);
            $subtitle.html(subtitle || subtitleDefault);
            
            setTimeout(() => {
                $_._clearOverlay();
                $popup.add($_.$overlay).addClass('_active');
            }, showDelay);
            
            setTimeout(() => {
                if ($popup.hasClass('_active')) $popup.add($_.$overlay).removeClass('_active');
            }, hideDelay);
        },

        _openPopup(data) {
            const
                { target, fire_click_selector, show_overlay, set_inputs_value, set_props_button_data } = data,
                $popup = $('.js-popup-' + target),
                $recaptcha = $popup.find('.js-recaptcha');

            $_.$body.trigger('trigger:disable-scroll');

            if ($recaptcha.length) $_.$body.trigger('trigger:init-recaptcha');

            $_.$popups.removeClass('_active');
            $_._clearOverlay();

            if (set_inputs_value) {
                set_inputs_value.forEach(({name, value}) => {
                    const $input = $popup.find(`[name="${name}"]`);
                    if ($input.length && value) $input.val(value);
                });
            }

            if (fire_click_selector) {
                $popup.find(fire_click_selector).click();
            }

            if (set_props_button_data) {
                const data = $_.$popupPropsButton.data('popup');

                $popup.find($_.$popupPropsButton).data('popup', {
                    ...data,
                    ...set_props_button_data
                });
            }

            $popup.addClass('_active');
            if (show_overlay) $_.$overlay.addClass('_active');
        },
        
        _closePopup() {
            const
                $popup_active = $('.js-popup._active'),
                $form = $popup_active.find('form');

            $_.$body.trigger('trigger:enable-scroll');
            $_.$overlay.removeClass('_active');
            $popup_active.removeClass('_active');
            sessionStorage.removeItem('extra_request');

            if ($form.length && $form.hasClass('_ty')) {
                const $collapseBlock = $form.find('.js-collapse');

                $form.removeClass('_ty');
                if ($collapseBlock) $collapseBlock.attr('style', '');
            }
        }
    };
    
    $(document).ready(() => {
        $_.init();
    });
});
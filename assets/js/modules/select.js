jQuery(function($) {
    const $_ = {
        init() {
            this.initCache();
            this.initEvents();
            this.initSelects();
        },
        
        initCache() {
            this.$body = $('body');
            this.$module = $('.js-select-module');
            this.$select = $('.js-select-module-select');
            this.$container = $('.js-select-module-container');
            this.$optContainer = $('.js-select-module-options');
            this.$dropdown = $('.js-select-dropdown');
            this.$btn = $('.js-select-module-text-block');
        },

        initEvents() {
            $_.$container.on('click', (e) => {
                const
                    $el = $(e.currentTarget),
                    $relatedModule = $el.closest($_.$module);
                
                $relatedModule.toggleClass("_active");
            });

            $_.$dropdown.on('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    $parentScroll = $currentTarget.closest('.js-smooth-scroll');

                if ($parentScroll.length) $parentScroll.trigger('trigger:update-scroll');
            });
        },
        
        initSelects() {
            $_.$module.each((key, item) => {
                const
                    $item = $(item),
                    $select = $item.find($_.$select),
                    $optContainer = $item.find($_.$optContainer),
                    $btn = $item.find($_.$btn);
                
                $_._buildOptions($select, $optContainer, $btn);
                $_._addEvents($item, $btn, $select);
            });
        },
        
        _buildOptions($select, $optContainer, $btn) {
            let $options = $select.find("option");

            for (let i = 0; i < $options.length; i++) {
                const
                    $item = $($options[i]),
                    isDisabled = $item.is(':disabled'),
                    isSelected = $item.is(':selected'),
                    selectedClass = isSelected ? "_active" : "",
                    icon = $item.data('icon'),
                    value = $item.val(),
                    title = $item.text();

                if (!isDisabled) {
                    $optContainer.append(`
                        <div class="select-module__option js-select-module-option ${icon} ${selectedClass}" 
                            data-value="${value}"
                            data-text="${title}"
                            data-icon="${icon}"
                        >
                            ${title}
                            <i class="icon"></i>
                        </div>
                    `);
                }
                
                if (isSelected) {
                    $select.closest($_.$module).addClass('_selected');
                    this.$container.addClass(icon)
                    if (title.length) $btn.text(title);
                }
            }
        },
        
        _addEvents($module, $btn, $select) {
            const $options = $module.find(".js-select-module-option");

            $select.on('change', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    value = $currentTarget.val(),
                    $relatedOption = $options.filter(`[data-value="${value}"]`);

                if (!$relatedOption.hasClass('_active')) {
                    $relatedOption.trigger('click', { preventSelectChange: true })
                }
            })

            $options.on("click", (e, data={}) => {
                const
                    { preventSelectChange } = data,
                    $currentTarget = $(e.currentTarget),
                    value = $currentTarget.data("value"),
                    title = $currentTarget.text(),
                    icon = $currentTarget.data('icon');

                console.log('icon', icon)
                $options.add($module).removeClass("_active");
                $currentTarget.addClass("_active");

                $module.addClass('_selected');
                this.updateIcon(this.$container, icon);
                $btn.text(title);
                $select.val(value);

                if (!preventSelectChange) $select.trigger('change');
                if ($select.closest('form').length) $select.valid();
            });
        },

        updateIcon($module, icon) {
            if ($module.hasClass('icon-arrow-up')) $module.removeClass('icon-arrow-up');
            if ($module.hasClass('icon-arrow-down')) $module.removeClass('icon-arrow-down');
            $module.addClass(icon);
        }
    };

    $(document).ready(() => {
        $_.init();
    });
});

jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            this.initEvents();
        },

        initCache() {
            this.$body = $('body');
            this.scrollDisabled = false;
        },

        initEvents() {
            $_.$body.on('trigger:disable-scroll', () => {
                $_._disableScroll();
            });

            $_.$body.on('trigger:enable-scroll', () => {
                $_._enableScroll();
            });
        },

        _disableScroll() {
            if (!$_.scrollDisabled) {
                const scrollY = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollY}px`;
                document.body.style.overflowY = 'scroll';
                $_.scrollDisabled = true;
            }
        },

        _enableScroll() {
            if ($_.scrollDisabled) {
                const scrollY = document.body.style.top;
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.overflowY = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
                $_.scrollDisabled = false;
            }
        },
    };

    $(document).ready(() => {
        $_.init();
    });
});

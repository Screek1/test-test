jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            if ($_.$map.length) this.initApi();
        },

        initCache() {
            this.$map = $('#google-street-view');
        },

        initApi() {
            const
                { apiUrl, initImmediately } = $_.$map.data('props'),
                script = document.createElement("script");

            script.type = "text/javascript";
            script.src = apiUrl;
            script.onload = $_._createMap;

            if (initImmediately) {
                $_._loadScript(script);
            } else {
                $_.$map.on('trigger:init', () => {
                    if (!$_.$map.data('loaded')) {
                        $_.$map.data('loaded', true);
                        $_._loadScript(script);
                    }
                });
            }
        },

        _loadScript(script) {
            document.getElementsByTagName("head")[0].appendChild(script);
        },

        _createMap() {
            const { coordinates } = $_.$map.data('props');
            const fenway = { lat: parseFloat(coordinates.lat), lng: parseFloat(coordinates.lon) };
            new google.maps.StreetViewPanorama(
              document.getElementById("google-street-view"),
              {
                  position: fenway,
                  pov: {
                      heading: 34,
                      pitch: 10,
                  },
              }
            );
        }
    };

    $(document).ready(() => {
        $_.init();
    });
});

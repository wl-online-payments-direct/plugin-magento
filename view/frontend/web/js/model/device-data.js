define([], function () {
    'use strict';

    return {
        /**
         * @returns {Object}
         */
        getData: function () {
            return {
                ColorDepth: window.screen.colorDepth,
                JavaEnabled: window.navigator.javaEnabled(),
                Locale: window.navigator.language,
                ScreenHeight: window.screen.height,
                ScreenWidth: window.screen.width,
                TimezoneOffsetUtcMinutes: (new Date()).getTimezoneOffset()
            };
        }
    }
});

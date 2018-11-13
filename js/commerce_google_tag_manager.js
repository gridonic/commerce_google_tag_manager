/**
 * @file
 * Defines Javascript behaviors for the commerce_google_tag_manager module.
 */

(function ($, window, drupalSettings) {
    'use strict';

    $(function() {
        if (!drupalSettings) {
            return;
        }

        var settings = drupalSettings.commerceGoogleTagManager || {};
        var url = settings.eventsUrl;
        var dataLayerVariable = settings.dataLayerVariable;

        if (!dataLayerVariable || !window.hasOwnProperty(dataLayerVariable)) {
            return;
        }

        var dataLayer = window[dataLayerVariable];

        $.get(url, function(data) {
            if (data && data.length) {
                data.forEach(function(eventData) {
                    dataLayer.push(eventData);
                });
            }
        });
    });
})(jQuery, window, drupalSettings);

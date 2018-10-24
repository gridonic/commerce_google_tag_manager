/**
 * @file
 * Defines Javascript behaviors for commerce GTM enhanced ecommerce module.
 */

(function ($, window, drupalSettings) {
    'use strict';

    $(function() {
        if (!drupalSettings) {
            return;
        }

        var settings = window.drupalSettings.commerceGtmEnhancedEcommerce || {};
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

# Commerce Google Tag Manager Enhanced Ecommerce

Provides Commerce integration for Enhanced Ecommerce via Google Tag Manager.

## Features

The module tracks the following [Enhanced Ecommerce](https://developers.google.com/tag-manager/enhanced-ecommerce)
events:

* **Views of Product Details** when viewing a commerce product entity.
* **Additions or Removals from cart**
* **Checkout** with the possibility to track checkout options
* **Purchases**

All events are tracked server-side and stored in the session. The next "regular" page view
loads all stored events via ajax and pushes them to Google Tag Manager via data layer.

The module offers a service to track other Enhanced Ecommerce events such as _Product Impressions_.

## Configuration

The module does (currently) not offer any configuration options. The configuration is
handled inside Google Analytics and Google Tag Manager.

### Google Analytics

* Enable `Ecommerce` and `Enhanced Ecommerce Reporting` in the settings of your property
* Add labels for each checkout step under `Checkout Labeling` (optional) 

### Google Tag Manager

You need to create separate tags and triggers for each of the tracked Enhanced Ecommerce events.

First of all, create triggers of event type `Custom Event` with the following event
names:

* `addToCart`
* `removeFromCart`
* `productDetailViews`
* `checkout`
* `purchase`

Next, create tags for each trigger:

* Set the tag type to `Google Analytics - Universal Analytics`
* Set the track type to `Event`
* Set the category to `Ecommerce`
* Set the corresponding action, e.g. `Add to Cart`
* Reference your Google Analytics settings.
Important: The settings need the options `Enable Enhanced Ecommerce Features` and `Use data layer`
to be activated. You can either create a new Google Analytics settings variable or override these
options for the existing Google Analytics setting.
* Select the correct trigger, e.g. `addToCart`

## Testing and Debugging

* Enable Preview in Google Tag Manager
* In the same browser, navigate to the website and check that the debug window is displayed
* Verify that the Enhanced Ecommerce tags are fired e.g. by visiting a product page, adding 
products to the cart or going through the checkout process.
* Check the Ecommerce reports in Google Analytics.

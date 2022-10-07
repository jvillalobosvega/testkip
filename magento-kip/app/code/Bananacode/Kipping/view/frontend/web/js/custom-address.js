define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            var shippingAddress = quote.shippingAddress();
            if(shippingAddress) {
                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }
                if (shippingAddress.customAttributes != undefined) {
                    $.each(shippingAddress.customAttributes, function (key, value) {
                        if (value !== undefined) {
                            if (typeof value === 'string' || value instanceof String) {
                                shippingAddress['customAttributes'][key] = value;
                                shippingAddress['extension_attributes'][key] = value;
                            } else {
                                shippingAddress['customAttributes'][value.attribute_code] = value.value;
                                shippingAddress['extension_attributes'][value.attribute_code] = value.value;
                            }
                        }
                    });
                }
            }

            var billingAddress = quote.billingAddress();
            if(billingAddress) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function (key, value) {
                        if (value !== undefined) {
                            if (typeof value === 'string' || value instanceof String) {
                                billingAddress['customAttributes'][key] = value;
                                billingAddress['extension_attributes'][key] = value;
                            } else {
                                billingAddress['customAttributes'][value.attribute_code] = value.value;
                                billingAddress['extension_attributes'][value.attribute_code] = value.value;
                            }
                        }
                    });
                }
            }
            return originalAction(messageContainer);
        });
    };
});

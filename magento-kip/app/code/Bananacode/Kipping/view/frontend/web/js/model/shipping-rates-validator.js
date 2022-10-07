define(
    [
        'jquery',
        'mageUtils',
        'Bananacode_Kipping/js/model/shipping-rates-validation-rules',
        'mage/translate'
    ],
    function ($, utils, validationRules, $t) {
        'use strict';
        return {
            validationErrors: [],
            validate: function(address) {
                var self = this;
                if(address['custom_attributes']) {
                    this.validationErrors = [];
                    $.each(validationRules.getRules(), function(field, rule) {
                        if (rule.required && utils.isEmpty(address['custom_attributes'][field])) {
                            var message = $t('Field ') + field + $t(' is required.');
                            self.validationErrors.push(message);
                        }
                    });
                    return !Boolean(this.validationErrors.length);
                }
            }
        };
    }
);

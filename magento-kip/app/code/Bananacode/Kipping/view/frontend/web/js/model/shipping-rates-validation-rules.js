define(
    [],
    function () {
        'use strict';
        return {
            getRules: function() {
                return {
                    "address_latitude": {
                        'required': true
                    },
                    "address_longitude": {
                        'required': true
                    }
                };
            }
        };
    }
)

/*
 *
 * JS Imports
 *
 * */
import {Header, Forms, Gif, Map, Modals, Products, Sliders, Kipping, Kipckout} from "./components";

import {Account, Home, Secondary, ShoppingLists, Recipes, TaxDocument} from "./pages";

import {initScroll} from "./helpers/service.scroll";

import {event, observeAdd, selectDoc} from "./utils/wonder";
import {initInput} from "./helpers/service.inputs";
import {observeShippingInformation} from "./components/component.kipckout";

/*
 *
 * App Initialization
 *
 * */
(function () {
    observeAdd(selectDoc('html'), function (nodes) {
        if (!Array.isArray(nodes)) {
            nodes = [].push(nodes);
        }

        nodes.map(node => {
            if (node.id) {
                /**
                 * Checkout
                 */
                try {
                    if (typeof node.id === 'string' || node.id instanceof String) {
                        if (node.id.includes('checkout-shipping-method-load')) {
                            let proxied = window.XMLHttpRequest.prototype.send;
                            window.XMLHttpRequest.prototype.send = function() {
                                let pointer = this,
                                    intervalId = window.setInterval(function(){
                                        if(pointer.readyState !== 4){
                                            return;
                                        }
                                        observeShippingInformation(pointer);
                                        clearInterval(intervalId);
                                    }, 1);
                                return proxied.apply(this, [].slice.call(arguments));
                            };

                            let mapsInitiated = false,
                                kippingInitiated = false,
                                kipInitiated = false;
                            setTimeout(function () {
                                let checkoutInterval = window.setInterval(function () {
                                    if(window.checkoutConfig && !kippingInitiated) {
                                        console.log('Initializing kipping component...')
                                        kippingInitiated = true;
                                        new Kipping();
                                    }

                                    if(selectDoc('[name*="latitude"]') && !mapsInitiated) {
                                        console.log('Initializing map component...')
                                        mapsInitiated = true;
                                        new Map();
                                    }

                                    if(mapsInitiated && kippingInitiated && !kipInitiated) {
                                        console.log('Initializing kip component...')
                                        kipInitiated = true;
                                        new Kipckout(true);
                                        new Gif();
                                    }

                                    if(mapsInitiated && kippingInitiated && kipInitiated) {
                                        clearInterval(checkoutInterval);
                                    }
                                }, 1);
                            }, 1500)
                        }
                    }
                } catch (e) {

                }
            }

            if (node.className) {
                if (typeof node.className === 'string' || node.className instanceof String) {
                    if (node.className.includes('page-footer')) {

                        new Gif();

                        new Header();

                        new Map();

                        new Modals();

                        new Secondary();

                        new Products();

                        new ShoppingLists();

                        new TaxDocument();

                        new Home('#kip-homepage');

                        new Sliders();

                        new Account('.account-tabs');

                        new Account('.customer-account-forgotpassword');

                        new Account('#logged-header');

                        new Account('#kip-loyalty');

                        new Recipes('#recipes-index');

                        new Kipckout(selectDoc('.checkout-onepage-success'));

                        initScroll();

                        event(document, 'keypress', function (e) {
                            let keyNum;
                            if(window.event) { // IE
                                keyNum = e.keyCode;
                            } else if(e.which){ // Netscape/Firefox/Opera
                                keyNum = e.which;
                            }
                            if(keyNum === 13) {
                                if(document.activeElement) {
                                    if(document.activeElement.id) {
                                        if(document.activeElement.id.includes('map')) {
                                            e.preventDefault();
                                        }
                                    }
                                }
                            }
                        });

                        new Forms();
                    }

                    /**
                     * Checkout
                     */
                    if (node.className.includes('input-text')) {
                        initInput(node)
                    }
                }
            }
        })
    })
})();

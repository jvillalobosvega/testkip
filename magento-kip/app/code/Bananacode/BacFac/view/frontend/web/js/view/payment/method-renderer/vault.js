/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Bananacode_BacFac/js/view/payment/method-renderer/bacfac'
], function (
    ko,
    jQuery,
    VaultComponent,
    $t,
    globalMessageList,
    bacFacComponent
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            active: false,
            template: 'Bananacode_BacFac/payment/vault',
            creditCardVerificationNumber: '',
            is_authorized: false,
            data_3ds: null,
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'creditCardVerificationNumber',
                ]);

            return this;
        },

        /**
         * Init component
         */
        initialize: function () {
            let self = this;

            this._super();

            window.addEventListener("message", function (event) {
                const message = event.data.message;
                switch (message) {
                    case 'notifyAuthVault':
                        let iframeContainer = document.getElementById(`${self.getId()}-tds-iframe-container`);
                        iframeContainer.classList.remove('active');
                        iframeContainer.innerHTML = '';

                        if(event.data.value) {
                            if(event.data.value.vaultId == self.getId()) {
                                if(event.data.value.auth) {
                                    self.setIsAuthorized(true);
                                    self.setAuthorized3DSdata(JSON.parse(event.data.value.data));
                                    self.placeOrder();
                                } else {
                                    self.setIsAuthorized(false);
                                    self.setAuthorized3DSdata(null);
                                    self._showErrors(jQuery.mage.__(event.data.value.msg));
                                }
                            }
                        } else {
                            self.setIsAuthorized(false);
                            self.setAuthorized3DSdata(null);
                            self._showErrors(jQuery.mage.__('Authentication failed.'));
                        }
                        break;
                }
            }, false);
        },

        /**
         * Set payment nonce
         * @param paymentMethodNonce
         */
        setPaymentMethodNonce: function (paymentMethodNonce) {
            this.paymentMethodNonce = paymentMethodNonce;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'is_authorized': this.is_authorized,
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };
        },

        /**
         * Set is_authorized
         * @param is_authorized
         */
        setIsAuthorized: function (is_authorized) {
            this.is_authorized = is_authorized;
        },

        /**
         *
         * @param data
         */
        setAuthorized3DSdata: function (data) {
            this.data_3ds = data;
        },

        /**
         * Is payment option active?
         * @returns {boolean}
         */
        isActive: function () {
            let active = this.getId() === this.isChecked();

            //this.active(active);

            return active;
        },

        /**
         * Return the payment method code.
         * @returns {string}
         */
        getCode: function () {
            return 'bacfac_vault';
        },

        /**
         * Return the payment method code.
         * @returns {string}
         */
        getBaseCode: function () {
            return 'bacfac';
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC.replaceAll('X','');
        },

        /**
         * Get first 6 digits of card
         * @returns {String}
         */
        getBIN: function () {
            return this.bin;
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getHash: function () {
            return this.publicHash;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * Place order
         */
        placeOrder: function (data, event) {
            /**
             * Validate cvv
             */
            if (!Number.isInteger(parseInt(this.creditCardVerificationNumber()))) {
                this._showErrors(jQuery.mage.__('Invalid verification number.'));
                return false;
            }

            let cardData = {
                "credit_card_number": this.getHash(),
                "credit_card_security_code_number": parseInt(this.creditCardVerificationNumber())
            };

            if(this.getCardType() === 'AE') {
                //AMEX
                cardData.vault = false;
                cardData.cc_type = 'AE';
                cardData.is_vault = true;

                cardData.is_authorized = false;
                bacFacComponent().placeOrder(cardData, event);
            } else {
                if(this.is_authorized) {
                    this.data_3ds.vault = false;
                    this.data_3ds.cc_type = this.getCardType();
                    this.data_3ds.is_vault = true;
                    this.data_3ds.is_authorized = true;
                    bacFacComponent().placeOrder(this.data_3ds, event);
                } else {
                    //Create iframe on the fly:
                    let iframeContainer = document.getElementById(`${this.getId()}-tds-iframe-container`),
                        iframe = document.createElement('iframe');

                    iframe.setAttribute('name', 'vault-tds-iframe')
                    iframe.setAttribute('id', 'vault-tds-iframe')
                    iframeContainer.appendChild(iframe);

                    cardData.is_vault = true;
                    cardData.vault_id = this.getId();

                    // 3DS Request
                    let api_url = window.checkoutConfig.payment[this.getBaseCode()]["api_url"],
                        bacfacUrl = window.checkoutConfig.payment[this.getBaseCode()]["bacfac_url"],
                        newForm = jQuery('<form>', {
                            'action': bacfacUrl,
                            'method': 'post',
                            'target': `vault-tds-iframe`
                        })
                            .append(jQuery('<input>', {
                                'name': 'api_url',
                                'value': api_url,
                                'type': 'hidden'
                            }))
                            .append(jQuery('<input>', {
                                'name': 'nonce',
                                'value': bacFacComponent().encrypt(JSON.stringify(cardData), bacFacComponent().getConfig()['ekey'], bacFacComponent()),
                                'type': 'hidden'
                            }))
                            .append(jQuery('', {
                                'value': 'send',
                                'type': 'submit'
                            }));

                    jQuery('body').append(newForm);

                    iframeContainer.classList.add('active');

                    jQuery(`#${this.getId()}-tds-iframe-container iframe`)
                        .contents()
                        .find('body')
                        .html(`<img src="/pub/media/wysiwyg/loader-1.gif" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%,-50%);width: 50px;">`);

                    setTimeout(function () {
                        newForm.submit().remove();
                    },300);
                }
            }
        },

        /**
         * Get image url for CVC
         * @returns {String}
         */
        getCvvImageUrl: function () {
            return window.checkoutConfig.payment.ccform.cvvImageUrl['bacfac'];
        },

        /**
         * Get image for CVC
         * @returns {String}
         */
        getCvvImageHtml: function () {
            return '<img src="' + this.getCvvImageUrl() +
                '" alt="' + $t('Card Verification Number Visual Reference') +
                '" title="' + $t('Card Verification Number Visual Reference') +
                '" />';
        },

        /**
         * Show error messages
         * @param msg
         * @private
         */
        _showErrors: function (msg) {
            jQuery(window).scrollTop(0);
            globalMessageList.addErrorMessage({
                message: msg
            });
        },
    });
});

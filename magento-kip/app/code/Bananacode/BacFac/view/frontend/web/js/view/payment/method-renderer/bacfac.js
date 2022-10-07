/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'crypto-js',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Payment/js/model/credit-card-validation/expiration-date-validator',
        'Magento_Vault/js/view/payment/vault-enabler',
        'mage/translate'
    ],
    function (jQuery,
              Component,
              CryptoJS,
              quote,
              globalMessageList,
              creditCardNumberValidator,
              expirationDateValidator,
              VaultEnabler) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Bananacode_BacFac/payment/form',
                is_authorized: false,
                bin: null,
                data_3ds: null,
                vault: true,
            },

            /**
             * Init component
             */
            initialize: function () {
                let self = this;

                this._super();

                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                this.vaultEnabler.isActivePaymentTokenEnabler.subscribe(function () {
                    self.onVaultPaymentTokenEnablerChange();
                });

                window.addEventListener("message", function (event) {
                    const message = event.data.message;
                    switch (message) {
                        case 'notifyAuth':
                            jQuery('#tds-iframe-container').removeClass('active');
                            if(event.data.value) {
                                if(event.data.value.auth) {
                                    self.setIsAuthorized(true);
                                    self.setAuthorized3DSdata(JSON.parse(event.data.value.data));
                                    //DATALAYER-CODITY
                                    window.dataLayer.push({'event': 'addpaymentinfo'});
                                    window.dataLayer.push({'event': 'purchase'});
                                    self.placeOrder();
                                } else {
                                    window.dataLayer.push({'event': 'error'});
                                    self.setIsAuthorized(false);
                                    self.setAuthorized3DSdata(null);
                                    self._showErrors(jQuery.mage.__(event.data.value.msg));
                                }
                            } else {
                                //DATALAYER-CODITY
                                window.dataLayer.push({'event': 'error'});
                                self.setIsAuthorized(false);
                                self.setAuthorized3DSdata(null);
                                self._showErrors(jQuery.mage.__('Authentication failed.'));
                            }
                            break;
                    }
                }, false);
            },

            /**
             * Re-init to use Vault
             */
            onVaultPaymentTokenEnablerChange: function () {
                this.vault = !this.vault;
                return this.vault;
            },

            /**
             * Set payment nonce
             * @param paymentMethodNonce
             */
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },

            /**
             * Set is_authorized
             * @param is_authorized
             */
            setIsAuthorized: function (is_authorized) {
                this.is_authorized = is_authorized;
            },

            /**
             * Set bin
             * @param bin
             */
            setBIN: function (bin) {
                this.bin = bin;
            },

            /**
             *
             * @param data
             */
            setAuthorized3DSdata: function (data) {
                this.data_3ds = data;
            },

            context: function () {
                return this;
            },

            getCode: function () {
                return 'bacfac';
            },

            isActive: function () {
                return this.getCode() === this.isChecked();
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'bin': this.bin,
                        'is_authorized': this.is_authorized,
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };
            },

            /**
             *
             * @param number
             * @returns {*}
             */
            isVisaMasterCard: function(number) {
                let visaMasterCard = false,
                    validTypes;

                validTypes = 0x0000;
                //mastercard
                validTypes |= 0x0001;
                //visa
                validTypes |= 0x0002;
                //amex
                validTypes |= 0x0004;

                if (validTypes & 0x0001 && /^(51|52|53|54|55)/.test(number)) { //mastercard
                    visaMasterCard = number.length === 16
                    if(visaMasterCard) {
                        return 'MC'
                    }
                }

                if (validTypes & 0x0002 && /^(4)/.test(number)) { //visa
                    visaMasterCard = number.length === 16;
                    if(visaMasterCard) {
                        return 'VI'
                    }
                }

                if (validTypes & 0x0004 && /^(34|37)/.test(number)) { //amex
                    if(!(number.length === 15)) {
                        return false;
                    }
                }

                return visaMasterCard
            },

            /**
             *
             */
            encryptMethod: function () {
                return 'AES-256-CBC';
            },

            /**
             *
             * @returns {number}
             */
            encryptMethodLength: function () {
                let encryptMethod = this.encryptMethod();
                let aesNumber = encryptMethod.match(/\d+/)[0];
                return parseInt(aesNumber);
            },

            /**
             *
             * @param string
             * @param key
             * @param self
             * @returns {*}
             */
            encrypt: (string, key, self) => {
                let iv = CryptoJS.lib.WordArray.random(16),
                    salt = CryptoJS.lib.WordArray.random(256),
                    iterations = 999,
                    encryptMethodLength = (self.encryptMethodLength() / 4),
                    hashKey = CryptoJS.PBKDF2(key, salt, {
                        'hasher': CryptoJS.algo.SHA512,
                        'keySize': (encryptMethodLength / 8),
                        'iterations': iterations
                    }),
                    encrypted = CryptoJS.AES.encrypt(string, hashKey, {'mode': CryptoJS.mode.CBC, 'iv': iv}),
                    encryptedString = CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
                    output = {
                        'ciphertext': encryptedString,
                        'iv': CryptoJS.enc.Hex.stringify(iv),
                        'salt': CryptoJS.enc.Hex.stringify(salt),
                        'iterations': iterations
                    };

                return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(output)));
            },

            /**
             * Place order, generate payment nonce before.
             * @param data
             * @param event
             */
            placeOrder: function (data, event) {
                let vault = false;
                if(data) {
                    if(data.is_vault === true) {
                        vault = true;
                    }
                }

                if(vault) {
                    this.setIsAuthorized(data.is_authorized);
                    this.setPaymentMethodNonce(this.encrypt(JSON.stringify(data), this.getConfig()['ekey'], this));
                    this._super(data, event);
                } else {
                    /**
                     * Validate cc number
                     */
                    if (!creditCardNumberValidator(this.creditCardNumber()).isValid) {
                        this._showErrors(jQuery.mage.__('Invalid credit card number.'));
                        return false;
                    } else {
                        const cardInfo = creditCardNumberValidator(this.creditCardNumber()).card;
                        const allowedTypes = Object.values(window.checkoutConfig.payment['ccform']['availableTypes']['bacfac']);
                        let allow = false;

                        for (let i = 0, l = allowedTypes.length; i < l; i++) {
                            if (cardInfo.title === allowedTypes[i]) {
                                allow = true
                            }
                        }

                        if (!allow) {
                            this._showErrors(jQuery.mage.__('Invalid credit card type.'));
                            return false;
                        }
                    }

                    /**
                     * Validate expiration date
                     */
                    if (!expirationDateValidator(this.creditCardExpMonth() + '/' + this.creditCardExpYear()).isValid) {
                        this._showErrors(jQuery.mage.__('Invalid expiration date.'));
                        return false;
                    }

                    /**
                     * Validate cvv
                     */
                    if (!Number.isInteger(parseInt(this.creditCardVerificationNumber()))) {
                        this._showErrors(jQuery.mage.__('Invalid verification number.'));
                        return false;
                    }

                    let cardData = {
                        "exp_month": parseInt(this.creditCardExpMonth()),
                        "exp_year": parseInt(this.creditCardExpYear()),
                        "credit_card_number": parseInt(this.creditCardNumber()),
                        "credit_card_security_code_number": parseInt(this.creditCardVerificationNumber())
                    };

                    /**
                     * Validate cc type
                     */
                    //Visa Mastercard
                    let isVisaMaster = this.isVisaMasterCard(this.creditCardNumber());
                    if(isVisaMaster) {
                        if(this.is_authorized) {
                            this.data_3ds.vault = this.vault;
                            this.data_3ds.cc_expirationDate = `${parseInt(this.creditCardExpMonth())}/${parseInt(this.creditCardExpYear())}`;
                            this.data_3ds.cc_type = isVisaMaster;
                            this.setBIN(this.creditCardNumber().toString().slice(0,6));
                            this.setPaymentMethodNonce(this.encrypt(JSON.stringify(this.data_3ds), this.getConfig()['ekey'], this));
                            this._super(data, event);
                        } else {
                            // 3DS Request
                            let api_url = this.getConfig()["api_url"],
                                bacfacUrl = this.getConfig()["bacfac_url"],
                                //todo: encrypt data?
                                newForm = jQuery('<form>', {
                                    'action': bacfacUrl,
                                    'method': 'post',
                                    'target': 'tds-iframe'
                                })
                                    .append(jQuery('<input>', {
                                        'name': 'api_url',
                                        'value': api_url,
                                        'type': 'hidden'
                                    }))
                                    .append(jQuery('<input>', {
                                        'name': 'nonce',
                                        'value': this.encrypt(JSON.stringify(cardData), this.getConfig()['ekey'], this),
                                        'type': 'hidden'
                                    }))
                                    .append(jQuery('', {
                                        'value': 'send',
                                        'type': 'submit'
                                    }));

                            jQuery('body').append(newForm);

                            jQuery('#tds-iframe-container').addClass('active');

                            jQuery(`#tds-iframe-container iframe`)
                                .contents()
                                .find('body')
                                .html(`<img src="/pub/media/wysiwyg/loader-1.gif" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%,-50%);width: 50px;">`);

                            setTimeout(function () {
                                newForm.submit().remove();
                            },300);
                        }
                    } else {
                        //AMEX
                        cardData.vault = this.vault;
                        cardData.cc_expirationDate = `${parseInt(this.creditCardExpMonth())}/${parseInt(this.creditCardExpYear())}`;
                        cardData.cc_type = 'AE';
                        this.setBIN(this.creditCardNumber().toString().slice(0,6));
                        this.setPaymentMethodNonce(this.encrypt(JSON.stringify(cardData), this.getConfig()['ekey'], this));
                        this._super(data, event);
                    }
                }
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

            /** Vault Methods **/

            /**
             * @returns {Bool}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            /**
             * @returns {String}
             */
            getVaultCode: function () {
                return this.getConfig()['ccVaultCode'];
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            }
        });
    }
);

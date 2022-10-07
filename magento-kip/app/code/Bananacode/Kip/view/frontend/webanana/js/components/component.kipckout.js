/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
 import {
    selectArr,
    event,
    selectDoc, ajax, toggleClass,
    addClass, removeClass, hasClass
} from "../utils/wonder";

import {
    formatDollar,
    nextStepShipping,
    orderSummaryHtml, referralsInputHTML,
    shippingSteps,
    taxDocuments
} from "../helpers/service.markup";

import {inputEmpty} from "../helpers/service.validator";
import {displayModalError, showLoader} from "../helpers/service.dialog";
import {TaxDocument} from "../pages";
import {controlAtc, kipCartSession, renderCart} from "../helpers/service.cart";

/**
 *
 */
const updateKipOrderSummary = () => {
    let summary = selectDoc('.opc-block-summary');
    if (summary) {
        console.log('Updating order summary totals...');
        ajax('GET',
            '/rest/V1/kip/order/summary'
        ).then(function (response) {
            showLoader(true);
            let data = (JSON.parse(response));
            if (data.status === 200) {
                let content = selectDoc('#kip-order-summary', summary),
                    totals = JSON.parse(data.response),
                    newContent = orderSummaryHtml(totals);
                if (content && newContent) {
                    content.innerHTML = newContent ?? content.innerHTML;
                } else {
                    content = selectDoc('#kip-order-summary', summary) ?? document.createElement('div');
                    content.id = 'kip-order-summary';
                    content.innerHTML = orderSummaryHtml(totals) ?? content.innerHTML;
                    summary.appendChild(content);
                }

                let kipckout = window.checkoutConfig.kipckout,
                    kipPlaceOder = selectDoc('button#kip-place-order');
                if (kipPlaceOder) {
                    if (kipckout.currentStepId.includes("payment.checkout-payment-method")) {
                        kipPlaceOder.disabled = false;
                        removeClass(kipPlaceOder, 'disabled');

                        if (!hasClass(kipPlaceOder, 'evented')) {
                            addClass(kipPlaceOder, 'evented')
                            event(kipPlaceOder, 'click', function (e) {
                                e.preventDefault();

                                //Validate data
                                if (!window.checkoutConfig.ctdkip || inputEmpty(window.checkoutConfig.ctdkip)) {
                                    displayModalError('Favor seleccionar un tipo de documento fiscal.');
                                } else {
                                    let activeMethod = selectDoc('.payment-method._active .action.primary.checkout')
                                    if (activeMethod) {
                                        activeMethod.click();
                                    } else {
                                        displayModalError('Favor seleccionar un método de pago.');
                                    }
                                }
                            });

                            let triggersPayment = selectArr('.payment-method .action.primary.checkout');
                            triggersPayment.map(triggerPayment => {
                                let disclaimer = selectDoc('.disclaimer', triggerPayment.parentNode)
                                if(!disclaimer) {
                                    disclaimer = document.createElement('p');
                                    disclaimer.className = 'disclaimer';
                                    disclaimer.innerHTML = 'Únicamente reservamos fondos en tu tarjeta. Al facturar tu pedido, se realiza el cobro con el monto exacto de lo que recibirás.';
                                    triggerPayment.parentNode.appendChild(disclaimer)
                                }
                            });

                            let styledCc = selectDoc('#cc_number_styled:not(.evented)');
                            if(styledCc) {
                                const ccFormat = (value) => {
                                    let v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, ''),
                                        matches = v.match(/\d{4,16}/g),
                                        match = matches && matches[0] || '',
                                        parts = [],
                                        len = match.length;
                                    for (let i = 0; i < len; i += 4) {
                                        parts.push(match.substring(i, i + 4))
                                    }
                                    if (parts.length) {
                                        return parts.join(' ')
                                    } else {
                                        return value
                                    }
                                }

                                addClass(styledCc, 'evented')
                                event(styledCc, 'input', function () {
                                    styledCc.value = ccFormat(this.value);
                                    selectDoc('#bacfac_cc_number').value = styledCc.value.replaceAll(' ', '');
                                    selectDoc('#bacfac_cc_number').dispatchEvent(new Event('change', {'bubbles': true}));
                                    selectDoc('#bacfac_cc_number').dispatchEvent(new Event('input', {'bubbles': true}));
                                });
                            }
                        }
                    } else {
                        kipPlaceOder.disabled = true;
                        addClass(kipPlaceOder, 'disabled')
                    }
                }
            }
        })
    }
}

/**
 * @param property
 * @param value
 */
const updateKippingSession = (property, value) => {
    let session = JSON.parse(localStorage.getItem('kipping'));
    if (!session) {
        session = {};
    }
    session[property] = value;
    localStorage.setItem('kipping', JSON.stringify(session));

    ajax('POST',
        '/rest/V1/kipping/session',
        JSON.stringify({
            data: JSON.stringify(session)
        }),
        'application/json'
    ).then(function (response) {
    });
}

const verifyBIN = (binValue) => {
    let bins = window.checkoutConfig.payment['bacfac'].bins,
        applies = false,
        binResult = '';
    if (bins && binValue.length >= 6) {
        console.log("Calling verify",binValue);
        console.log(bins);
        bins.split(',').map(bin => {
            if (bin == binValue.slice(0, 6)) {
                applies = true;
                binResult = bin;
            }
        });
    } else {
        applies = false;
    }

    if(applies !== window.checkoutConfig.payment['bacfac'].binsdiscuntapplied) {
        showLoader();
        updateKippingSession('bin', binResult);
        window.checkoutConfig.payment['bacfac'].binsdiscuntapplied = applies;
        renderCart(null, null, true, null, {
            bin: binResult
        });
    }
}

export const initVaultSelector = () => {
    let tries = 0;
    let vaultMethodsLoaded = window.setInterval(function () {
        if (window.checkoutConfig.payment.vault && !selectDoc('#vault-custom-selector')) {
            let vaultMethods = selectArr('#payment-method-bacfac-vault'),
                configuredVaultSize = Object.keys(window.checkoutConfig.payment.vault);
            if (configuredVaultSize.length > 0) {
                if (vaultMethods.length !== configuredVaultSize.length) {
                    return;
                } else {
                    clearInterval(vaultMethodsLoaded);
                }

                let firstVault = vaultMethods[0],
                    vaultCustomSelector = document.createElement('div'),
                    dummyDiv = document.createElement('div');

                vaultCustomSelector.id = 'vault-custom-selector';
                dummyDiv.id = 'vault-custom-dummy';

                vaultCustomSelector.innerHTML = `<div>Tarjetas Guardadas</div>`;
                vaultMethods.map(vault => {
                    let optionContent = selectDoc('label.label', vault),
                        id = selectDoc('input.radio', vault),
                        option = document.createElement('div')

                    option.innerHTML = optionContent.innerHTML;
                    option.setAttribute('data-value', id.value)
                    vaultCustomSelector.appendChild(option);
                    event(option, 'click', function () {
                        let current = selectDoc(`input.radio[value="${option.getAttribute('data-value')}"]`);
                        current.click();

                        //BINS
                        let vaultBIN = selectDoc(`#bin-number-${option.getAttribute('data-value')}`);
                        if(vaultBIN) {
                            verifyBIN(vaultBIN.innerText);
                        }
                    })
                });

                event(vaultCustomSelector, 'click', function () {
                    toggleClass(vaultCustomSelector, 'open')
                });

                firstVault.parentNode.insertBefore(vaultCustomSelector, firstVault);
                firstVault.parentNode.insertBefore(dummyDiv, firstVault);
                tries = 1501;
            }
        } else {
            clearInterval(vaultMethodsLoaded);
        }
        tries++;
        if (tries > 1500) {
            clearInterval(vaultMethodsLoaded);
        }
    }, 1);
}

//Second step next flow observer
export const observeShippingInformation = (request) => {
    //Verify response and render final step
    if (request.responseURL.includes('/shipping-information') && request.responseURL.includes('/rest')) {
        const initBins = () => {
            let inputCc = selectDoc('input#bacfac_cc_number:not(.evented)');
            if (inputCc) {
                updateKippingSession('bin', '');
                addClass(inputCc, 'evented');
                window.checkoutConfig.payment['bacfac'].binsdiscuntapplied = false;
                event(inputCc, 'input', function () {
                    verifyBIN(inputCc.value);
                });
            }
        }

        const mapHour = (hour) => {
            hour = hour.toString();

            switch (hour) {
                case "13":
                    return "1";
                case "14":
                    return "2";
                case "15":
                    return "3";
                case "16":
                    return "4";
                case "17":
                    return "5";
                case "18":
                    return "6";
                case "19":
                    return "7";
                case "20":
                    return "8";
                case "21":
                    return "9";
                case "22":
                    return "10";
                case "23":
                    return "11";
                case "24":
                    return "12";
                default:
                    return hour;
            }
        }

        const updateInvoice = (value, isIndex = true, hide = true) => {
            let typesTD = selectDoc('#tax-documents select#documents-types'),
                docsTDContainer = selectDoc('#tax-documents #stored-tax-docs'),
                docsTD = selectDoc('#tax-documents select#customer-documents');

            if (hide) {
                removeClass(docsTDContainer, 'active')
            }

            if (isIndex) {
                docsTD.selectedIndex = value;
                if (value === 0) {
                    value = null;
                }
            } else {
                docsTD.value = value;
            }

            window.checkoutConfig.ctdkip = value;
            updateKippingSession('invoice_document', value);

            //Check if exempt and update totals
            //exemptProductId
            let exempt = false;
            if (typesTD.value === 'EIVA') {
                if (!inputEmpty(value)) {
                    exempt = true;
                }
            }
            if (window.checkoutConfig.kipexento !== exempt) {
                showLoader();
                window.checkoutConfig.kipexento = exempt;
                if(window.checkoutConfig.kipexento) {
                    controlAtc(null, {
                        value: window.checkoutConfig.payment['bacfac'].exemptProductId
                    }, {
                        "q": 1,
                        "p": '',
                        "i": '',
                        "n": '',
                        "m": 1,
                        "d": '',
                        "c": ''
                    });
                } else {
                    kipCartSession(window.checkoutConfig.payment['bacfac'].exemptProductId, 0, true);
                    renderCart(window.checkoutConfig.payment['bacfac'].exemptProductId, 'delete');
                }
            }
        }

        const initTaxDocumentSelector = (paymentMethodsContainer) => {
            if (!selectDoc('#tax-documents')) {
                let taxDocumentsSelector = document.createElement('div');
                taxDocumentsSelector.id = 'tax-documents';
                ajax('GET',
                    `/rest/V1/bananacodeTaxDocument/getApprovedDocumentsByCustomer`
                ).then(function (response) {
                    let data = (JSON.parse(response));
                    if (data.status === 200) {
                        window.checkoutConfig.kipexento = false;

                        let documents = JSON.parse(data.response);
                        if (documents.output.totalRecords > 0) {
                            taxDocumentsSelector.innerHTML = taxDocuments(documents.output.items);
                        } else {
                            taxDocumentsSelector.innerHTML = taxDocuments([]);
                        }
                        paymentMethodsContainer.insertBefore(taxDocumentsSelector, paymentMethodsContainer.firstChild);

                        try {
                            new TaxDocument();
                        } catch (e) {
                        }

                        let typesTD = selectDoc('#tax-documents select#documents-types'),
                            docsTDContainer = selectDoc('#tax-documents #stored-tax-docs'),
                            docsTD = selectDoc('#tax-documents select#customer-documents');

                        event(typesTD, 'change', function (e) {
                            e.preventDefault();

                            selectArr('#tax-documents select#customer-documents option:not(.first)').map(o => {
                                removeClass(o, 'active')
                                o.disabled = true;
                            });

                            if (inputEmpty(e.target.value)) {
                                updateInvoice(0)
                            } else {
                                //Document type selected
                                if (e.target.value !== 'FCF') { //Stored document
                                    addClass(docsTDContainer, 'active')

                                    let currentOptions = selectArr(`#tax-documents select#customer-documents option.document-${e.target.value}`);
                                    currentOptions.map(co => {
                                        addClass(co, 'active');
                                        co.disabled = false;
                                    });

                                    if (currentOptions.length > 0) {
                                        updateInvoice(currentOptions[0].value, false, false)
                                    } else {
                                        updateInvoice(0)
                                    }
                                } else { //Simple invoice
                                    updateInvoice('FCF', false, true)
                                }
                            }
                        });

                        window.checkoutConfig.ctdkip = null;
                        updateKippingSession('invoice_document', null);
                        event(docsTD, 'change', function (e) {
                            e.preventDefault();
                            updateInvoice(e.target.value, false, false)
                        })
                    }
                })
            }
        };

        const initReferrals = (paymentMethodsContainer) => {
            if (!selectDoc('#referrals-input')) {
                ajax('GET',
                    `/rest/V1/referral/customer`
                ).then(function (response) {
                    let data = JSON.parse(response);
                    if (data.status === 200) {
                        let referral = JSON.parse(data.response);
                        if(referral.output) {

                            let referralsInput = document.createElement('div');
                            referralsInput.id = 'referrals-input';
                            referralsInput.innerHTML = referralsInputHTML();
                            paymentMethodsContainer.insertBefore(referralsInput, selectDoc('.payment-option.discount-code'));

                            let toggle = selectDoc('button.toggle', referralsInput),
                                apply = selectDoc('button.apply', referralsInput),
                                input = selectDoc('form input', referralsInput),
                                available = selectDoc('span.available', referralsInput);

                            event(input, 'keyup', function () {
                                let total = selectDoc('#kip-order-summary .grand-total');
                                total = parseFloat(total.getAttribute('data-total'));

                                this.value = Math.abs(parseFloat(this.value).toFixed(2));
                                if(this.value > total) {
                                    this.value = Math.abs(parseFloat(total).toFixed(2));
                                }
                            });

                            referral = referral.output;
                            available.innerHTML = formatDollar(referral.total_left);
                            if(referral.session !== 0) {
                                input.value = Math.abs(referral.session);
                                input.disabled = true;
                                apply.innerHTML = 'Cancelar';
                                toggleClass(input, 'applied');
                            }

                            event(toggle, 'click', function (e) {
                                e.preventDefault();
                                toggleClass(toggle, 'active')
                            });

                            event(apply, 'click', function (e) {
                                e.preventDefault();

                                if(input.value > Math.abs(referral.total_left)) {
                                    displayModalError('Ingresar monto menor o igual al disponible.');
                                    return;
                                }

                                toggleClass(input, 'applied')
                                if(hasClass(input, 'applied')) {
                                    renderCart(null, null, true, null, {
                                        referral: input.value
                                    });
                                    input.disabled = true;
                                    apply.innerHTML = 'Cancelar';
                                } else {
                                    renderCart(null, null, true, null, {
                                        referral: 0
                                    });
                                    input.disabled = false;
                                    input.value = '';
                                    apply.innerHTML = 'Aplicar'
                                }
                                showLoader();
                            });
                        }
                    }
                });
            }
        };

        let kipckout = window.checkoutConfig.kipckout,
            shippingMethodStep = selectDoc("ol#checkoutSteps > li#opc-shipping_method"),
            paymentMethodStep = selectDoc("ol#checkoutSteps > li#payment");

        //Validate kipping data
        let selectedData = selectArr('#kip-shipping-custom-options .checked'),
            selectedSchedules = selectArr('#kip-shipping-custom-options #scheduled select'),
            selectedSchedulesFest = selectArr('#kip-shipping-custom-options #scheduled_festivity select');

        //Validate method & package
        let validKipping = true;
        if (selectedData.length !== 2) {
            displayModalError('Favor seleccionar todos los datos de entrega.');
            validKipping = false;
        } else {
            selectedData.map(data => {
                //Validate schedules
                if (data.id === 'scheduled_kipping' || data.id === 'scheduled_today_kipping') {
                    selectedSchedules.map(schedule => {
                        if (inputEmpty(schedule.value)) {
                            displayModalError('Favor seleccionar la fecha y hora de entrega.');
                            validKipping = false;
                        }
                    })
                }
                if (data.id === 'scheduled_festivity_kipping') {
                    selectedSchedulesFest.map(scheduleFest => {
                        if (inputEmpty(scheduleFest.value)) {
                            displayModalError('Favor seleccionar la fecha y hora de entrega.');
                            validKipping = false;
                        }
                    })
                }
            })
        }

        if (kipckout.validForm &&
            shippingMethodStep &&
            paymentMethodStep &&
            request.status === 200 &&
            validKipping
        ) {
            //-------------------------
            //Display final step
            //-------------------------
            shippingMethodStep.classList.remove('active')
            paymentMethodStep.classList.add('active')
            kipckout.step++;
            kipckout.lastStepId = '#opc-shipping_method.checkout-shipping-method';
            kipckout.currentStepId = '#payment.checkout-payment-method';
            window.checkoutConfig.kipckout = kipckout;

            //-------------------------
            //Update step 2 summary
            //-------------------------
            let summary = selectDoc('#opc-shipping_method .step-summary'),
                opcTitle = selectDoc('#opc-shipping_method .step-title'),
                session = JSON.parse(localStorage.getItem('kipping'));

            if (!summary) {
                summary = document.createElement('div');
                summary.className = 'step-summary';
                opcTitle.parentNode.insertBefore(summary, opcTitle.nextSibling);
                opcTitle.parentNode.parentNode.classList.add('summary-opc');
                event(summary, 'click', function () {
                    opcTitle.click();
                })
            }
            summary.innerHTML = `<p>Envío ${session.method.includes('scheduled') ? 'programado' : 'express'}</p>`;
            if (session.scheduled_day) {
                let date = session.scheduled_day,
                    dates = date.match(/.{1,2}/g),
                    day = dates[0],
                    month = dates[1],
                    hour = parseInt(session.scheduled_hour),
                    hourInterval = parseInt(session.scheduled_hour) + 2,
                    months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                summary.innerHTML += `<p>${day} de ${months[parseInt(month) - 1]} de ${(mapHour(hour) + (hour > 11 ? ':00 pm' : ':00 am'))} a ${(mapHour(hourInterval) + (hourInterval > 11 ? ':00 pm' : ':00 am'))}</p>`;
            }
            summary.innerHTML += `<p>${session.package_method === 'package' ? 'Bolsa plástica' : 'Sin bolsas plásticas'}</p>`;
            summary.innerHTML += session.order_note ? `<p>${session.order_note}</p>` : '';

            updateKipOrderSummary();
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });

            //-------------------------
            //Display Tax Documents & Vault Payments
            //-------------------------
            let taxDocumentsSelector = selectDoc('#checkout-payment-method-load .payment-methods .payment-group #tax-documents'),
                vaultSelector = selectDoc('#checkout-payment-method-load .payment-methods .payment-group #vault-selector'),
                referralsInput = selectDoc('#checkout-payment-method-load .payment-methods .payment-group #referrals-input'),
                paymentMethodsContainer = selectDoc('#checkout-payment-method-load .payment-methods');
            if (!paymentMethodsContainer) {
                let observePaymentForm = window.setInterval(function () {
                    paymentMethodsContainer = selectDoc('#checkout-payment-method-load .payment-methods');
                    if (!paymentMethodsContainer) {
                        return;
                    }

                    initTaxDocumentSelector(paymentMethodsContainer);
                    initVaultSelector();
                    initReferrals(selectDoc('#co-payment-form > fieldset'));

                    if(!selectDoc('input#bacfac_cc_number')) {
                        let binsInt = setInterval(function () {
                            if(!selectDoc('input#bacfac_cc_number')) {
                                return;
                            }
                            initBins();
                            clearInterval(binsInt);
                        }, 200);
                    } else {
                        initBins();
                    }

                    clearInterval(observePaymentForm);
                }, 1);
            } else {
                if (!taxDocumentsSelector) {
                    initTaxDocumentSelector(paymentMethodsContainer);
                }
                if (!vaultSelector) {
                    initVaultSelector();
                }
                if (!referralsInput) {
                    initReferrals(selectDoc('#co-payment-form > fieldset'));
                }
                if(!selectDoc('input#bacfac_cc_number')) {
                    let binsInt = setInterval(function () {
                        if(!selectDoc('input#bacfac_cc_number')) {
                            return;
                        }
                        initBins();
                        clearInterval(binsInt)
                    }, 200);
                } else {
                    initBins();
                }
            }
        }
    }

    //Keep custom summaries up to date
    if (request.responseURL.includes('customer/section/load') || request.responseURL.includes('payment-information')) {
        updateKipOrderSummary();
    }
};

export class Kipckout {
    /**
     * Header constructor
     */
    constructor(container) {
        if (container) {
            this.init();
        }
    }

    /**
     * Initialize class
     */
    init() {
        this.props();

        if (selectDoc('.checkout-index-index')) {
            this.initStepsFlow();
        } else {
            this.clearKippingSession();
        }
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.pageTitle = selectDoc("div.page-title-wrapper")

        this.steps = selectArr("ol#checkoutSteps > li");
        this.stepsTitles = selectArr("ol#checkoutSteps > li .step-title");
        this.shippingMethodNext = selectDoc("#shipping-method-buttons-container button");

        this.shippingStep = selectDoc("ol#checkoutSteps > li#shipping");
        this.shippingMethodStep = selectDoc("ol#checkoutSteps > li#opc-shipping_method");
        this.paymentMethodStep = selectDoc("ol#checkoutSteps > li#payment");
    }

    /**
     *
     * @returns {(boolean|*[])[]}
     */
    validateShippingData() {
        let requiredInputs = selectArr('#co-shipping-form ._required'),
            success = true,
            invalidInputs = [];
        requiredInputs.map(requiredInput => {
            let input = selectDoc('input', requiredInput);
            if (input) {
                if (inputEmpty(input.value)) {
                    success = false;
                    invalidInputs.push(input.getAttribute('placeholder'))
                }
            }

            let select = selectDoc('select', requiredInput);
            if (select) {
                if (inputEmpty(select.value)) {
                    success = false;
                    invalidInputs.push(select.options[0].innerText)
                }
            }
        });
        return [success, invalidInputs]
    }

    /**
     *
     */
    initStepsFlow() {
        if (window.checkoutConfig) {
            let self = this;

            window.checkoutConfig.kipckout = {
                validForm: true,
                step: 0,
                lastStepId: '',
                currentStepId: '#shipping.checkout-shipping-address',
            };

            let kipckout = window.checkoutConfig.kipckout;

            //Add step number
            this.stepsTitles.map((title, i) => {
                title.setAttribute('data-step', i)
            })

            //Init: first step next flow
            if (this.shippingStep) {
                let nextContainer = document.createElement('div'),
                    existingAddresses = selectArr('.shipping-address-items .shipping-address-item'),
                    shippingStepContent = selectDoc('.step-content', this.shippingStep);
                nextContainer.className = 'actions-toolbar';
                nextContainer.id = 'shipping-address-buttons-container';
                nextContainer.innerHTML = nextStepShipping();
                shippingStepContent.appendChild(nextContainer);
                event(selectDoc('button.action.primary', nextContainer), 'click', function () {
                    kipckout.validForm = true
                    let selectedAddress = selectDoc('.shipping-address-items .shipping-address-item.selected-item'),
                        invalidInputs = [],
                        validate = self.validateShippingData();

                    existingAddresses = selectArr('.shipping-address-items .shipping-address-item');
                    if (existingAddresses.length < 1) {
                        kipckout.validForm = validate[0];
                        invalidInputs = validate[1];
                    } else {
                        if (!selectedAddress) {
                            displayModalError('Selecciona una dirección de envíoss.');
                            return;
                        }
                    }

                    if (!kipckout.validForm) {
                        self.shippingMethodNext.click();
                        displayModalError('Campos requeridos: ' + invalidInputs.join(','))
                    } else {
                        self.shippingStep.classList.remove('active');
                        self.shippingMethodStep.classList.add('active');
                        kipckout.step++;
                        kipckout.lastStepId = '#shipping.checkout-shipping-address';
                        kipckout.currentStepId = '#opc-shipping_method.checkout-shipping-method';

                        let summary = selectDoc('#shipping .step-summary'),
                            shippingTitle = selectDoc('#shipping .step-title');

                        if (!summary) {
                            summary = document.createElement('div');
                            summary.className = 'step-summary';
                            shippingTitle.parentNode.insertBefore(summary, shippingTitle.nextSibling);
                            shippingTitle.parentNode.classList.add('summary-shipping');
                            event(summary, 'click', function () {
                                shippingTitle.click();
                            })
                        }

                        if (existingAddresses.length <= 0) {
                            let shippingForm = selectDoc('#co-shipping-form');
                            if (shippingForm) {
                                let summaryHTML = '';

                                selectArr('input', shippingForm).map(addressField => {
                                    if (
                                        addressField.value
                                        && !addressField.className.includes('checkbox')
                                        && !addressField.name.includes('name')
                                    ) {
                                        summaryHTML += `${addressField.value} `;
                                    }
                                });

                                summary.innerHTML = `<p>${summaryHTML}</p>`;
                            }
                        } else {
                            summary.innerHTML = `<p>${selectedAddress.innerHTML}</p>`;
                        }

                        updateKipOrderSummary();

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth',
                        })
                    }

                    window.checkoutConfig.kipckout = kipckout;
                });

                let steps = document.createElement('div'),
                    addressStepsReady = false;

                steps.className = "address-steps";
                steps.innerHTML = shippingSteps();

                const renderAddressSteps = (mainContainer, saveSelector, addCancel) => {
                    let tries = 0,
                        intv = setInterval(function () {
                            let save = selectDoc(saveSelector),
                                addressFormContainer = selectDoc('form#co-shipping-form');

                            if (save && addressFormContainer) {
                                clearInterval(intv);
                                addressStepsReady = true;

                                if (addCancel) {
                                    mainContainer.appendChild(steps);
                                } else {
                                    mainContainer.prepend(steps);
                                }

                                let nextButton = document.createElement('button'),
                                    backButton = document.createElement('button'),
                                    button1 = selectDoc('.address-steps #button-step1'),
                                    button2 = selectDoc('.address-steps #button-step2'),
                                    label = selectDoc('#current-address-step-label'),
                                    cancel = addCancel ? selectDoc('button.action-hide-popup', save.parentNode) : null;

                                nextButton.className = 'next-address-step active';
                                nextButton.innerHTML = '<span>Continuar</span>';

                                backButton.className = 'go-back-address';
                                backButton.innerHTML = '<span>Regresar</span>';

                                save.parentNode.appendChild(nextButton);
                                save.parentNode.appendChild(backButton);
                                cancel ? cancel.classList.add('active') : '';

                                event(nextButton, 'click', function () {
                                    addressFormContainer.classList.add('step2');
                                    button1.classList.remove('active');
                                    button2.classList.add('active');
                                    label.innerText = 'Llena los detalles de tu dirección de entrega.'

                                    cancel ? cancel.classList.remove('active') : '';
                                    nextButton.classList.remove('active');
                                    backButton.classList.add('active');
                                    save.classList.add('active');
                                })

                                event(backButton, 'click', function () {
                                    addressFormContainer.classList.remove('step2');
                                    button1.classList.add('active');
                                    button2.classList.remove('active');
                                    label.innerText = 'Busca y señala tu ubicación en el mapa. Puedes mover el PIN o dar clic en el lugar.'

                                    cancel ? cancel.classList.add('active') : '';
                                    nextButton.classList.add('active');
                                    backButton.classList.remove('active');
                                    save.classList.remove('active');
                                });
                            }
                            tries++;
                            if (tries > 5000) {
                                clearInterval(intv);
                            }
                        }, 1);
                }

                if (existingAddresses.length > 0) {
                    let formPopUp = selectDoc('#opc-new-shipping-address'),
                        openPopup = selectDoc('.new-address-popup button'),
                        editAddresses = selectArr('.shipping-address-item .action.edit-address-link'),
                        save = 'button.action-save-address';

                    if (formPopUp && openPopup) {
                        event(openPopup, 'click', function () {
                            if (!addressStepsReady) {
                                renderAddressSteps(formPopUp, save, true);
                            }
                        })

                        editAddresses.map(editAddress => {
                            event(editAddress, 'click', function () {
                                if (!addressStepsReady) {
                                    renderAddressSteps(formPopUp, save, true);
                                }
                            })
                        })
                    }
                } else {
                    if (!addressStepsReady) {
                        let formContainer = selectDoc('#checkout-step-shipping'),
                            save = '#shipping-address-buttons-container .primary';

                        renderAddressSteps(formContainer, save, false);
                        addClass(formContainer, 'empty-address');
                    }
                }

                this.shippingStep.classList.add('active');
            }

            //Init: back step flow
            this.steps.map(step => {
                event(step, 'click', function (e) {
                    let target = e.target;
                    if (target.className) {
                        if (target.className.includes('step-title')) {
                            let clickedStep = parseInt(target.getAttribute('data-step'));
                            if ((clickedStep + 1) === kipckout.step) {
                                let kipPlaceOder = selectDoc('button#kip-place-order');
                                if (kipPlaceOder) {
                                    kipPlaceOder.disabled = true;
                                    addClass(kipPlaceOder, 'disabled');
                                }

                                kipckout.step--;
                                kipckout.lastStepId = kipckout.currentStepId;
                                kipckout.currentStepId = `#${target.parentNode.id}`;
                                window.checkoutConfig.kipckout = kipckout;

                                if (inputEmpty(target.parentNode.id)) {
                                    kipckout.currentStepId = `#${target.parentNode.parentNode.id}`;
                                }

                                selectDoc(kipckout.currentStepId).classList.add('active');
                                selectDoc(kipckout.lastStepId).classList.remove('active');

                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth',
                                })
                            }
                        }
                    }
                })
            })

            //Init: custom design details
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });

            if(this.pageTitle) {
                let goBack = document.createElement('a');
                goBack.id = 'go-back';
                goBack.innerHTML = 'Atrás';
                goBack.href = '/checkout/cart'
                this.pageTitle.appendChild(goBack);
                event(goBack, 'click', function () {
                    window.location.href = '/checkout/cart';
                });

                let span = selectDoc('span', this.pageTitle);
                span.innerHTML = 'Confirmar pedido';

                addClass(this.pageTitle, 'show')
            }

        }
    }

    /**
     *
     */
    clearKippingSession() {
        let session = {};
        localStorage.setItem('kipping', JSON.stringify(session));
        ajax('POST',
            '/rest/V1/kipping/session',
            JSON.stringify({
                data: JSON.stringify(session)
            }),
            'application/json'
        );
    }
}

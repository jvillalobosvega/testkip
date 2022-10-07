/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    addClass,
    removeClassAll,
    ajax
} from "../utils/wonder";

import {showLoader} from '../helpers/service.dialog'

import {Modals} from "../components";

export class Account {
    /**
     * Header constructor
     */
    constructor(container) {
        if (selectDoc(container)) {
            this.init();
        }
    }

    /**
     * Initialize class
     */
    init() {
        this.props();
        this.initTabs();
        this.checkUrl();

        this.initAvatarUpload();
        this.initModals();
        this.initAddress();
        this.initSales();
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.tabs = selectArr(".account-tabs button");
        this.containers = selectArr(".account-container > div");
        this.modals = new Modals(false);

        this.signUpButton = selectDoc('#signup-form-validate button.primary')
        this.loginButton = selectDoc('#login-form button.primary')
        this.forgotButton = selectDoc('.form.password.forget button.primary')

        this.avatarContainer = selectDoc("#logged-header .avatar");
        this.avatarInput = selectDoc("#logged-header .avatar input[name='avatar']");
        this.avatarToken = selectDoc("#logged-header .avatar input[name='token']");
        this.avatarId = selectDoc("#logged-header .avatar input[name='id']");
        this.avatarImg = selectDoc("#logged-header .avatar img");
        this.avatarSpan = selectDoc("#logged-header .avatar span.no-avatar");

        this.experienceData = selectDoc('#logged-experience #experience-data');
        this.experienceForm = selectDoc('#logged-experience form');
        this.experienceModal = selectDoc('#logged-experience');
        this.experienceLater = selectDoc('#logged-experience #later-experience');
        this.experienceAccept = selectDoc('#logged-experience #accept-experience');

        this.askProductForm = selectDoc('#new-product form');
        this.askProductModal = selectDoc('#new-product');
        this.askProductAsk = selectDoc('#new-product #ask-product');
        this.askProductContent = selectDoc('#new-product .content:not(.content-success)');
        this.askProductSuccess = selectDoc('#new-product .content-success');

        this.reportErrorForm = selectDoc('#report-error form');
        this.reportErrorModal = selectDoc('#report-error');
        this.reportErrorReport = selectDoc('#report-error #report');
        this.reportErrorContent = selectDoc('#report-error .content:not(.content-success)');
        this.reportErrorSuccess = selectDoc('#report-error .content-success');

        this.formEdit = selectDoc('#form-validate.form.form-edit-account');

        this.addressForm = selectDoc('.form-address-edit');
        this.addressStep = 1;
        this.addressSteps = selectArr(".address-steps button");
        this.addressStepsContainers = selectArr(".form-address-edit .steps .step");
        this.addressStepLabel = selectDoc("#current-step-label");
        this.addressBack = selectDoc(".form-address-edit button#go-back");
        this.addressNext = selectDoc(".form-address-edit button#next-step");
        this.addressSubmit = selectDoc(".form-address-edit button#submit");

        this.orderView = selectDoc('.customer-order-view')
    }

    /**
     *
     */
    initTabs() {
        let self = this;

        const loaderAccountForm = (form) => {
            if(form) {
                setTimeout(function () {
                    let valid = true;
                    selectArr('.mage-error', form).map(err => {
                        let style = window.getComputedStyle(err);
                        if(style.display !== 'none') {
                            valid = false;
                        }
                    });
                    if(valid) {
                        showLoader()
                    }
                }, 300)
            }
        }

        /**
         * Before Login
         */
        this.tabs.map(tab => {
            event(tab, 'click', function () {
                removeClassAll(self.tabs, 'active')
                addClass(tab, 'active')
                let target = selectDoc(`.account-container .${tab.getAttribute('data-target')}`)
                if (target) {
                    removeClassAll(self.containers, 'active')
                    addClass(target, 'active')
                }
            })
        });

        if (this.signUpButton && this.loginButton) {
            event(this.signUpButton, 'click', function () {
                loaderAccountForm(selectDoc('#signup-form-validate'));
            })

            event(this.loginButton, 'click', function () {
                loaderAccountForm(selectDoc('#login-form'));
            })
        }

        if (this.forgotButton) {
            event(this.forgotButton, 'click', function () {
                loaderAccountForm(selectDoc('#form-validate'));
            })
        }
    }

    /**
     *
     */
    checkUrl() {
        if (window.location.href.includes('account/login')) {
            let loginCta = selectDoc('[data-target="login-container"]')
            if (loginCta) {
                loginCta.click();
            }
        } else {
            if (window.location.href.includes('account/create')) {
                let registerCta = selectDoc('[data-target="create-container"]')
                if (registerCta) {
                    registerCta.click();
                }
            }
        }
    }

    /**
     *
     */
    initAvatarUpload() {
        let self = this;
        if (this.avatarContainer) {
            event(this.avatarContainer, 'click', function () {
                self.avatarInput.click();
            })

            event(this.avatarInput, 'change', function (event) {
                let file = event.target.files[0],
                    reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function (e) {
                    self.avatarImg.style.opacity = '0.5'
                    self.avatarSpan ? self.avatarSpan.style.opacity = '0.5' : ''
                    ajax('POST',
                        '/rest/V1/kip/customer/avatar',
                        JSON.stringify({
                            base64: reader.result,
                            id: self.avatarId.value,
                        }),
                        'application/json',
                        null,
                        'Bearer ' + self.avatarToken.value
                    ).then(function (response) {
                        self.avatarImg.style.opacity = '1'
                        self.avatarSpan ? self.avatarSpan.style.opacity = '1' : ''
                        let data = JSON.parse(response);
                        if (data.status === 200) {
                            self.avatarImg.src = JSON.parse(data.response).src;
                            self.avatarImg.classList.remove('no-avatar');
                            self.avatarSpan ? self.avatarSpan.classList.remove('no-avatar') : '';
                        }
                    })
                }.bind(this);
            })
        }
    }

    /**
     *
     */
    initModals() {
        this.initExperienceModal();

        this.initAskProductModal();

        this.initReportErrorModal();
    }

    /**
     *
     */
    initExperienceModal() {
        let self = this;
        if (this.experienceModal) {
            event(this.experienceAccept, 'click', function () {
                showLoader();
                let f = new FormData(self.experienceForm);
                if (f) {
                    let data = {};
                    for (let prop of f.entries()) {
                        if (!data[prop[0]]) {
                            data[prop[0]] = [];
                        }
                        if (prop[1]) {
                            data[prop[0]].push(prop[1]);
                        }
                    }
                    self.experienceAccept.style.opacity = '0.5'
                    ajax('POST',
                        '/rest/V1/kip/customer/experience',
                        JSON.stringify({
                            data: JSON.stringify(data)
                        }),
                        'application/json',
                        null,
                        'Bearer ' + self.avatarToken.value
                    ).then(function (response) {
                        showLoader(true)
                        self.experienceAccept.style.opacity = '1'
                        let data = JSON.parse(response);
                        if (data.status === 200) {
                            self.modals.closeModal();
                        }
                    })
                }
            })

            event(this.experienceLater, 'click', function () {
                showLoader();
                ajax('POST',
                    '/rest/V1/kip/customer/experience',
                    JSON.stringify({
                        data: JSON.stringify({})
                    }),
                    'application/json',
                    null,
                    'Bearer ' + self.avatarToken.value
                ).then(function (response) {
                    showLoader(true)
                    self.experienceAccept.style.opacity = '1'
                    let data = JSON.parse(response);
                    if (data.status === 200) {
                        self.modals.closeModal();
                    }
                })
            })

            if (this.experienceData.value) {
                let data = JSON.parse(this.experienceData.value);

                let keys = Object.keys(data);
                Object.values(data).map((val, i) => {
                    let inputs = selectArr(`[name="${keys[i]}"]`, self.experienceForm);
                    inputs.map(input => {
                        if(input.type === 'checkbox') {
                            val.map(v => {
                                if(input.value === v) {
                                    input.click();
                                }
                            })
                        } else {
                            input.value = val;
                        }
                    })

                })

            }
        }
    }

    /**
     *
     */
    initAskProductModal() {
        let self = this;
        if (this.askProductModal) {
            event(this.askProductAsk, 'click', function () {
                showLoader();
                let f = new FormData(self.askProductForm);
                if (f) {
                    let data = '';
                    for (let prop of f.entries()) {
                        if (prop[0] && prop[1]) {
                            data += `${prop[0]}=`;
                            if(prop[0] === 'comment') {
                                data += `${encodeURI('Solicitud producto nuevo: ' + prop[1])}&`;
                            } else {
                                data += `${encodeURI(prop[1])}&`;
                            }
                        }
                    }

                    self.askProductAsk.style.opacity = '0.5'
                    ajax('POST',
                        '/contact/index/post?' + data,
                        null,
                        'text/html; charset=UTF-8',
                    ).then(function (response) {
                        self.askProductAsk.style.opacity = '1'
                        let data = JSON.parse(response);
                        if (data.status === 200) {
                            showLoader(true)
                            self.askProductContent.classList.add('hide')
                            self.askProductSuccess.classList.remove('hide')
                        } else {
                            showLoader(true)
                        }
                    })
                }
            })
        }
    }

    /**
     *
     */
    initReportErrorModal() {
        let self = this;
        if (this.reportErrorModal) {
            event(this.reportErrorReport, 'click', function () {
                showLoader();
                let f = new FormData(self.reportErrorForm);
                if (f) {
                    let data = '';
                    for (let prop of f.entries()) {
                        if (prop[0] && prop[1]) {
                            data += `${prop[0]}=`;
                            if(prop[0] === 'comment') {
                                data += `${encodeURI('Reporte error sitio: ' + prop[1])}&`;
                            } else {
                                data += `${encodeURI(prop[1])}&`;
                            }
                        }
                    }

                    self.reportErrorReport.style.opacity = '0.5'
                    ajax('POST',
                        '/contact/index/post?' + data,
                        null,
                        'text/html; charset=UTF-8',
                    ).then(function (response) {
                        self.reportErrorReport.style.opacity = '1'
                        let data = JSON.parse(response);
                        if (data.status === 200) {
                            showLoader(true)
                            self.reportErrorContent.classList.add('hide')
                            self.reportErrorSuccess.classList.remove('hide')
                        } else {
                            showLoader(true)
                        }
                    })
                }
            })
        }
    }

    /**
     *
     */
    initAddress() {
        let self = this;
        if (this.addressForm) {
            event(this.addressNext, 'click', function (e) {
                e.preventDefault();
                self.addressStep++;
                self.addressStepLabel.innerHTML = 'Llena los detalles de tu dirección de entrega.';
                removeClassAll(self.addressSteps, 'active')
                addClass(selectDoc('button[data-target="step2"]'), 'active')

                let target = selectDoc(`.form-address-edit .step2`)
                if (target) {
                    removeClassAll(self.addressStepsContainers, 'active')
                    addClass(target, 'active')
                }
                self.addressSubmit.classList.add('active')
                this.classList.remove('active')
            });

            event(this.addressBack, 'click', function (e) {
                e.preventDefault();
                self.addressStep--;
                self.addressStepLabel.innerHTML = 'Busca y señala tu ubicación en el mapa. Puedes mover el PIN o dar clic en el lugar.';

                if (self.addressStep === 0) {
                    window.location.href = '/customer/address'
                } else {
                    self.addressNext.classList.add('active')
                    self.addressSubmit.classList.remove('active')
                    removeClassAll(self.addressSteps, 'active')

                    addClass(selectDoc('button[data-target="step1"]'), 'active')
                    let target = selectDoc(`.form-address-edit .step1`)
                    if (target) {
                        removeClassAll(self.addressStepsContainers, 'active')
                        addClass(target, 'active')
                    }
                }
            })
        }
    }

    /**
     *
     */
    initSales() {
        if (this.orderView) {}
    }
}

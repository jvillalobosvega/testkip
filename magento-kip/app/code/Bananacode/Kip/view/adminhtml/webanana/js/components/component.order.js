/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    observeRemove,
    ajax
} from "../utils/wonder";

import {inputEmpty} from "../helpers/service.validator";

export class Order {
    /**
     * Constructor
     */
    constructor() {
        if(selectDoc('body.sales-order_create-index')) {
            this.init();
        }

        if(selectDoc('body.sales-order-view')) {
            this.initLS();
        }
    }

    /**
     * Initialize class
     */
    init() {
        this.props();

        let self = this;
        if(selectDoc('#scheduled #scheduled-date')) {
            this.initKipping();
        }
        observeRemove(selectDoc('body.sales-order_create-index'), function (nodes) {
            if(nodes[0].nodeType === 3) {
                if(!nodes[0].parentElement) {
                    if(nodes[0].wholeText.includes('Create New Order')) {
                        setTimeout(function () {
                            self.initKipping();
                        }, 5000)
                    }
                }
            }
        })
    }

    /**
     *
     */
    initLS() {
        this.sendLS();
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
    }

    /**
     *
     * @param property
     * @param value
     */
    updateSession(property, value) {
        let customerId = selectDoc('#kip_customer_id'),
            session = JSON.parse(localStorage.getItem('kippingadmin' + customerId.value));
        if (!session) {
            session = {};
        }
        session[property] = value;
        localStorage.setItem('kippingadmin' + customerId.value, JSON.stringify(session));
    }

    /**
     *
     * @param hour
     * @returns {*}
     */
    mapHour(hour) {
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

    /**
     * @param hour
     * @param range
     * @returns {string}
     */
    renderHour(hour, range) {
        let self = this,
            isDisabled = hour.split(':'),
            disabled = '';

        if(isDisabled.length > 1) {
            disabled = 'disabled';
            hour = isDisabled[0];
        }

        let hourInterval = parseInt(hour) + parseInt(range);

        return `<option ${disabled} value="${hour}">${(self.mapHour(hour) + (hour > 11 ? ':00 pm' : ':00 am'))} - ${(self.mapHour(hourInterval) + (hourInterval > 11 ? ':00 pm' : ':00 am'))}</option>`;
    }

    /**
     *
     */
    initKipping() {
        let self = this,
            packageTypes = selectDoc('#packaging #packaging-select'),
            orderNote = selectDoc('#order-note textarea'),

            daysSelector = selectDoc('#scheduled #scheduled-date'),
            hoursSelector = selectDoc('#scheduled #scheduled-hours'),

            daysFestSelector = selectDoc('#scheduled_festivity #scheduled-date'),
            hoursFestSelector = selectDoc('#scheduled_festivity #scheduled-hours');

        let formKey = selectDoc('#kip_form_key'),
            customerId = selectDoc('#kip_customer_id');

        //Clear session
        localStorage.removeItem('kippingadmin' + customerId.value);

        //Init scheduled days/hours
        /**
         * @param daysSelector
         * @param hoursSelector
         * @param days
         * @param hours
         */
        const initDateTimeSelectors = (daysSelector, hoursSelector, days, hours) => {
            if (daysSelector && hoursSelector) {
                if (days.length > 0) {
                    days.map(day => {
                        daysSelector.innerHTML += `<option class="${day.class}" value="${day.value}">${day.label}</option>`
                    });
                    let scheduleHours = hours[days[0].value];
                    if (scheduleHours) {
                        scheduleHours.hours.split(',').map(hour => {
                            hoursSelector.innerHTML += self.renderHour(hour, scheduleHours.hours_range);
                        })
                    }

                    event(daysSelector, 'change', function (e) {
                        self.updateSession('scheduled_day', e.target.value);
                        self.updateSession('scheduled_hour', null);
                        if (hours[e.target.value]) {
                            let hours = hours[e.target.value];
                            hoursSelector.innerHTML = `<option class="medium-14" value="">Horas disponibles</option>`;
                            hours.hours.split(',').map(hour => {
                                hoursSelector.innerHTML += self.renderHour(hour, hours.hours_range);
                            })
                        }
                    })

                    event(hoursSelector, 'change', function (e) {
                        self.updateSession('scheduled_hour', e.target.value);
                    })
                }
            }
        }

        ajax('GET', `/rest/V1/kipping/schedules?form_key=${formKey.value}`)
            .then(function (response) {
                let data = (JSON.parse(response));
                if (data.status === 200) {
                    let schedules = JSON.parse(data.response);
                    if (schedules.availableDays && schedules.availableHours) {
                        initDateTimeSelectors(daysSelector, hoursSelector, schedules.availableDays, schedules.availableHours)
                    }

                    if (schedules.availableDaysFestivity && schedules.availableHoursFestivity) {
                        initDateTimeSelectors(daysFestSelector, hoursFestSelector, schedules.availableDaysFestivity, schedules.availableHoursFestivity)
                    }
                }
            });

        //Init order note
        event(orderNote, 'keyup', function (e) {
            self.updateSession('order_note', e.target.value);
        });

        //Init packages chooser
        event(packageTypes, 'change', function (e) {
            self.updateSession('package', e.target.value);
        });

        //Init tax documents
        ajax('GET',
            `/rest/V1/bananacodeTaxDocument/getApprovedDocumentsByCustomer?form_key=${formKey.value}&ci=${customerId.value}`
        ).then(function (response) {
            let data = (JSON.parse(response));
            if (data.status === 200) {
                let documents = JSON.parse(data.response),
                    customerDocs = selectDoc('#tax-documents select#customer-documents'),
                    typesTD = selectDoc('#tax-documents select#documents-types');

                if (documents.output.totalRecords > 0) {
                    documents.output.items.map(document => {
                        customerDocs.innerHTML += `<option class="document-${document.name} ${document.name === 'CCF' ? 'active' : ''}" value="${document.document_id}">${document.registry_number ?? document.id_number}</option>`
                    });
                }

                event(typesTD, 'change', function (e) {
                    e.preventDefault();
                    selectArr('#tax-documents select#customer-documents option:not(.first)').map(o => {
                        o.disabled = true;
                    });
                    if (!inputEmpty(e.target.value)) {
                        //Document type selected
                        self.updateSession('invoice_document', e.target.value);
                        if (e.target.value !== 'FCF') { //Stored document
                            let currentOptions = selectArr(`#tax-documents select#customer-documents option.document-${e.target.value}`);
                            currentOptions.map(co => {
                                co.disabled = false;
                            });
                            self.updateSession('invoice_document', null);
                        }
                    }
                });

                event(customerDocs, 'change', function (e) {
                    e.preventDefault();
                    if (!inputEmpty(e.target.value)) {
                        self.updateSession('invoice_document', e.target.value);
                    }
                });

                self.updateSession('invoice_document', null);
            }
        })
    }

    /**
     *
     */
    sendLS() {
        let customerId = selectDoc('#kip_customer_id'),
            session = JSON.parse(localStorage.getItem('kippingadmin' + customerId.value)),
            formKey = selectDoc('#kip_form_key'),
            orderId = selectDoc('#kip_order_id');

        if(session && orderId && formKey) {
            session.order_id = orderId.value;
            ajax('POST',
                `/rest/V1/kipping/comment?form_key=${formKey.value}`,
                JSON.stringify({
                    data: JSON.stringify(session)
                }),
                'application/json'
            ).then(function () {
                localStorage.removeItem('kippingadmin' + customerId.value);
                window.location.reload();
            });
        }
    }
}

/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    toggleClass,
    addClassAll,
    hasClass, removeClassAll, ajax, addClass, removeClass,
} from "../utils/wonder";
import {customShippingOptions} from "../helpers/service.markup";
import {showLoader} from "../helpers/service.dialog";
import {inputEmpty} from "../helpers/service.validator";

export class Kipping {
    /**
     * Header constructor
     */
    constructor() {
        this.init();
    }

    /**
     * Initialize class
     */
    init() {
        this.props();

        //Wait for available shipping methods to load...
        let self = this;
        setTimeout(function () {
            self.initShippingCustomOptions();
        }, 1500)
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.hoursMap = []
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

    updateSession(property, value) {
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
    initShippingCustomOptions() {
        let shippingMethodsContainer = selectDoc('#checkout-shipping-method-load'),
            self = this;

        if (shippingMethodsContainer) {
            let customOptions = document.createElement('div');
            customOptions.id = 'kip-shipping-custom-options';
            customOptions.innerHTML = customShippingOptions();
            shippingMethodsContainer.appendChild(customOptions);

            let shippingTypes = selectArr('#shipping-type .shipping-type:not(.disabled)'),
                coreShippingTypes = selectArr('.table-checkout-shipping-method input.radio'),
                packageTypes = selectArr('#packaging .packaging-type'),
                session = JSON.parse(localStorage.getItem('kipping')),
                estimatedTotal = selectDoc('#shipping-estimated #total'),
                orderNote = selectDoc('#order-note textarea'),

                dateTime = selectDoc('#kip-shipping-custom-options #scheduled'),
                daysSelector = selectDoc('#scheduled #scheduled-date'),
                hoursSelector = selectDoc('#scheduled #scheduled-hours'),

                dateTimeFest = selectDoc('#kip-shipping-custom-options #scheduled_festivity'),
                daysFestSelector = selectDoc('#scheduled_festivity #scheduled-date'),
                hoursFestSelector = selectDoc('#scheduled_festivity #scheduled-hours');

            //Bind shipping methods
            shippingTypes.map(type => {
                event(type, 'click', function (e) {
                    e.preventDefault();
                    let method = selectDoc(`#label_method_${type.id}`),
                        change = new Event('change');
                    if (method) {
                        method.parentNode.click();
                        removeClassAll(shippingTypes, 'checked')
                        type.classList.add('checked');

                        let price = selectDoc('.col-price > .price > .price',method.parentNode);
                        estimatedTotal.innerHTML = `${price.innerHTML}`;

                        //Reset all
                        dateTime.classList.remove('active');
                        dateTimeFest.classList.remove('active');

                        self.updateSession('scheduled_day', null);
                        self.updateSession('scheduled_hour', null);

                        daysSelector.selectedIndex = 0;
                        hoursSelector.selectedIndex = 0;
                        daysFestSelector.selectedIndex = 0;
                        hoursFestSelector.selectedIndex = 0;

                        if(type.id === 'scheduled_festivity_kipping') {
                            dateTimeFest.classList.add('active');
                            self.updateSession('method', type.id.replace('_kipping', ''));
                        } else {
                            if (type.id === 'scheduled_kipping' || type.id === 'scheduled_today_kipping') {
                                let scheduledTodayOption = selectDoc('option.scheduled_today', daysSelector)
                                if(type.id === 'scheduled_today_kipping') {
                                    selectArr('option:not(.scheduled_today)', daysSelector).map(o => {
                                        o.disabled = true;
                                        addClass(o, 'disabled');
                                    })
                                    if(scheduledTodayOption) {
                                        removeClass(scheduledTodayOption, 'disabled');
                                        scheduledTodayOption.disabled = false;
                                        daysSelector.selectedIndex = 1;
                                    }
                                } else {
                                    selectArr('option', daysSelector).map(o => {
                                        o.disabled = false;
                                        removeClass(o, 'disabled');
                                    })
                                    if(scheduledTodayOption) {
                                        addClass(scheduledTodayOption, 'disabled');
                                        scheduledTodayOption.disabled = true;
                                    }
                                }
                                dateTime.classList.add('active');
                                self.updateSession('method', type.id.replace('_kipping', ''));
                            } else {
                                self.updateSession('method', type.id.replace('_kipping', ''));
                            }
                        }
                        daysSelector.dispatchEvent(change);
                    }
                })
            });

            //Init packages chooser
            packageTypes.map(type => {
                event(type, 'click', function (e) {
                    e.preventDefault();
                    removeClassAll(packageTypes, 'checked');
                    type.classList.add('checked');
                    self.updateSession('package_method', type.id);
                })

                if (session) {
                    if (session.package_method) {
                        if (type.id === session.package) {
                            type.click();
                        }
                    }
                }
            });

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
                                let currentHours = hours[e.target.value];
                                hoursSelector.innerHTML = `<option class="medium-14" value="">Horas disponibles</option>`;
                                currentHours.hours.split(',').map(hour => {
                                    hoursSelector.innerHTML += self.renderHour(hour, currentHours.hours_range);
                                })
                            }
                        })

                        event(hoursSelector, 'change', function (e) {
                            self.updateSession('scheduled_hour', e.target.value);
                        })
                    }
                }
            }

            //Init scheduled days/hours
            ajax('GET', `/rest/V1/kipping/schedules`)
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

                    shippingTypes.map(type => {
                        coreShippingTypes.map(coreType => {
                            if (coreType.checked) {
                                let currentCore = coreType.value.replaceAll('kipping', '').replaceAll('_', ''),
                                    currentCustom = type.id.replaceAll('kipping', '').replaceAll('_', '');
                                if (currentCore === currentCustom) {
                                    type.click();
                                }
                            }
                        });
                    })
                });

            //Init order note
            let note = '',
                charCounter = selectDoc('#order-note .left')

            if (session) {
                if (session.order_note) {
                    orderNote.value = session.order_note;
                    charCounter.innerHTML = (140 - session.order_note.length);
                    note = session.order_note;
                }
            }

            let typingTimer;

            event(orderNote, 'input', function (e) {
                if (e.inputType !== 'deleteContentBackward') {
                    if (note.length < 140) {
                        note = e.target.value;
                        charCounter.innerHTML = (140 - note.length);
                    } else {
                        e.target.value = note;
                    }
                } else {
                    note = e.target.value;
                    charCounter.innerHTML = (140 - note.length);
                }
            })

            event(orderNote, 'keyup', function () {
                clearTimeout(typingTimer);
                //if (!inputEmpty(note)) {
                    typingTimer = setTimeout(function () {
                        self.updateSession('order_note', note);
                    }, 1000);
                //}
            })

            event(orderNote, 'keydown', function () {
                clearTimeout(typingTimer);
            })
        }
    }
}

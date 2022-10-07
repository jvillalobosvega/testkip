/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    hasClass, observeAdd, isVisible,
} from "../utils/wonder";

import {showLoader, displayModalError} from '../helpers/service.dialog'

import doCookies from "../lib/docookies.min";

export class Modals {
    /**
     *
     * @param init
     */
    constructor(init = true) {
        if(init) {
            this.init();
        }
    }

    /**
     * Initialize class
     */
    init() {
        let self = this;
        setTimeout(function () {
            self.props();
            self.initNotifications();
            self.initLogOut();
            self.initModals();
        }, 1500)
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.messages = selectDoc(".page.messages");

        this.modalCount = 0;
        this.listener = null;
        this.modal = null;
        this.modalCloses = selectArr('.modal .close-modal');
        this.modalOpeners = selectArr('.open-modal');

        this.shippingZone = selectDoc('#shipping-zone.modal-container');
    }

    /**
     *
     */
    initNotifications() {
        if (this.messages) {
            observeAdd(this.messages, function (node) {
                if (node[0]) {
                    if (node[0].className) {
                        if (node[0].className.includes('message-')) {
                            node[0].style.display = 'none !important';
                            let divMsg = selectDoc('div', node[0]);
                            if (divMsg) {
                                if (node[0].className.includes('error')) {
                                    displayModalError(divMsg.innerText)
                                } else {
                                    displayModalError(divMsg.innerText, true)
                                }
                            }
                        }
                    }
                }
            })
        }
    }

    /**
     *
     */
    initLogOut() {
        this.logoutActions = selectArr('a.logout');
        this.logout = selectDoc('#logout-ask.modal-container button#logout');

        let self = this;
        this.logoutActions.map(logout => {
            event(logout, 'click', function (e) {
                e.preventDefault();
                self.modal = selectDoc('#logout-ask.modal-container');
                self.openModal();
            })
        })

        if(this.logout) {
            event(this.logout, 'click', function () {
                showLoader();
                localStorage.removeItem('kipcart');
                localStorage.removeItem('kiptoken');
                window.location.href = '/customer/account/logout';
            });
        }
    }

    /**
     *
     * @param e
     * @param self
     */
    checkOutSide(e, self) {
        let modal = selectDoc('.modal', self.modal);
        if (!modal.contains(e.target) && isVisible(modal)) {
            if (self.modalCount > 0) {
                self.closeModal();
            } else {
                self.modalCount++;
            }
        } else {
            self.modalCount++;
        }
    }

    /**
     *
     */
    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active')
        }
        selectArr('.modal-container.active').map(modal => {
            modal.classList.remove('active')
        })

        this.modalCount = 0;
        selectDoc('html').classList.remove('noscroll')
        document.removeEventListener("click", this.listener);
    }

    /**
     *
     */
    openModal() {
        let self = this;
        this.modal.classList.add('active')
        this.html.classList.add('noscroll')
        event(document, 'click', self.listener = function (e) {
            self.checkOutSide(e, self)
        });
    }

    /**
     *
     */
    initModals() {
        let self = this;

        this.modalCloses.map(close => {
            event(close, 'click', function () {
                self.closeModal();
            })
        })

        this.modalOpeners.map(open => {
            event(open, 'click', function () {
                self.modal = selectDoc(`#${open.getAttribute('data-m')}.modal-container`);
                if (self.modal) {
                    if (!hasClass(self.modal, 'active')) {
                        self.openModal();
                    }
                }
            })

            if (open.getAttribute('data-m') === 'logged-experience') {
                self.modal = selectDoc(`#${open.getAttribute('data-m')}.modal-container`);
                if (self.modal) {
                    if (!hasClass(self.modal, 'active') && !hasClass(self.modal, 'hide')) {
                        self.openModal();
                    }
                }
            }
        })
    }
}

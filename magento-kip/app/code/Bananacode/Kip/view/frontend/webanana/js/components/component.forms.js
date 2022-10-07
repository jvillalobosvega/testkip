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
    hasClass, removeClass, addClass,
} from "../utils/wonder";
import {initInput} from "../helpers/service.inputs";

export class Forms {
    /**
     * Forms constructor
     */
    constructor() {
        this.init();
    }

    /**
     * Initialize class
     */
    init() {
        this.props();
        this.initInputs();
        this.initCheckboxes();
        this.initPasswords();
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
     */
    initCheckboxes() {
        selectArr(".field.choice input:not(.event)").map(checkbox => {
            addClass(checkbox, 'event')
            event(checkbox, 'click', function () {
                toggleClass(checkbox.parentNode, 'checked');
                checkbox.checked = hasClass(checkbox.parentNode, 'checked');
            })
        })
    }

    /**
     *
     */
    initInputs() {
        selectArr("input:not([type='checkbox']):not([type='file']):not(.event)").map(input => {
            addClass(input, 'event')
            initInput(input)
        })

        selectArr("form textarea:not(.event)").map(textarea => {
            addClass(textarea, 'event')
            initInput(textarea)
        })
    }

    /**
     *
     */
    initPasswords() {
        selectArr(".field.password:not(.event)").map(password => {
            addClass(password, 'event')
            event(password, 'click', function (e) {
                if (hasClass(e.target, 'field')) {
                    let input = selectDoc('input', password);
                    if (input) {
                        if (hasClass(password, 'show')) {
                            input.setAttribute('type', 'password');
                            removeClass(password, 'show')
                        } else {
                            input.setAttribute('type', 'text');
                            addClass(password, 'show')
                        }
                        input.focus();
                    }
                }
            })
        })
    }
}

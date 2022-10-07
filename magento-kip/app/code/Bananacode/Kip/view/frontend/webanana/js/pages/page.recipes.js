/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    addClass,
    removeClassAll
} from "../utils/wonder";

export class Recipes {
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
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");

        this.tabs = selectArr(".tabs button");
        this.containers = selectArr(".tabs > div");
    }

    /**
     *
     */
    initTabs() {
        let self = this;

        this.tabs.map(tab => {
            event(tab, 'click', function () {
                removeClassAll(self.tabs, 'active')
                addClass(tab, 'active')
                let target = selectDoc(`.tabs .${tab.getAttribute('data-target')}`)
                if (target) {
                    removeClassAll(self.containers, 'active')
                    addClass(target, 'active')
                }
            })
        })
    }
}

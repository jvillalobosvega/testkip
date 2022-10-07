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
    hasClass,
} from "../utils/wonder";
import {initSlider} from "../helpers/service.slider";

export class Home {
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
        this.initInspirations();
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
    }

    initInspirations() {
        selectArr('#home-inspirations .row .col').map(card => {
            event(card, 'click', function (){
                let a = selectDoc('a', card);
                if(a) {
                    a.click();
                }
            })
        })
    }
}

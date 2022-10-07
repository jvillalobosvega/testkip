/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    addClass,
    event,
    selectArr,
    selectDoc,
} from "../utils/wonder";
import {initSlider} from "../helpers/service.slider";

export class Sliders {
    /**
     * Header constructor
     */
    constructor(container) {
        this.init();
    }

    /**
     * Initialize class
     */
    init() {
        this.props();
        this.initSliders();
        this.initArrows();
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
    }

    initSliders() {
        selectArr('.kip-slider .slider').map(slider => {
            let id = 'slider-'+ Math.floor(Math.random() * 100) + Date.now();
            slider.id = id;
            let ul = document.querySelector(`#${id} ul`);
            if(ul.children.length > 1) {
                /*for (let i = ul.children.length; i >= 0; i--) {
                    ul.appendChild(ul.children[Math.random() * i | 0]);
                }*/
                initSlider(`#${id}`, {
                    perView: 1,
                    type: 'carousel',
                    autoplay: 5000
                })
            }
        })
    }

    initArrows() {
        const hideArrows = (arrow, container) => {
            let widget = selectDoc(container);
            if(widget.parentNode.id == 'kip-promoted-products') {
                if(selectArr('ol li', widget).length <= 4) {
                    addClass(arrow, 'mobile-only');
                }
            }
        }

        const scroll = (arrow, container) => {
            let step = -30,
                limit = (window.innerWidth/3),
                scrollContainer = selectDoc(`${container} ol`, arrow.parentNode);

            if (arrow.className.includes('right')) {
                step = 30;
            }

            if(window.innerWidth <= 768) {
                limit = (window.innerWidth - 40) / 1.5;
            }

            let scrollAmount = 0,
                slideTimer = setInterval(function () {
                    scrollContainer.scrollLeft += step;
                    scrollAmount += 30;
                    if (scrollAmount >= limit) {
                        window.clearInterval(slideTimer);
                    }
                }, 25);
        }

        selectArr('.block.widget button.arrow').map(arrow => {
            hideArrows(arrow, '.block.widget');
            event(arrow, 'click', function () {
                scroll(arrow, '.block.widget')
            });
        })

        selectArr('.block.related button.arrow').map(arrow => {
            hideArrows(arrow, '.block.related');
            event(arrow, 'click', function (){
                scroll(arrow, '.block.related')
            });
        })
    }
}

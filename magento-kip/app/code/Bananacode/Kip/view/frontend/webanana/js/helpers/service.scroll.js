/*
 *
 * Images Lazy Load service
 *
 * */

import {event, removeClass, selectArr, selectDoc} from "../utils/wonder";

export const scrollValidations = () => {
    /**
     * Images Lazy Load
     */
    let bottomLimit = (window.innerHeight || document.documentElement.clientHeight) * 1.25;

    selectArr('img.lazy-load-img').map(image => {
        let bounding = image.getBoundingClientRect();
        if (
            bounding.top >= 0 &&

            bounding.bottom > 0 &&
            bounding.bottom <= bottomLimit

            && bounding.left >= 0
            && image.parentNode.getBoundingClientRect().left >= 0
        ) {
            image.src = image.getAttribute('data-src')
            removeClass(image, 'lazy-load-img')
        }
    })

    /**
     * Animations
     */
    let animations = selectArr(".anm:not(.anm-done)");
    animations.map((animation) => {
        let bounding = animation.getBoundingClientRect();
        if (
            bounding.top <=
            (window.innerHeight || document.documentElement.clientHeight)
        ) {
            animation.classList.add("anm-done");

            let timeOut = 0;
            selectArr(".fade-up", animation).map((fadeUp) => {
                setTimeout(function () {
                    fadeUp.classList.add("up");
                }, timeOut);
                timeOut += 100;
            });
        }
    });
};

export const initScroll = () => {
    window.removeEventListener("scroll", scrollValidations);
    event(window, "scroll", scrollValidations);

    let algoliaAutoComplete = selectDoc('#algolia-autocomplete-container span.aa-dropdown-menu');
    if(algoliaAutoComplete) {
        event(algoliaAutoComplete, "scroll", scrollValidations);
    }

    scrollValidations();
};

/**
 *
 * @returns {number}
 */
export const lazyLoadIcons = () => {
    selectArr('img.icon.lazy-load-img').map(lazyIcon => {
        lazyIcon.src = lazyIcon.getAttribute('data-src')
        removeClass(lazyIcon, 'lazy-load-img')
    });

    return selectArr('img.icon.lazy-load-img').length;
}

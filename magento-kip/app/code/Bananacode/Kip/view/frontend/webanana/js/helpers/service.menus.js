import {
    addClass,
    event,
    hasClass,
    removeClass,
    removeClassAll,
    selectArr,
    selectDoc
} from "../utils/wonder";

export const initMenuItems = (menuId) => {
    selectArr(`${menuId} .filter-options-item:not(.no-collapse)`).map(category => {
        event(category, 'click', function () {
            let activeCategories = selectArr(`${menuId} .filter-options-item:not(.no-collapse).active`),
                containerDisable = selectDoc(`${menuId} .filter-options-item:not(.no-collapse).active .filter-options-content`),
                items = selectArr(`${menuId} .filter-options-content ul .filter-options-item`, category),
                currentContainer = selectDoc(`${menuId} .filter-options-content`, category),
                height = 0;

            if(!hasClass(category, 'active')) {
                removeClassAll(activeCategories, 'active');
                items.map(i => {height += i.getBoundingClientRect().height;})
                currentContainer.style.height = `${height}px`;
                containerDisable ? containerDisable.style.height = `0` : '';
                addClass(category, 'active');
            } else {
                currentContainer.style.height = `0`
                removeClass(category, 'active')
            }
        });
    });
};

export const matchMenuItemsWUrl = (menuId) => {
    let activeItem = selectDoc(`${menuId} .filter-options-item.cat.allow.no-collapse.active a`);
    if(activeItem) {
        if(activeItem.getAttribute('href').trim() !== window.location.href.trim()) {
            let realItem = selectDoc(`${menuId} .filter-options-item.cat.allow.no-collapse a[href="${window.location.href.trim()}"]`);
            if(realItem) {
                removeClass(activeItem.parentNode.parentNode,'active');
                activeItem.parentNode.parentNode.parentNode.parentNode.parentNode.click();

                addClass(realItem.parentNode.parentNode, 'active');
                realItem.parentNode.parentNode.parentNode.parentNode.parentNode.click();
            }
        } else {
            activeItem.parentNode.parentNode.parentNode.parentNode.parentNode.click();
        }
    }
};

/*
 *
 * JS Imports
 *
 * */

/*
 *
 * App Initialization
 *
 * */
import {Order} from "./components";
import {observeAdd, selectDoc, hasClass} from "./utils/wonder";

(function () {
    observeAdd(selectDoc('html'), function (nodes) {
        if (!Array.isArray(nodes)) {
            nodes = [].push(nodes);
        }

        nodes.map(node => {
            if (node.className) {
                if (typeof node.className === 'string' || node.className instanceof String) {
                    if (hasClass(node, 'page-footer')) {
                        new Order();
                    }
                }
            }
        })
    })
})();

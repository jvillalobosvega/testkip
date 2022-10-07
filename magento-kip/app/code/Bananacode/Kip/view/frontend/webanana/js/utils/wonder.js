/**
 *
 * @param query
 * @param item
 * @returns {NodeListOf<Element>}
 */
export const selectAll = (query, item = document) =>
  item.querySelectorAll(query);

/**
 *
 * @param query
 * @param item
 * @returns {*[]}
 */
export const selectArr = (query, item = document) =>
  [].slice.call(item.querySelectorAll(query));

/**
 *
 * @param query
 * @param item
 * @returns {Element}
 */
export const selectDoc = (query, item = document) => item.querySelector(query);

/**
 *
 * @param item
 * @param cls
 * @returns {boolean}
 */
export const toggleClass = (item, cls) => item.classList.toggle(cls);

/**
 *
 * @param elem
 * @param styles
 * @returns {*|void}
 */
export const styles = (elem, styles) =>
  elem.setAttribute(
    "style",
    Object.entries(styles)
      .map(([key, value]) => `${key}: ${value};`)
      .join(" "));

/***
 *
 * @param target
 * @param className
 * @returns {boolean}
 */
export const hasClass = (target, className) =>
    target ? (new RegExp("(\\s|^)" + className + "(\\s|$)").test(target.className)) : false;

/***
 *
 * @param target
 * @param className
 * @returns {boolean}
 */
export const hasClassSVG = (target, className) =>
    target.baseVal ? (new RegExp("(\\s|^)" + className + "(\\s|$)").test(target.baseVal)) : false;

/***
 *
 * @param elem
 * @returns {boolean}
 */
export const isVisible = (elem) =>
  !!elem &&
  !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length); // source (2018-03-11): https://github.com/jquery/jquery/blob/master/src/css/hiddenVisibleSelectors.js

/**
 *
 * @param list
 * @param cls
 * @returns {*}
 */
export const removeClassAll = (list, cls) =>
  list.map((l) => l.classList.remove(cls));

/**
 *
 * @param list
 * @param cls
 * @returns {*}
 */
export const addClassAll = (list, cls) => list.map((l) => l.classList.add(cls));

/**
 *
 * @param items
 * @param cls
 * @returns {boolean[]}
 */
export const toggleClassAll = (items, cls) =>
  [].slice.call(items).map((item) => item.classList.toggle(cls));

/**
 *
 * @param elem
 * @param event
 * @param func
 */
export const event = (elem, event, func) =>
  elem
    ? elem.addEventListener(event, func)
    : console.log("Invalid element to apply " + event + ": " + func);

/**
 * Verify if has class and then add it
 * @param e
 * @param cls
 * @returns {any}
 */
export const addClass = (e, cls) => (!hasClass(e, cls) && e) ? e.classList.add(cls): false;

/**
 * Verify if has class and then remove it
 * @param e
 * @param cls
 * @returns {any}
 */
export const removeClass = (e, cls) => e ? (hasClass(e, cls) ? e.classList.remove(cls): false) : false;

/**
 *
 * @param method
 * @param url
 * @param data
 * @param contentType
 * @param xhttp
 * @param auth
 * @returns {Promise<any>}
 */
export const ajax = (
  method,
  url,
  data = {},
  contentType = "application/json",
  xhttp = null,
  auth = null
) => {
  return new Promise((resolve) => {
    if (!xhttp) {
      xhttp = new XMLHttpRequest();
    }
    xhttp.onreadystatechange = function () {
      if (xhttp.readyState === 4) {
        resolve(
          JSON.stringify({
            status: xhttp.status,
            response: xhttp.responseText,
          })
        );
      }
    };

    xhttp.open(method, url, true);
    if(auth) {
      xhttp.setRequestHeader("Authorization", auth);
    }
    if (method === "POST" && data) {
      xhttp.setRequestHeader("Content-type", contentType);
      xhttp.send(data);
    } else {
      xhttp.send();
    }

    return xhttp;
  });
};

/**
 *
 * @param container
 * @param callback
 * @returns {Promise<void>}
 */
export const observeAdd = (container, callback) => {
  new MutationObserver((mutationsList, observer) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "childList") {
        const nodes = [].slice.call(mutation.addedNodes);
        if (nodes.length > 0) {
          callback(nodes);
        }
      }
    }
  }).observe(container, { attributes: true, childList: true, subtree: true });
};

/**
 *
 * @param container
 * @param callback
 * @returns {Promise<void>}
 */
export const observeAll = (container, callback) => {
  new MutationObserver((mutationsList, observer) => {
    callback(mutationsList);
  }).observe(container, { attributes: true, childList: true, subtree: true });
};

/**
 *
 * @param container
 * @param callback
 * @returns {Promise<void>}
 */
export const observeRemove = (container, callback) => {
  new MutationObserver((mutationsList, observer) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "childList") {
        const nodes = [].slice.call(mutation.removedNodes);
        if (nodes.length > 0) {
          callback(nodes);
        }
      }
    }
  }).observe(container, { attributes: true, childList: true, subtree: true });
};

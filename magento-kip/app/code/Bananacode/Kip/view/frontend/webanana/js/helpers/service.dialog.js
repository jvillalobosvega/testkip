/*
 *
 * Dialogs PopUps
 *
 * */

import {addClass, event, isVisible, removeClass, selectDoc} from "../utils/wonder";

import {dialogMarkup, modalRestrictedHTML} from "./service.markup";

const props = {
  showModalCount: 0,
  body: null,
  popup: null
};

/**
 *
 * @param e
 * @returns {Promise<void>}
 */
const checkOutSide = (e) => {
  e.preventDefault();
  const modal = selectDoc("#dialog-modal .modal");
  if (!modal.contains(e.target) && isVisible(modal)) {
    if (props.showModalCount > 0) {
      props.showModalCount = 0;
      props.body.removeChild(props.popup);
      document.removeEventListener("click", checkOutSide);
    } else {
      props.showModalCount++;
    }
  }
};

/**
 *
 * @param title
 * @param message
 * @param cta
 * @param ctaUrl
 * @param confirm
 * @returns {Promise<*|undefined>}
 */
export const dialog = (
  title,
  message,
  cta = null,
  ctaUrl = null,
  confirm = false
) => {
  try {
    /**
     * Add popup
     */
    props.popup = document.createElement("div");
    props.popup.id = "dialog-modal";
    props.popup.classList.add("modal-container");
    props.popup.innerHTML = dialogMarkup({
      title: title,
      message: message,
      cta: cta,
      ctaUrl: ctaUrl,
      yesno: confirm,
    });
    props.body = selectDoc("body");
    props.body.appendChild(props.popup);

    /**
     * Add events
     */
    event(document, "click", checkOutSide);
    const cancel = selectDoc("#dialog-modal #cancel-notify");
    const ctaButton = selectDoc("#dialog-modal #cta-notify");
    if (cancel && !confirm) {
      event(cancel, "click", function () {
        document.removeEventListener("click", checkOutSide);
        props.body.removeChild(props.popup);
        props.showModalCount = 0;
      });
    }

    /**
     * Yes/No
     */
    if (cancel && confirm && ctaButton) {
      return new Promise((resolve) => {
        event(ctaButton, "click", function (e) {
          e.preventDefault();
          resolve(true);
          document.removeEventListener("click", checkOutSide);
          props.body.removeChild(props.popup);
          props.showModalCount = 0;
        });
        event(cancel, "click", function () {
          resolve(false);
          document.removeEventListener("click", checkOutSide);
          props.body.removeChild(props.popup);
          props.showModalCount = 0;
        });
      });
    }
  } catch (e) {
    console.log("Error creating notification.", e);
  }
};

/**
 *
 * @param error
 * @param isSuccess
 */
export const displayModalError = (error, isSuccess = false) => {
    let modalRestricted = selectDoc('#error-modal');

    if (!modalRestricted) {
        modalRestricted = document.createElement('div');
        modalRestricted.id = 'error-modal';
        addClass(modalRestricted, 'modal-container')
        selectDoc('body').appendChild(modalRestricted);
    }

    modalRestricted.innerHTML = modalRestrictedHTML(error);
    isSuccess ? addClass(modalRestricted, 'success') : removeClass(modalRestricted, 'success');

    addClass(modalRestricted, 'active')
    setTimeout(function () {
        removeClass(modalRestricted, 'active')
    }, 4000);
}

export const showLoader = (hide = false) => {
  props.kipAjaxLoader = selectDoc('#kip-ajax-loader-modal')
  if(props.kipAjaxLoader) {
    hide ? props.kipAjaxLoader.classList.remove('active') : props.kipAjaxLoader.classList.add('active')
  }
}

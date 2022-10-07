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

export class Gif {
  /**
   * Header constructor
   */
  constructor() {
    this.init();
  }

  /**
   * Initialize class
   */
  init() {
    this.props();
    this.appendLoader();
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
  appendLoader() {
    let modal = document.createElement('div');
    modal.className = 'modal-container';
    modal.id = 'kip-ajax-loader-modal';
    modal.innerHTML = `<div class="loader-kip modal"></div>`;
    this.body.appendChild(modal);
  }
}

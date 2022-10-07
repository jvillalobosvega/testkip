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

export class Offers {
  /**
   * Header constructor
   */
  constructor(container) {
    if(selectDoc(container)) {
      this.init();
    }
  }

  /**
   * Initialize class
   */
  init() {
    this.props();
  }

  /**
   * Initialize class props
   */
  props() {
    this.body = selectDoc("body");
    this.html = selectDoc("html");
  }
}

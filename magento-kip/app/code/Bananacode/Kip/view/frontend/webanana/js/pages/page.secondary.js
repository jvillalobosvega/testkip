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
  hasClass, removeClassAll,
} from "../utils/wonder";
import {initSlider} from "../helpers/service.slider";

export class Secondary {
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
    this.initFAQ();
    this.initTerms();
  }

  /**
   * Initialize class props
   */
  props() {
    this.body = selectDoc("body");
    this.html = selectDoc("html");
    this.faqs = selectArr(".cms-faq .column.main li:nth-child(odd)");
    this.terms = selectArr("#kip-terms .terms-nav li");
    this.firstTerm = selectDoc("#kip-terms .terms-nav li");
    this.termsContainers = selectArr("#kip-terms .content .container");
  }

  initFAQ() {
    this.faqs.map(faq => {
      event(faq, 'click', function () {
        toggleClass(faq, 'open')
      })
    })
  }

  /**
   *
   */
  checkUrl() {
    let self = this,
        paramsC = 0;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach(function (param, key) {
      let opener = selectDoc(`#kip-terms .terms-nav [data-target="${key}"]`)
      opener ? opener.click() : (self.firstTerm ? self.firstTerm.click() : '');
      paramsC++;
    })

    if(paramsC < 1) {
      (self.firstTerm ? self.firstTerm.click() : '')
    }
  }

  initTerms() {
    let self = this;
    this.terms.map(term => {
      event(term, 'click', function () {
        removeClassAll(self.terms, 'active')
        toggleClass(term, 'active')
        let container = selectDoc(`#${term.getAttribute('data-target')}-content`);
        if(container) {
          removeClassAll(self.termsContainers, 'active')
          toggleClass(container, 'active')
        }
      })
    })

    this.checkUrl();
  }
}

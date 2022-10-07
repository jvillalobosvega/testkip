import { selectAll, selectDoc} from "../utils/wonder";

import Glide from "../lib/glide.min";

/**
 *
 * @param container
 * @param configs
 * @returns {*|void}
 */
export const initSlider = (container, configs) => {
  /**
   * * Append slider bullets
   */
  const buttonsContainer = selectDoc(container + " #glide-sprite");
  if (buttonsContainer) {
    buttonsContainer.innerHTML = "";
    const slidesContainer = selectDoc(container + " .glide__slides");
    if (slidesContainer) {
      if (slidesContainer.innerHTML.trim().length > 0) {
        const slidersCount = selectAll(container + " .glide__slide").length;
        for (let i = 0; i < slidersCount; i++) {
          buttonsContainer.innerHTML += `<button data-glide-dir="=${i}"></button>`;
        }


        buttonsContainer.parentNode.innerHTML += `<div data-glide-el="controls" class="slider-controls">
                                                      <button class="left" data-glide-dir="<">
                                                          <span class="arrow-icon"></span>
                                                      </button>
                                                      <button class="right" data-glide-dir=">">
                                                          <span class="arrow-icon"></span>
                                                      </button>
                                                  </div>`;

        return new Glide(container, configs).mount();
      }
    }
  } else {
    return new Glide(container, configs).mount();
  }
};

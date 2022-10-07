import {
    ajax,
    selectArr,
    selectDoc,
    toggleClass,
    event,
    isVisible,
    observeAdd,
    addClass,
    hasClass,
    removeClass,
    removeClassAll, observeAll, hasClassSVG
} from "../utils/wonder";
import {loginPopup, wishlistPopup} from "../helpers/service.markup";
import {showLoader} from '../helpers/service.dialog'
import {inputEmpty} from "../helpers/service.validator";
import {controlAtc, controlQty, extractPDPOptions} from "../helpers/service.cart";
import {initScroll, lazyLoadIcons} from "../helpers/service.scroll";
import {initMenuItems, matchMenuItemsWUrl} from "../helpers/service.menus";
import {Forms} from "./component.forms";

export class Products {
    /**
     * Forms constructor
     */
    constructor() {
        this.init();
    }

    /**
     * Initialize class
     */
    init() {
        let self = this;

        this.props();

        ajax('GET',
            `/rest/V1/kip/customer-data`
        ).then(function (response) {
            let data = JSON.parse(response),
                customer = JSON.parse(data.response);
            if (customer.data) {
                self.customerId = customer.data.id
                self.formkey = customer.data.formkey;
            }

            self.initWishlist()
            self.initQtyControllers()
            self.initPCP()
            self.initRecipes();
            self.mageplazaAjaxLayeredNav();
            self.initPDPrequiredOptional();
            self.initAlgolia();
            self.initReorder();
        });
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.authToken = selectDoc("input[name='customer_token']");

        this.maincontent = selectDoc("#maincontent");
        this.sidebarMain = selectDoc(".sidebar.sidebar-main");
        this.openCategories = selectDoc("#open-categories");
        this.menuCategories = selectDoc("#menu-categories");

        this.shopByContainer = selectDoc("#layered-filter-block");
        this.menuShopByMageplaza = selectDoc("#layered-filter-block-mageplaza");
        this.menuShopBy = selectDoc("#narrow-by-list");
        this.shopBy = selectDoc("#layered-filter-block .block-title.filter-title");

        this.listener = null;
        this.navCount = 0;

        this.currentWishId = null;
        this.currentWishName = null;

        this.recipePdp = selectDoc('body.recipe-product-page');
        this.recipePdpTabs = selectArr('#recipe-ingredients .tabs button');
        this.recipePdpContainers = selectArr('#recipe-ingredients .tab');
        this.recipePdpIngredients = selectDoc('#recipe-ingredients');
        this.recipePdpButtons = [];
        this.recipePdpScroller = selectDoc('#recipe-ingredients .scroller');
        this.recipePdpInstructions = selectDoc('#recipe-instructions');
        this.recipeIngredientsProducts = selectArr('#recipe-ingredients tr.sub-product');
        this.orderRecipe = selectDoc('#order-recipe');

        this.productContainer = selectDoc('#layer-product-list');
        this.layerContainer = selectDoc('.layered-filter-block');

        this.customerId = 0;
        this.formkey = null;
        this.wishlists = null;
    }

    /**
     *
     */
    atcsShowScroll() {
        selectArr('button.tocart').map(atc => {
            if (atc.getBoundingClientRect().top <= window.innerHeight && atc.getBoundingClientRect().top > 0) {
                atc.parentNode.classList.add('open')
            } else {
                atc.parentNode.classList.remove('open')
            }
        })
    }

    /**
     *
     * @param atcs
     */
    initScrollHoverQty() {
        let self = this;
        window.removeEventListener('scroll', self.atcsShowScroll);
        event(window, 'scroll', self.atcsShowScroll)
    }

    /**
     * Check products in wishlist
     * @param hearts
     */
    isProductWished(hearts) {
        let self = this;

        const processWishes = () => {
            self.wishlists.map(list => {
                if (list.products) {
                    hearts.map(heart => {
                        addClass(heart, 'wischecked');
                        list.products.map(wished => {
                            let lists = [];
                            if (heart.getAttribute('data-product')) {
                                if (heart.getAttribute('data-product') === wished) {
                                    lists.push(list.value);
                                }
                            }
                            if (lists.length > 0) {
                                heart.setAttribute('data-lists', lists.join(','))
                                addClass(heart, 'wished');
                            }
                        })
                    })
                }
            });
        }

        if (hearts.length > 0) {
            if (!self.wishlists) {
                ajax('GET',
                    `/rest/V1/kip/customer-wishes`
                ).then(function (response) {
                    let data = (JSON.parse(response));
                    if (data.status === 200) {
                        self.wishlists = JSON.parse(data.response).output;
                        processWishes();
                    } else {
                        self.wishlists = [];
                    }
                })
            } else {
                processWishes();
            }
        }
    }

    /**
     * Add specific product to wishlist
     * @param wishlistPopUp
     * @param list
     */
    addToWishList(wishlistPopUp, list) {
        let self = this;
        ajax('GET',
            `/rest/V1/kip/customer/add-wish/${self.currentWishId}/${list}`
        ).then(function (response) {
            showLoader(true);
            let data = (JSON.parse(response));
            if (data.status === 200) {
                self.initListOfLists(wishlistPopUp, data.response.trim().split(','))
            }
        })
    }

    /**
     *
     * @param allLists
     * @param currentLists
     */
    updateListsStatus(allLists, currentLists) {
        if (currentLists) {
            allLists.map(allList => {
                let found = false;
                currentLists.map(currentList => {
                    if (currentList === allList.getAttribute('data-list')) {
                        addClass(allList, 'added');
                        found = true;
                    }
                })
                if (!found) {
                    removeClass(allList, 'added');
                }
            })
        } else {
            allLists.map(allList => {
                removeClass(allList, 'added');
            })
        }
    }

    /**
     *
     * @param wishlistPopUp
     * @param current
     */
    initListOfLists(wishlistPopUp, current = null) {
        let self = this;

        ajax('GET',
            `/rest/V1/kip/customer-wishes`
        ).then(function (response) {
            let data = (JSON.parse(response));
            if (data.status === 200) {
                let lists = JSON.parse(data.response).output,
                    container = selectDoc('#wish-lists', wishlistPopUp);
                if (container) {
                    container.innerHTML = '';
                    let ul = document.createElement('ul');
                    lists.map(list => {
                        if (list) {
                            if (list.value !== 'recipesgroupnotuse') {
                                let li = document.createElement('li');
                                li.className = 'bold-16';
                                li.setAttribute('data-list', list.value);
                                li.innerHTML = `<span>${list.value} (${list.count})</span>`;
                                ul.appendChild(li);
                                event(li, 'click', function () {
                                    showLoader();

                                    //Add product to wishlist
                                    self.addToWishList(wishlistPopUp, li.getAttribute('data-list'));
                                })
                            }
                        }
                    });
                    container.appendChild(ul);
                    if (current) {
                        self.updateListsStatus(selectArr('#wish-lists li', wishlistPopUp), current);
                        let heart = selectDoc(`.towishlist[data-product="${self.currentWishId}"]`);
                        if (heart) {
                            heart.setAttribute('data-lists', current.join(','));
                            if (!inputEmpty(current.join(','))) {
                                addClass(heart, 'wished')
                            } else {
                                removeClass(heart, 'wished')
                            }
                        }
                    }
                }
            }
        })
    }

    /**
     * Init wishlist popup
     * @param hearts
     */
    addProductsWish(hearts) {
        let self = this;
        if (hearts.length > 0) {
            let wishlistPopUp = selectDoc('#wishlist-popup');

            if (!wishlistPopUp) {
                wishlistPopUp = document.createElement('div');
                wishlistPopUp.className = "modal-container";
                wishlistPopUp.id = "wishlist-popup";
                wishlistPopUp.innerHTML = wishlistPopup();
                self.body.appendChild(wishlistPopUp);

                if (self.customerId > 0) {
                    let addWishlist = selectDoc('#create-wish-lists button', wishlistPopUp),
                        scold = selectDoc('.sub-content.old', wishlistPopUp),
                        scnew = selectDoc('.sub-content.new', wishlistPopUp),
                        scnewCancel = selectDoc('button.secondary-btn', scnew),
                        scnewAdd = selectDoc('button.primary-btn', scnew),
                        scNewInput = selectDoc('form.add input', scnew);

                    //Search wishlist
                    event(selectDoc('#search-wishes', wishlistPopUp), 'keyup', function (e) {
                        let allLists = selectArr('#wish-lists li', wishlistPopUp);
                        if (e.target.value) {
                            allLists.map(allList => {
                                if (allList.getAttribute('data-list').toLowerCase().includes(e.target.value.toLowerCase())) {
                                    removeClass(allList, 'hide')
                                } else {
                                    addClass(allList, 'hide')
                                }
                            })
                        } else {
                            allLists.map(allList => {
                                removeClass(allList, 'hide')
                            })
                        }
                    })

                    //Show new wishlist form
                    event(addWishlist, 'click', function () {
                        removeClass(scold, 'active')
                        addClass(scnew, 'active')
                    })

                    //Cancel new wishlist form
                    event(scnewCancel, 'click', function () {
                        removeClass(scnew, 'active')
                        addClass(scold, 'active')
                    })

                    //Create new wishlist
                    event(scnewAdd, 'click', function () {
                        showLoader()
                        ajax('GET',
                            `/rest/V1/kip/customer/add-wish/${self.currentWishId}/${scNewInput.value}`
                        ).then(function (response) {
                            showLoader(true);
                            scNewInput.value = '';
                            let data = (JSON.parse(response));
                            if (data.status === 200) {
                                self.initListOfLists(wishlistPopUp, data.response.trim().split(','));
                                removeClass(scnew, 'active')
                                addClass(scold, 'active')
                            }
                        })
                    })
                }

                /**
                 * Close wishlist modal
                 */
                let closeModal = selectDoc('.close-modal', wishlistPopUp)
                if (closeModal) {
                    event(closeModal, 'click', function () {
                        wishlistPopUp.classList.remove('active')
                        self.html.classList.remove('noscroll')
                    })
                }
            }

            if (wishlistPopUp) {
                /**
                 * Render modal
                 */
                let mode = null;

                if (self.customerId > 0) {
                    mode = selectDoc('#wishlist-popup .logged.mode');

                    //Render wishlists
                    self.initListOfLists(wishlistPopUp);
                } else {
                    mode = selectDoc('#wishlist-popup .logout.mode');
                }

                if (mode) {
                    addClass(mode, 'active')
                }
            }

            /**
             * On click hearts
             */
            hearts.map(heart => {
                addClass(heart, 'evented');
                event(heart, 'click', function (e) {
                    e.preventDefault();
                    if (wishlistPopUp) {
                        self.currentWishId = heart.getAttribute('data-product');
                        self.currentWishName = heart.getAttribute('data-name');

                        if (hasClass(heart, 'grouped')) {
                            let mode = selectDoc('#wishlist-popup .logout.mode.active');
                            if (!mode) {
                                showLoader();
                                self.addToWishList(wishlistPopUp, 'recipesgroupnotuse')
                            } else {
                                wishlistPopUp.classList.add('active');
                                self.html.classList.add('noscroll');
                            }
                        } else {
                            if (heart.getAttribute('data-lists')) {
                                self.updateListsStatus(
                                    selectArr('#wish-lists li', wishlistPopUp),
                                    heart.getAttribute('data-lists').split(',')
                                )
                            } else {
                                self.updateListsStatus(
                                    selectArr('#wish-lists li', wishlistPopUp),
                                    null
                                );
                            }

                            selectDoc('span.current-product', wishlistPopUp).innerHTML = self.currentWishName;
                            wishlistPopUp.classList.add('active');
                            self.html.classList.add('noscroll');
                        }
                    }
                })
            })
        }
    }

    /**
     * Delete wishlist events
     * @param trashes
     */
    deleteWishlist(trashes) {
        trashes.map(trash => {
            addClass(trash, 'evented')
            event(trash, 'click', function (e) {
                showLoader()
                ajax('GET',
                    `/rest/V1/kip/customer/delete-wishlist/${trash.getAttribute('data-list')}`
                ).then(function (response) {
                    showLoader(true);
                    let data = (JSON.parse(response));
                    if (data.status === 200) {
                        window.location.reload()
                    }
                })

            })
        })
    }

    ajaxLogin(form, action) {
        showLoader();
        let f = new FormData(form);
        if (f) {
            let data = '',
                i = 1,
                size = 0;

            for (let prop of f.entries()) {
                size++;
            }

            for (let prop of f.entries()) {
                data += `${prop[0]}=`;
                data += `${prop[1]}`;
                if (i < size) {
                    data += '&'
                }
                i++;
            }

            action.style.opacity = '0.5'
            ajax('POST',
                '/customer/account/loginPost?' + encodeURI(data),
                encodeURI(data),
                'application/x-www-form-urlencoded'
            ).then(function (response) {
                window.location.reload();
            })
        }
    }

    /**
     *
     */
    logInModal() {
        let self = this,
            loginPopUp = selectDoc('#login-popup');
        if (!loginPopUp) {
            loginPopUp = document.createElement('div');
            loginPopUp.className = "modal-container";
            loginPopUp.id = "login-popup";
            loginPopUp.innerHTML = loginPopup(self.formkey);
            self.body.appendChild(loginPopUp);

            /**
             * Login
             */
            let login = selectDoc('#kip-ajax-login', loginPopUp)
            if (login) {
                event(login, 'click', function (e) {
                    e.preventDefault();
                    self.ajaxLogin(selectDoc('form', loginPopUp), login);
                })
            }

            /**
             * Close
             */
            let closeModal = selectDoc('.close-modal', loginPopUp)
            if (closeModal) {
                event(closeModal, 'click', function () {
                    loginPopUp.classList.remove('active')
                    self.html.classList.remove('noscroll')
                })
            }

            new Forms();
        }

        if (loginPopUp) {
            /**
             * Render modal
             */
            if (self.customerId <= 0) {
                addClass(loginPopUp, 'active');
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * +/- controllers pdp
     * @param qty
     */
    addQtyControllersEventsProduct(qty) {
        let self = this;
        if (qty) {
            selectArr('span', qty.parentNode).map(control => {
                controlQty(control);
            })

            let atcForm = selectDoc('#product_addtocart_form');
            if (atcForm) {
                let atc = selectDoc('#product-addtocart-button', atcForm),
                    product = selectDoc('[name="product"]', atcForm);

                addClass(atc, 'evented');

                event(atc, 'click', function (e) {
                    let pdp = extractPDPOptions();
                    if (pdp) {
                        e.preventDefault();
                        if (self.logInModal()) {
                            addClass(atc, 'animate');
                            setTimeout(function () {
                                removeClass(atc, 'animate')
                            }, 1000);
                            controlAtc(atc, product, null, pdp);
                        }
                    }
                });
            }
        }
    }

    /**
     * +/- controllers pcp
     * @param atcs
     */
    addQtyControllersEvents(atcs) {
        if (atcs.length > 0) {
            let self = this;
            atcs.map(atc => {
                if(atc.id !== 'product-addtocart-button') {

                    addClass(atc, 'evented');

                    let product = selectDoc('[name="product"]', atc.parentNode);
                    if (product) {
                        selectArr('.qty-controls span', atc.parentNode).map(control => {
                            controlQty(control);
                        })

                        event(atc, 'click', function (e) {
                            e.preventDefault();
                            if (self.logInModal()) {
                                addClass(atc, 'animate');
                                setTimeout(function () {
                                    removeClass(atc, 'animate')
                                }, 1000);
                                controlAtc(atc, product);
                            }
                        });
                    }
                }
            });

            this.initScrollHoverQty(atcs);
        }
    }

    /**
     * Init wishlist functionality on PDP & PCP
     */
    initWishlist() {
        this.isProductWished(selectArr('.action.towishlist:not(.wischecked)'));

        this.addProductsWish(selectArr('.action.towishlist:not(.evented)'));

        this.deleteWishlist(selectArr('button.delete-wishlist:not(.evented)'));
    }

    /**
     * Init qty +/- controllers functionality on PDP & PCP
     */
    initQtyControllers() {
        //PDP
        this.addQtyControllersEventsProduct(selectDoc('.box-tocart input#qty'));

        //PCP
        this.addQtyControllersEvents(selectArr('button.tocart:not(.evented)'));
    }

    /**
     *
     * @param e
     * @param self
     * @param menu
     */
    checkOutSide(e, self, menu) {
        if ((!menu.contains(e.target) && isVisible(menu))) {
            if (self.navCount > 0) {
                self.closeNav();
            } else {
                self.navCount++;
            }
        } else {
            self.navCount++;
        }
    }

    /**
     *
     */
    closeNav() {
        this.navCount = 0;
        removeClass(this.html, 'noscroll')
        removeClass(this.menuCategories, 'open')
        removeClass(this.body, 'filter-active')
        removeClass(this.shopByContainer, 'active')
        removeClass(this.menuShopBy, 'active')
        removeClass(this.menuShopByMageplaza, 'active')
        document.removeEventListener("click", this.listener);
    }

    /**
     *
     */
    initPCP() {
        let self = this;
        if (this.sidebarMain && this.maincontent && !hasClass(this.sidebarMain, 'evented')) {
            addClass(this.sidebarMain, 'evented');

            initMenuItems('#menu-categories')

            matchMenuItemsWUrl('#menu-categories')

            //Scroll animation
            let lastScrollTop = 0,
                up = true,
                locked = false;

            event(window, 'scroll', function () {
                let st = window.pageYOffset || document.documentElement.scrollTop; // Credits: "https://github.com/qeremy/so/blob/master/so.dom.js#L426"
                up = st <= lastScrollTop;
                lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling

                let header = selectDoc('header.page-header').getBoundingClientRect(),
                    bottom = self.maincontent.getBoundingClientRect().bottom,
                    bottom2 = self.sidebarMain.getBoundingClientRect().bottom,
                    top2 = self.sidebarMain.getBoundingClientRect().top;

                if (locked) {
                    if ((bottom2 >= window.innerHeight || top2 >= header.bottom) && up) {
                        locked = false;
                        self.sidebarMain.classList.remove('locked')
                    }
                } else {
                    if (bottom2 >= bottom) {
                        if (!up) {
                            locked = true;
                            self.sidebarMain.classList.add('locked')
                        }
                    }
                }
            });

            const lazyLoadIconsScroll = () => {
                let leftIcons = lazyLoadIcons();
                if (leftIcons === 0) {
                    this.sidebarMain.removeEventListener('scroll', lazyLoadIconsScroll)
                }
            }
            event(this.sidebarMain, 'scroll', lazyLoadIconsScroll);
        }

        //Custom categories menu
        this.openCategories = selectDoc("#open-categories");
        this.menuCategories = selectDoc("#menu-categories");
        if (this.openCategories && !hasClass(this.openCategories, 'evented')) {
            addClass(self.openCategories, 'evented')
            event(this.openCategories, 'click', function () {
                toggleClass(self.menuCategories, 'open')
                self.html.classList.add('noscroll')
                event(document, 'click', self.listener = function (e) {
                    self.checkOutSide(e, self, self.menuCategories)
                });

                lazyLoadIcons();
            })
        }

        //Shop by filters menu
        this.shopByContainer = selectDoc("#layered-filter-block");
        this.menuShopByMageplaza = selectDoc("#layered-filter-block-mageplaza");
        this.menuShopBy = selectDoc("#narrow-by-list");
        this.shopBy = selectDoc("#layered-filter-block .block-title.filter-title");
        if (this.shopBy) {
            if (this.menuShopBy) {
                removeClass(this.body, 'no-filters-available')
                let overlay = document.createElement('div');
                addClass(overlay, 'shopby-overlay');
                this.shopBy.parentNode.appendChild(overlay);

                event(this.shopBy, 'click', function () {
                    if (self.navCount === 0) {
                        event(document, 'click', self.listener = function (e) {
                            self.checkOutSide(e, self, self.menuShopBy)
                        });

                        self.html.classList.add('noscroll');
                        addClass(self.body, 'filter-active');
                        addClass(self.shopByContainer, 'active');
                        addClass(self.menuShopBy, 'active');

                        lazyLoadIcons();
                    }
                })

                let filters = selectArr('.filter-options-content', this.menuShopBy);
                filters.map(filter => {
                    observeAll(filter, function (nodes) {
                        filter.style.display = 'block';
                    })
                });
            } else {
                addClass(this.body, 'no-filters-available')
            }
        }

        this.closeNav();
    }

    /**
     *
     */
    initRecipes() {
        let self = this;
        if (this.recipePdp) {
            this.recipePdpTabs.map(tab => {
                event(tab, 'click', function (e) {
                    e.preventDefault();
                    let container = selectDoc(`#${tab.getAttribute('data-container')}`)
                    if (container) {
                        removeClassAll(self.recipePdpTabs, 'active')
                        removeClassAll(self.recipePdpContainers, 'active')
                        toggleClass(container, 'active')
                        toggleClass(tab, 'active')
                    }
                });
                addClass(tab, 'ready');
            })

            let lastScrollTop = 0,
                up = true,
                locked = false,
                scrollerContent = selectDoc('#buy-container', self.recipePdpScroller)

            if (scrollerContent && window.innerWidth > 768) {
                self.recipePdpInstructions.style.minHeight = `${scrollerContent.getBoundingClientRect().height + 35 + 40 + 60}px`
            }

            event(window, 'scroll', function () {
                let st = window.pageYOffset || document.documentElement.scrollTop; // Credits: "https://github.com/qeremy/so/blob/master/so.dom.js#L426"
                up = st <= lastScrollTop;
                lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling

                let header = selectDoc('header.page-header').getBoundingClientRect(),
                    top = self.recipePdpIngredients.getBoundingClientRect().top - header.height,
                    bottom = self.recipePdpIngredients.getBoundingClientRect().bottom - header.height,
                    scroller = self.recipePdpScroller.getBoundingClientRect();

                if (top < 0) {
                    if ((bottom > (scroller.bottom - header.height))) {
                        if (!locked) {
                            self.recipePdpScroller.style.bottom = 'initial';
                            self.recipePdpScroller.style.top = (Math.abs(top) + 10) + 'px';
                        } else {
                            if (up) {
                                if ((scroller.bottom >= window.innerHeight) || scroller.bottom === 0) {
                                    locked = false;
                                    self.recipePdpScroller.style.bottom = 'initial';
                                    self.recipePdpScroller.style.top = (Math.abs(top) + 10) + 'px';
                                }
                            }
                        }
                    } else {
                        if (!up) {
                            locked = true;
                            self.recipePdpScroller.style.bottom = '0';
                            self.recipePdpScroller.style.top = 'initial';
                        }
                    }
                } else {
                    self.recipePdpScroller.style.bottom = 'initial';
                    self.recipePdpScroller.style.top = '0';
                }
            })

            this.recipeIngredientsProducts.map(ingredient => {
                selectArr('.qty span', ingredient).map(control => {
                    controlQty(control);
                });

                let form = selectDoc('.ingredient-form', ingredient);
                if (form) {
                    let button = selectDoc('button', form);
                    if (button) {
                        self.recipePdpButtons.push(button)
                        event(button, 'click', function (e) {
                            e.preventDefault();
                            addClass(button, 'animate');
                            setTimeout(function () {
                                removeClass(button, 'animate')
                            }, 1000);

                            let product = selectDoc('input[name="product"]', form),
                                papa = selectDoc('input[name="papa"]', form),
                                productIngredient = selectDoc(`.ingredient-id-${product.value}`),
                                presentacion = selectDoc(`#presentacion-prod-${product.value}`);

                            if (productIngredient) {
                                if (self.logInModal()) {
                                    controlAtc(button, product, {
                                        "q": parseFloat(selectDoc(`input.qty`, productIngredient).value),
                                        "p": parseFloat(selectDoc(`#product-price-${product.value}`, productIngredient).getAttribute('data-price-amount')),
                                        "i": selectDoc(`.img img`, productIngredient).src,
                                        "n": selectDoc(`.product-item-name`, productIngredient).innerText,
                                        "m": parseFloat(selectDoc(`input.qty`, productIngredient).getAttribute('data-min')),
                                        "d": presentacion ? presentacion.innerText : '',
                                        "c": papa.value ? JSON.parse(papa.value) : '',
                                    });
                                }
                            }
                        });
                    }
                }
            });

            event(this.orderRecipe, 'click', function (e) {
                this.disabled = true;
                addClass(this, 'animate');
                e.preventDefault();
                let wait = 0,
                    added = 0;
                self.recipePdpButtons.map(ingredient => {
                    setTimeout(function () {
                        ingredient.click();
                        added++;
                        if (added >= self.recipePdpButtons.length) {
                            self.orderRecipe.disabled = false;
                            removeClass(self.orderRecipe, 'animate');
                        }
                    }, wait);
                    wait += 2500;
                });
            })
        }
    }

    /**
     *
     */
    mageplazaAjaxLayeredNav() {
        let self = this;
        if (this.productContainer) {
            observeAdd(this.productContainer, function (nodes) {
                nodes.map(node => {
                    if (node.className) {
                        if (
                            node.className.includes('products-grid') ||
                            node.className.includes('results') ||
                            node.className.includes('empty')
                        ) {
                            self.initPCP();
                            self.initQtyControllers();
                            self.initWishlist();
                            initScroll();
                            scroll({
                                top: 0,
                                behavior: "smooth"
                            });
                        }
                    }
                    if (node.id) {
                        if (
                            node.id.includes('layer-product-list')
                        ) {
                            self.initPCP();
                            self.initQtyControllers();
                            self.initWishlist();
                            initScroll();
                            scroll({
                                top: 0,
                                behavior: "smooth"
                            });
                        }
                    }
                })
            })
        }
    }

    /**
     *
     */
    initPDPrequiredOptional() {
        selectArr('#product_addtocart_form select.product-custom-option').map(select => {
            select.selectedIndex = 1;
        });
    }

    /**
     *
     */
    initAlgolia() {
        let self = this;
        if (selectDoc('.catalogsearch-result-index')) {
            let algoliaInt = setInterval(function () {
                let algolia = selectDoc('#instant-search-results-container');
                if (algolia) {
                    initScroll();
                    self.initPCP();
                    self.initQtyControllers();
                    self.initWishlist();
                    observeAdd(algolia, function () {
                        self.initPCP();
                        self.initQtyControllers();
                        self.initWishlist();
                        initScroll();
                    });
                    clearInterval(algoliaInt);
                }
            }, 1);
        }

        let algoliaAutoComplete = selectDoc('#algolia-autocomplete-container');
        if (algoliaAutoComplete) {
            observeAdd(algoliaAutoComplete, function () {
                //Stop algolia redirect on hit click
                selectArr('.aa-suggestions .aa-suggestion .algoliasearch-autocomplete-hit:not(.evented)').map(suggestion => {
                    addClass(suggestion, 'evented');
                    event(suggestion, 'click', function (e) {
                        if (hasClass(e.target, 'atc') || hasClassSVG(e.target.className, 'atc')) {
                            //selectDoc('#algolia-autocomplete-tt input#search').blur();
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                        }
                    })
                });

                //Strange mobile click behavior
                if (window.innerWidth <= 768) {
                    selectArr('.aa-suggestions .aa-suggestion:not(.evented)').map(s => {
                        s.setAttribute('class', 'aa-suggestion-kip');
                        addClass(s, 'evented');
                    });
                }

                self.initQtyControllers();
                setTimeout(function () {
                    initScroll();
                }, 300);
            });
        }
    }

    /**
     *
     */
    initReorder() {
        var delay = 1;
        let repeatOrder = selectDoc('#repeat-order-kip');
        if (repeatOrder) {
            event(repeatOrder, 'click', function (e) {
                e.preventDefault();
                showLoader();
                // setTimeout(() => {  console.log("nx"); }, 4000+delay);
                delay++;
                selectArr('.atc-reorder').map(reorderItem => {
                    controlAtc(repeatOrder, reorderItem, {
                        "q": parseFloat(reorderItem.getAttribute('data-qty')),
                        "p": parseFloat(reorderItem.getAttribute('data-price')),
                        "i": '',
                        "n": reorderItem.getAttribute('data-name'),
                        "m": parseFloat(reorderItem.getAttribute('data-min')),
                        "d": reorderItem.getAttribute('data-presentacion'),
                        "c": reorderItem.getAttribute('data-papa') ? JSON.parse(reorderItem.getAttribute('data-papa')) : '',
                    },false,true);
                    // console.log("REPEAT ORDER",reorderItem.getAttribute('data-name'));
                    
                });
                setTimeout(function () {
                    showLoader(true);
                    // window.location.href = '/checkout/cart'
                }, 2900+delay)
            })
        }
    }
}

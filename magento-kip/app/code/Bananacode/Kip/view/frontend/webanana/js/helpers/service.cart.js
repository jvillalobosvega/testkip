import {
    addClass, ajax,
    event, hasClass,
    selectArr, selectDoc,
    removeClass
} from "../utils/wonder";

import {
    formatDollar,
    kipCartHtml,
    kipCartItemHtml,
    kipCartItemsHtml, kipImpulseHtml,
    minicartSummaryItems,
    orderSummaryHtml
} from "./service.markup";

import {inputEmpty} from "./service.validator";

import {displayModalError, showLoader} from "./service.dialog";
import {initVaultSelector} from "../components/component.kipckout";

/**
 *
 * @param atc
 * @param product
 * @param params
 * @param pdp
 */
export const controlAtc = (atc, product, params = null, pdp = false, isReorder = false) => {
    console.log("R",isReorder);
    let productId = product.value;

    if (!params) {
        let qtyInput = selectDoc('.qty-controls input', atc.parentNode),
            priceInput = selectDoc(`#product-price-${product.value} span`);

        if (!qtyInput) {
            qtyInput = selectDoc('input#qty', product.parentNode);
        }

        if (!priceInput) {
            priceInput = selectDoc(`#old-price-${product.value}-final_price span`);
        }
        if (!priceInput) {
            priceInput = selectDoc(`#algoliasearch-autocomplete-price-${product.value} span.after_special`);
        }

        if (qtyInput) {
            let qtyAdded = parseFloat(qtyInput.value),
                priceAdded = priceInput ? parseFloat(priceInput.innerText.replaceAll('$', '')) : 0,
                image = (selectDoc(`#product-item-info_${product.value} .product-item-photo img`)),
                name = (image ? image.getAttribute('alt') : '');

            if (pdp) {
                name = (selectDoc('.page-title-wrapper.product span.base').innerText);
                image = (selectDoc('img.fotorama__img') ?? selectDoc('img.gallery-placeholder__image'));
                productId = pdp.selected_configurable_option ?? productId;
            }

            params = {
                "q": qtyAdded,
                "p": priceAdded,
                "c": pdp,
                "i": image ? (image.getAttribute('data-src') ?? image.getAttribute('src')) : '',
                "n": name,
                "m": qtyInput.getAttribute('data-min'),
                "d": "", //presentacion ? presentacion.innerText : ''
            };
        }
    }

    if (params) {
        let preCart = kipCartSession();
        kipCartSession(productId, params)
        renderCart(productId, 'add', false, JSON.parse(preCart),null,isReorder);
    }
}

/**
 *
 * @param control
 */
export const controlQty = (control) => {
    if (control) {
        if (!hasClass(control, 'kiptrolled') && (hasClass(control, 'minus') || hasClass(control, 'add'))) {
            control.classList.add('kiptrolled');
            event(control, 'click', function (event) {

                /*************/
                /*****QXD*****/
                /*************/
                /******************************************************************/
                /* Adding class to block the controls and avoid multiple requests */
                /******************************************************************/

                console.log("is button inactive?");
                console.log(!hasClass(control, 'inactive_button'));
                if(!hasClass(control, 'inactive_button')){
                    addClass(this, 'inactive_button');
                    let input = selectDoc('input', this.parentNode);
                    if (input) {
                        let value = parseFloat(input.value),
                            min = parseFloat(input.getAttribute('data-min'));

                        value = isNaN(value) ? 0 : value;
                        min = isNaN(min) ? 1 : min;

                        if (this.className.includes('minus')) {
                            value -= min;
                        } else {
                            value += min;
                        }

                        if (value >= min) {
                            value = Math.round(value * 10) / 10;
                            input.value = value;
                            input.setAttribute('value', value.toString().replace(/\.0+$/, ''));
                            input.dispatchEvent(new Event('change', {'bubbles': true}));
                            controlUpdate(input, value, this);
                        }else{
                            removeClass(this, 'inactive_button');
                        }
                    }
                }
            });
        }
    }
}

/**
 *
 * @param del
 */
export const controlDelete = (del) => {
    if (del) {
        if (!hasClass(del, 'kiptrolled')) {
            addClass(del, 'kiptrolled');
            event(del, 'click', function (e) {
                e.preventDefault();

                let kipCartItem = selectDoc(`ol#mini-cart li.kiptem-id-${del.getAttribute('data-cart-item')}`, selectDoc('#kip-content-wrapper'))
                if (kipCartItem) {
                    kipCartItem.className = 'product-item removed';
                    setTimeout(function () {
                        addClass(kipCartItem, 'killed')
                    }, 500);
                }

                let magCartItem = selectDoc(`#shopping-cart-table .kiptem-id-${del.getAttribute('data-cart-item')}`)
                if (magCartItem) {
                    magCartItem.className = 'cart item removed';
                    setTimeout(function () {
                        addClass(magCartItem, 'killed')
                    }, 500);
                }

                kipCartSession(del.getAttribute('data-cart-item'), 0, true);
                renderCart(del.getAttribute('data-cart-item'), 'delete');
            })
        }
    }
}

/**
 *
 * @param input
 * @param value
 */
export const controlUpdate = (input, value, element) => {
    if (input.getAttribute('data-cart-item')) {
        kipCartSession(input.getAttribute('data-cart-item'), value, true)
        renderCart(input.getAttribute('data-cart-item'), 'update', null, null, null, false, element);
    }else{
        removeClass(element, 'inactive_button');
    }
}

/**
 *
 * @param itemId
 * @param action
 * @param totals
 * @param preCart
 * @param lsoptions
 */
export const renderCart = (
    itemId = null,
    action = null,
    totals = null,
    preCart = null,
    lsoptions = null,
    isReorder = false,
    clearElement = null
) => {
    let cart = JSON.parse(kipCartSession()),
        magCartEmpty = selectDoc('#minicart-empty-custom-content'),
        customItemsContainer = selectDoc('#minicart-custom-items'),
        kipCart = selectDoc('#kip-content-wrapper');


    if (customItemsContainer) {
        if (Object.keys(cart).length > 0) {
            if (!kipCart) {
                kipCart = document.createElement('div');
                kipCart.id = 'kip-content-wrapper';
                customItemsContainer.appendChild(kipCart);
                kipCart.innerHTML = kipCartHtml(cart);
            } else {
                if (!itemId) {
                    kipCartItemsHtml(cart, kipCart)
                } else {
                    let kipCartItem = selectDoc(`ol#mini-cart li.kiptem-id-${itemId}`, kipCart),
                        kipCartItemPrice = kipCartItem ? selectDoc(`.product-item-pricing .minicart-price .price`, kipCartItem) : null,
                        kipCartItemQty = kipCartItem ? selectDoc(`#cart-item-${itemId}-qty`, kipCartItem) : null;

                    switch (action) {
                        case 'add':
                            //Add new row
                            if (!preCart[itemId]) {
                                let kipCartItems = selectDoc('ol#mini-cart', kipCart)
                                kipCartItems ? kipCartItems.appendChild(kipCartItemHtml(cart, itemId)) : '';
                            } else {
                                //Update row qty & price
                                if (kipCartItemPrice) {
                                    kipCartItemPrice.innerText = formatDollar(cart[itemId].p * cart[itemId].q)
                                }
                                if (kipCartItemQty) {
                                    kipCartItemQty.value = cart[itemId].q;
                                }
                            }
                            break;
                        case 'delete':
                            kipCartItem ? kipCartItem.parentNode.removeChild(kipCartItem) : '';
                            break;
                        case 'update':
                            //Qty updated by client just change price
                            if (kipCartItemPrice) {
                                kipCartItemPrice.innerText = formatDollar(cart[itemId].p * cart[itemId].q)
                            }
                            let magCartItem = selectDoc(`#shopping-cart-table .kiptem-id-${itemId}`)
                            if (magCartItem) {
                                let cartItemPrice = selectDoc('.subtotal .cart-price .price', magCartItem);
                                if (cartItemPrice) {
                                    cartItemPrice.innerText = formatDollar(cart[itemId].p * cart[itemId].q)
                                }
                            }
                            break;
                    }
                }
            }

            //Keep qty controllers bind for minicart and cart page.
            selectArr('.minicart-items-wrapper .details-qty.qty > span').map(control => {
                controlQty(control);
            });
            selectArr('#shopping-cart-table .qty-controls > span').map(control => {
                controlQty(control);
            });

            //Delete actions
            selectArr('.minicart-items-wrapper .action.kip-delete').map(del => {
                controlDelete(del);
            });
            selectArr('#shopping-cart-table .action.kip-delete').map(del => {
                controlDelete(del);
            });
        }

        //Update minicart quantities
        let navShow = selectDoc('.minicart-wrapper .action.showcart'),
            minicartCounter = selectDoc('#minicart-custom-header .count'),
            amplifyCartKip = selectDoc('#amplify-cart-kip'),
            totalQty = 0,
            total = 0;

        Object.keys(cart).map(item => {
            if (cart[item].q > 0) {
                totalQty += cart[item].q;
                total += (cart[item].q * cart[item].p)
            }
        });

        if (totalQty > 0) {
            let fixed = 0;
            if (totalQty % 1 > 0) {
                fixed = 2;
            }
            minicartCounter.innerHTML = totalQty.toFixed(fixed).replace(/\.0+$/, '');
            navShow.innerHTML = minicartSummaryItems(totalQty.toFixed(fixed).replace(/\.0+$/, ''), total);
            kipCart ? removeClass(kipCart, 'empty') : '';
            magCartEmpty ? removeClass(magCartEmpty, 'empty') : '';
            amplifyCartKip ? removeClass(amplifyCartKip, 'empty') : '';
        } else {
            kipCart ? addClass(kipCart, 'empty') : '';
            magCartEmpty ? addClass(magCartEmpty, 'empty') : '';
            amplifyCartKip ? addClass(amplifyCartKip, 'empty') : '';
            navShow.innerHTML = `<p class="cart-empty"><span>Mi carrito</span></p>`;
        }

        let closeMiniCart = selectDoc('#btn-minicart-close:not(.evented)');
        if (closeMiniCart) {
            addClass(closeMiniCart, 'evented');
            event(closeMiniCart, 'click', function () {
                selectDoc('body').click();
                let mainCartBlock = selectDoc('.block.block-minicart');
                mainCartBlock ? removeClass(mainCartBlock, 'impulse') : '';
            })
        }
    }

    //Sync local storage to backend
    if (itemId || totals) {
        let backToFront = false;
        if (!itemId && !action && totals) {
            //If homepage or my account then sync session
            backToFront = true;
        }
        if (window.location.href.includes('kipcart=true')) {
            backToFront = false;
        }

        let token = customerToken();
        if (token) {
            // alert("Calling-1");
            // syncCartBackend(token, backToFront, lsoptions);
            filterClicks(token, backToFront, lsoptions,"lcx", isReorder, clearElement);
            // (async() => {
            //     console.log('1')
            //     await syncCartBackend(token, backToFront, lsoptions)  
            //     console.log('2')
            //   })()
        } else {
            ajax('GET',
                '/rest/V1/kip/token'
            ).then(function (response) {
                let data = (JSON.parse(response));
                if (data.status === 200) {
                    let token = JSON.parse(data.response).token;
                    customerToken(token);
                    window.kipatcAdding = false;
                    // alert("Calling-2");
                    // syncCartBackend(token, backToFront, lsoptions);
                    filterClicks(token, backToFront, lsoptions,"pos",isReorder);
                    // (async() => {
                    //     console.log('1')
                    //     await syncCartBackend(token, backToFront, lsoptions)  
                    //     console.log('2')
                    //   })()
                } else {
                    handle401();
                }
            });
        }
    }
}

/**
 *
 * @param impulseItems
 * @param impulseItemsv2
 */
export const renderImpulse = (impulseItems,impulseItemsv2) => {
    let customItemsContainer = selectDoc('#minicart-custom-items'),
        kipImpulse = selectDoc('#kip-impulse-wrapper');
    if (customItemsContainer) {
        if (Object.keys(impulseItems).length > 0) {
            if (!kipImpulse) {
                kipImpulse = document.createElement('div');
                kipImpulse.id = 'kip-impulse-wrapper';
                customItemsContainer.appendChild(kipImpulse);
                // console.log("impulsov1",impulseItems);
                // console.log("impulsov2",impulseItemsv2);
                kipImpulse.innerHTML = kipImpulseHtml(impulseItems,impulseItemsv2);

                selectArr('input[name="product"]', kipImpulse).map(product => {
                    selectArr('.qty-controls span', product.parentNode).map(control => {
                        controlQty(control);
                    })

                    let atc = selectDoc('button.tocart', product.parentNode);
                    if (atc) {
                        event(atc, 'click', function () {
                            addClass(atc, 'animate');
                            setTimeout(function () {
                                removeClass(atc, 'animate')
                            }, 1000);
                            controlAtc(atc, product);
                        })
                    }
                });
                

                const showImpulse = () => {
                    let mainBlock = selectDoc('.block.block-minicart');
                    if (mainBlock) {
                        addClass(mainBlock, 'impulse');
                    }
                }

                let checkout = selectDoc('#minicart-custom-footer .checkout');
                if (checkout) {
                    event(checkout, 'click', function (e) {
                        e.preventDefault();
                        showImpulse();
                    })
                }

                let checkoutCart = selectDoc('.checkout-methods-items button.checkout');
                if (checkoutCart) {
                    let parent = checkoutCart.parentNode;
                    parent.removeChild(checkoutCart);

                    let newCheckoutBtn = document.createElement('button');
                    newCheckoutBtn.className = 'action primary checkout';
                    newCheckoutBtn.setAttribute('data-role', 'proceed-to-checkout');
                    newCheckoutBtn.innerHTML = '<span>Proceder a pago</span>';

                    parent.appendChild(newCheckoutBtn);
                    event(newCheckoutBtn, 'click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        e.cancelBubble = true;

                        let openCart = selectDoc('a.action.showcart');
                        if (openCart) {
                            openCart.click();
                            showImpulse();
                        } else {
                            window.location.href = '/checkout';
                        }
                    })
                }
            }
        }
    }
}


/**
 *
 * @param product
 * @param value
 * @param qty
 * @returns {string}
 */
export const kipCartSession = (product = null, value = null, qty = false) => {
    let cart = JSON.parse(localStorage.getItem('kipcart'));
    if (!cart) {
        localStorage.setItem('kipcart', JSON.stringify({}))
    }
    if(!window.kipatc) {
        window.kipatc = {};
    }

    if (product && (value !== null)) { //avoid issues with 0
        if (qty) {
            cart[product].q = value;
        } else {
            if (cart[product]) {
                cart[product].q += value.q;
            } else {
                cart[product] = value;
            }
        }
        window.kipatc[product] = cart[product];
        localStorage.setItem('kipcart', JSON.stringify(cart));
    }

    return localStorage.getItem('kipcart');
}

/**
 *
 * @param updateList
 * @param qi
 */
const clearKipCartSession = (updateList, qi = false) => {
    //If the cart is not being updated then sync with backend
    let newSession = {},
        oldSession = (Object.keys(window.kipatc).length > 0) ? JSON.parse(localStorage.getItem('kipcart')) : updateList;

    if (oldSession) {
        if (!qi) {
            Object.keys(oldSession).map(i => {
                if (oldSession[i].q > 0) {
                    newSession[i] = oldSession[i];
                }
            })
        } else {
            newSession = oldSession;
            Object.keys(updateList).map(i => {
                if (newSession[i]) {
                    if (newSession[i].q > 0 && !newSession[i].qi) {
                        newSession[i].qi = updateList[i].qi;
                    }
                }
            });
        }
    }
    localStorage.setItem('kipcart', JSON.stringify(newSession));
}

/**
 *
 * @param value
 * @returns {string}
 */
const customerToken = (value = null) => {
    if (value) {
        localStorage.setItem('kiptoken', value);
    }
    return localStorage.getItem('kiptoken');
}

function filterClicks(a,b,c,d="N",isReorder = false, clearElement = null){
    // console.log("<<>>"+d);
    let lastExecution = window.localStorage.getItem("lastsync");
    const delay = 100;
    var n_tmstamp=parseInt(lastExecution)+parseInt(delay);        
    if (isReorder){
        console.log("R=");
        (async() => {
            console.log('1')
            await syncCartBackend(a, b, c,isReorder, clearElement)  
            console.log('2')
          })()
    }
    else{
        if (parseInt(n_tmstamp) > Date.now()){        
            console.log("NotProceed");
            return true;
        }
        else{
            (async() => {
                console.log('1')
                await syncCartBackend(a, b, c, false, clearElement)  
                console.log('2')
              })()
        }
    }
}

/**
 *
 * @param token
 * @param backToFront
 * @param lsoptions
 */
async function syncCartBackend  (token, backToFront = false, lsoptions = null, isReorder = false, clearElement = null) {
    // let lastExecution = window.localStorage.getItem("lastsync");
    // const delay = 500;
    // var n_tmstamp=parseInt(lastExecution)+parseInt(delay);        
    // if (parseInt(n_tmstamp) > Date.now()){        
    //     console.log("NotProceed");
    //     return true;
    // }
    
    console.log("Proceed..");
    let footerMiniCart = selectDoc('#minicart-custom-footer'),
        kipImpulse = selectDoc('#kip-impulse-wrapper');

    if (!window.kipatcAdding||isReorder) {
        window.kipatcAdding = true;

        let sessionCart = JSON.stringify(window.kipatc);
        if(lsoptions) {
            sessionCart = JSON.stringify(lsoptions);
        } else {
            if (backToFront) {
                sessionCart = "backToFront";
            }
        }
        window.kipatc = {};

        kipImpulse ? addClass(kipImpulse, 'loading') : '';
        addClass(footerMiniCart, 'loading');
        
        window.localStorage.setItem("lastsync",parseInt(Date.now()));

        ajax('POST',
            '/rest/V1/kip/cart',
            JSON.stringify({
                data: sessionCart
            }),
            'application/json',
            null,
            'Bearer ' + token
        ).then(function (response) {
            let data = (JSON.parse(response));
            if (data.status === 401) {
                handle401();
            } else {
                let result = JSON.parse(data.response);

                //Token expired
                if (result.token) {
                    window.kipatc = {};
                    window.kipatcAdding = false;
                    console.log('Token expired...');
                    customerToken(result.token)
                    // alert("Calling-3");
                    // syncCartBackend(result.token, backToFront, lsoptions);
                    filterClicks(result.token, backToFront, lsoptions,"tokex");
                    

                      

                } else {
                    let updateList = result.cart;
                    window.kipexpress = result.express;
                    window.kipflash = result.flash;

                    if (result.errors) {
                        let waitShowError = 0;
                        result.errors.map(error => {
                            setTimeout(function () {
                                displayModalError(error)
                            }, waitShowError);
                            waitShowError += 4500;
                        });
                    }

                    if (Object.keys(window.kipatc).length > 0) {
                        // alert("SYNC CODITY CART");
                        window.kipatcAdding = false;
                        clearKipCartSession(updateList, true);
                        // alert("Calling-4");
                        // syncCartBackend(token, backToFront, lsoptions);
                        filterClicks(result.token, backToFront, lsoptions,"px90");
                        // (async() => {
                        //     console.log('1')
                        //     await syncCartBackend(token, backToFront, lsoptions)  
                        //     console.log('2')
                        //   })()

                    } else {
                        window.kipatc = {};
                        window.kipatcAdding = false;
                        console.log('Cart updated success...');

                        /*************/
                        /*****QXD*****/
                        /*************/
                        /**********************************/
                        /* Unblocking the current element */
                        /**********************************/
                        if(clearElement != null){
                            console.log("removing inactive_button");
                            removeClass(clearElement, 'inactive_button');
                        }
                        if (data.status === 200) {
                            if(selectDoc('body.checkout-index-index')) {
                                clearKipCartSession(updateList);
                                let total = selectDoc('#kip-order-summary .medium-24.grand-total')
                                if(total) {
                                    let before = parseFloat(total.getAttribute('data-total')),
                                        after = parseFloat(result.totals.total);
                                    if(before !== after) {
                                        if((before <= 0 && after > 0) || (after <= 0 && before > 0)) {
                                            let mainBlockCheckout = selectDoc('.opc-block-summary');
                                            if (mainBlockCheckout) {
                                                let dummy = document.createElement('span')
                                                dummy.id = 'wake-up-mag';
                                                mainBlockCheckout.appendChild(dummy);
                                            }
                                            initVaultSelector();
                                        }
                                    }
                                }
                            } else {
                                clearKipCartSession(updateList);
                                renderCart();

                                //syncCartLs(token, lsoptions);
                                let kipImpulse = selectDoc('#kip-impulse-wrapper');
                                kipImpulse ? removeClass(kipImpulse, 'loading') : '';
                                removeClass(footerMiniCart, 'loading');
                                updateKipCartBottomTotals(data);
                                checkAvailableShippings();

                                //Update knockout.js magento
                                let mainBlock = selectDoc('.block.block-minicart');
                                if (mainBlock) {
                                    let dummy = document.createElement('span')
                                    dummy.id = 'wake-up-mag';
                                    mainBlock.appendChild(dummy);
                                }

                                //Facebook atc event
                                if (result.skus) {
                                    result.skus.map(sku => {
                                        let atcEvent = new CustomEvent('ajax:addToCart', {'sku': sku});
                                        document.dispatchEvent(atcEvent);
                                    });
                                }
                            }
                        }
                    }

                    if (result.impulse) {
                        if (Object.keys(result.impulse).length > 0) {
                            // console.log("IMPULSOV2",result.impulse_v2);
                            renderImpulse(result.impulse,result.impulse_v2);
                            updateKipCartBottomTotals(data);
                        }
                    }
                }
            }
        })
    // }
    // else{
    //     console.log('CODITY-FIX-SUCCESS!')
    // }

    } else {
        console.log('Update is pending...')
    }
    return true;    
}

const syncCartLs = (token, lsoptions) => {
    console.log('Syncing LS cart...');
    ajax('POST',
        '/rest/V1/kip/lscart',
        JSON.stringify({
            data: JSON.stringify(lsoptions)
        }),
        'application/json',
        null,
        'Bearer ' + token
    ).then(function (response) {
        console.log('LS cart synced...');
        //line: 41
    })
}

/**
 *
 * @param data
 */
const updateKipCartBottomTotals = (data) => {
    let footerMiniCartTable = selectDoc('#minicart-custom-footer table');

    let totals = null;
    if (data.status === 200) {
        totals = JSON.parse(data.response).totals;
    }

    if (footerMiniCartTable && totals) {
        //Append html
        let cart = JSON.parse(data.response).cart,
            content = selectDoc('#kip-order-summary-minicart', footerMiniCartTable);

        if (!content) {
            content = document.createElement('div');
            content.id = 'kip-order-summary-minicart';
            footerMiniCartTable.appendChild(content);
        }

        content.innerHTML = orderSummaryHtml(totals, true) ?? content.innerHTML;
        renderDiscountBlock(totals);

        let navPrice = selectDoc('.action.showcart .cart-subtotal .price');
        if (totals.subtotal && totals.discount) {
            navPrice ? navPrice.innerText = formatDollar(totals.subtotal + totals.discount) : '';
        }

        let impulseTotal = selectDoc('#kip-impulse-wrapper .total .update');
        if (impulseTotal && totals.total) {
            impulseTotal.innerHTML = `${formatDollar(totals.total - totals.shipping)}`;
        }

        clearKipCartSession(cart);
        renderCart();
    }

    let checkoutTotal = selectDoc('#kip-order-summary .total .grand-total');
    if (checkoutTotal && totals) {
        showLoader(true);
        checkoutTotal.innerHTML = formatDollar(totals.total);

        let checkoutDiscount = selectDoc('#kip-order-summary .discount');
        if (checkoutDiscount) {
            checkoutDiscount.innerHTML = `${formatDollar(totals.discount)}`;
        }

        let checkoutReferral = selectDoc('#kip-order-summary .referral');
        if (checkoutReferral) {
            checkoutReferral.innerHTML = `${formatDollar(totals.referral)}`;
        }
    }
}

/**
 *
 * @param totals
 */
const renderDiscountBlock = (totals) => {
    //Check if coupon is applied or not
    let couponForm = selectDoc('#discount-coupon-form');
    if (couponForm) {
        let button = selectDoc('button.action.primary span', couponForm),
            couponInput = selectDoc('input#coupon_code', couponForm),
            removeCoupon = selectDoc('input#remove-coupon', couponForm),
            errorCoupon = selectDoc('#error-coupon'),
            added;

        if (Math.abs(totals.discount) > 0 && !inputEmpty(totals.discount_code)) {
            button.innerHTML = 'Cancelar';
            couponInput.value = totals.discount_code.replace('(', '').replace(')', '');
            couponInput.disabled = true;
            removeCoupon.value = 1;
            added = true;
        } else {
            button.innerHTML = 'Aplicar';
            couponInput.value = '';
            couponInput.disabled = false;
            removeCoupon.value = 0;
            added = false;
        }

        //Check if coupon was properly applied or canceled...
        if (window.kipcoupon) {
            //Tried to remove
            if (!added && window.kipcoupon === 'add') {
                errorCoupon.classList.add('active');
                errorCoupon.innerHTML = 'Error al cancelar el cupón.';
                setTimeout(function () {
                    errorCoupon.classList.remove('active');
                }, 5000)
            }
            //Tried to add
            if (!added && window.kipcoupon === 'remove') {
                errorCoupon.classList.add('active');
                errorCoupon.innerHTML = 'Cupón no válido/removido.';
                setTimeout(function () {
                    errorCoupon.classList.remove('active');
                }, 5000)
            }
            window.kipcoupon = null;
        }

        //Add events to cancel or add coupon...
        let submit = selectDoc('button.action:not(.evented)', couponForm);
        if (submit) {
            addClass(submit, 'evented');
            event(submit, 'click', function () {
                let couponInput = selectDoc('input#coupon_code');
                if (inputEmpty(couponInput.value)) {
                    couponInput.classList.add('mage-error')
                } else {
                    couponInput.classList.remove('mage-error')
                    submit.style.opacity = '0.5';
                    let remove = selectDoc('input#remove-coupon').value;
                    window.kipcoupon = remove ? 'remove' : 'add';
                    ajax('POST',
                        `/checkout/cart/couponPost?coupon_code=${selectDoc('input#coupon_code').value}&remove=${remove}&form_key=${selectDoc('input[name="form_key"]').value}`,
                        null,
                        'text/html; charset=UTF-8',
                    ).then(function () {
                        submit.style.opacity = '1';
                        //alert("Calling-3");
                        // syncCartBackend(customerToken());
                        filterClicks(customerToken());
                        // (async() => {
                        //     console.log('1')
                        //     await syncCartBackend(customerToken())  
                        //     console.log('2')
                        //   })()
                    })
                }
            })
        }
    }
}

/**
 * Show not logged in components
 */
const handle401 = () => {
    let footerMiniCart = selectDoc('#minicart-custom-footer'),
        itemsMiniCart = selectDoc('#kip-content-wrapper .minicart-items-wrapper'),
        amplifyCartKip = selectDoc('#amplify-cart-kip');

    amplifyCartKip ? amplifyCartKip.parentNode.removeChild(amplifyCartKip) : '';
    footerMiniCart ? footerMiniCart.innerHTML = `
                            <p class="medium-12 login pre">Para proceder con tu compra</p>
                            <a href="/customer/account/login" class="checkout">Inicia Sesión</a>
                            <p class="medium-12 login">¿No tienes cuenta? <a href="/customer/account/create">Crear cuenta</a></p>` : '';

    addClass(footerMiniCart, 'log-in');
    addClass(itemsMiniCart, 'log-in');
}

const checkAvailableShippings = () => {
    ajax('GET', `/rest/V1/kipping/schedules`)
        .then(function (response) {
            let data = (JSON.parse(response));
            if (data.status === 200) {
                let schedules = JSON.parse(data.response);

                let expressLeft = selectDoc('.express-left'),
                    flashLeft = selectDoc('.flash-left'),
                    cartPageTitle = selectDoc('.checkout-cart-index .page-title-wrapper h1.page-title'),
                    expressLeftTotal = 0,
                    flashLeftTotal = 0;
                selectArr('#kip-content-wrapper .cart-item-qty').map(qty => {
                    if (selectDoc(`.kiptem-id-${qty.getAttribute('data-cart-item')} .presentacion .ex`)) {
                        expressLeftTotal += parseFloat(qty.value);
                    }
                    if (selectDoc(`.kiptem-id-${qty.getAttribute('data-cart-item')} .presentacion .flash`)) {
                        flashLeftTotal += parseFloat(qty.value);
                    }
                });
                expressLeftTotal = window.kipexpress - expressLeftTotal;
                flashLeftTotal = window.kipflash - flashLeftTotal;

                let expressCopy = '';
                if (!isNaN(expressLeftTotal)) {
                    let expressAvailable = selectDoc('.available-shippings .express'),
                        expressAvailableCart = selectDoc('.available-shippings-cart .express');

                    if (expressLeftTotal < 0) {
                        expressAvailable ? removeClass(expressAvailable, 'on') : '';
                        expressAvailableCart ? removeClass(expressAvailableCart, 'on') : '';
                    } else {
                        expressCopy = `Express máx.: ${window.kipexpress} | Faltan: ${expressLeftTotal.toFixed(2)} ${expressLeftTotal < 0 ? '(no disponible)' : ''}`;
                        expressAvailable ? addClass(expressAvailable, 'on') : '';
                        expressAvailableCart ? addClass(expressAvailableCart, 'on') : '';
                    }
                }

                let flashCopy = '';
                if (!isNaN(flashLeftTotal)) {
                    let flashAvailable = selectDoc('.available-shippings .flash'),
                        flashAvailableCart = selectDoc('.available-shippings-cart .flash');

                    if (flashLeftTotal < 0) {
                        flashAvailable ? removeClass(flashAvailable, 'on') : '';
                        flashAvailableCart ? removeClass(flashAvailableCart, 'on') : '';
                    } else {
                        flashCopy = `Flash máx.: ${window.kipexpress} | Faltan: ${flashLeftTotal} ${flashLeftTotal < 0 ? '(no disponible)' : ''}`;
                        flashAvailable ? addClass(flashAvailable, 'on') : '';
                        flashAvailableCart ? addClass(flashAvailableCart, 'on') : '';
                    }
                }

                expressLeft.innerHTML = expressCopy;
                flashLeft.innerHTML = flashCopy;
                // CART EXTENDIDO
                cartPageTitle ? cartPageTitle.innerHTML =
                    `<span class="base">
                        Carrito
                 </span>
                 <p class="available-shippings-cart">
                        <strong>Envíos disponibles:</strong>
                        <span class="scheduled">Programado</span> |
                        <span class="scheduled_today">Mismo día</span> |
                        <span class="scheduled_festivity">Pre-Order</span> <d>|</d>
                        <span class="express">Express</span> |
                        <span class="flash">Flash</span>
                 </p>
                 <p class="express-left-cart">${expressCopy}</p><p class="flash-left-cart">${flashCopy}</p>` : '';

                let container = selectDoc('.available-shippings');
                if (container) {
                    let express = selectDoc('.express', container);
                    if (express) {
                        let expressLeft = selectDoc('.express-left');
                        if (!schedules.isExpressAvailable) {
                            removeClass(express, 'on');
                            expressLeft ? expressLeft.innerHTML = '' : '';
                        } else {
                            addClass(express, 'on');
                        }
                    }

                    let flash = selectDoc('.flash', container);
                    if (flash) {
                        let flashLeft = selectDoc('.flash-left');
                        if (!schedules.isFlashAvailable) {
                            removeClass(flash, 'on');
                            flashLeft ? flashLeft.innerHTML = '' : '';
                        } else {
                            addClass(flash, 'on');
                        }
                    }

                    let scheduled = selectDoc('.scheduled', container);
                    if (scheduled) {
                        if (!schedules.isScheduledAvailable) {
                            removeClass(scheduled, 'on');
                        } else {
                            addClass(scheduled, 'on');
                        }
                    }

                    let scheduledToday = selectDoc('.scheduled_today', container);
                    if (scheduledToday) {
                        if (!schedules.isScheduledTodayAvailable) {
                            removeClass(scheduledToday, 'on');
                        } else {
                            addClass(scheduledToday, 'on');
                        }
                    }

                    let scheduledFestivity = selectDoc('.scheduled_festivity', container);
                    if (scheduledFestivity) {
                        if (schedules.isScheduledFestivityAvailable === null) {
                            let d = selectDoc('d', container);
                            scheduledFestivity.parentNode.removeChild(scheduledFestivity);
                            d.parentNode.removeChild(d);
                        } else {
                            if (!schedules.isScheduledFestivityAvailable) {
                                removeClass(scheduledFestivity, 'on');
                            } else {
                                addClass(scheduledFestivity, 'on');
                            }
                        }
                    }
                }

                let containerCart = selectDoc('.available-shippings-cart');
                if (containerCart) {
                    let expressCart = selectDoc('.express', containerCart),
                        flashCart = selectDoc('.flash', containerCart);

                    if (expressCart || flashCart) {
                        let expressLeft = selectDoc('.express-left-cart');
                        if (!schedules.isExpressAvailable) {
                            removeClass(expressCart, 'on');
                            expressLeft ? expressLeft.innerHTML = '' : '';
                        } else {
                            addClass(expressCart, 'on')
                        }

                        let flashLeft = selectDoc('.flash-left-cart');
                        if (!schedules.isFlashAvailable) {
                            removeClass(flashCart, 'on');
                            flashLeft ? flashLeft.innerHTML = '' : '';
                        } else {
                            addClass(flashCart, 'on')
                        }
                    }

                    let scheduledCart = selectDoc('.scheduled', containerCart);
                    if (scheduledCart) {
                        if (!schedules.isScheduledAvailable) {
                            removeClass(scheduledCart, 'on');
                        } else {
                            addClass(scheduledCart, 'on');
                        }
                    }

                    let scheduledTodayCart = selectDoc('.scheduled_today', containerCart);
                    if (scheduledTodayCart) {
                        if (!schedules.isScheduledTodayAvailable) {
                            removeClass(scheduledTodayCart, 'on');
                        } else {
                            addClass(scheduledTodayCart, 'on');
                        }
                    }

                    let scheduledFestivityCart = selectDoc('.scheduled_festivity', containerCart);
                    if (scheduledFestivityCart) {
                        if (schedules.isScheduledFestivityAvailable === null) {
                            let d = selectDoc('d', container);
                            scheduledFestivityCart.parentNode.removeChild(scheduledFestivityCart);
                            d.parentNode.removeChild(d);
                        } else {
                            if (!schedules.isScheduledFestivityAvailable) {
                                removeClass(scheduledFestivityCart, 'on');
                            } else {
                                addClass(scheduledFestivityCart, 'on');
                            }
                        }
                    }
                }
            }
        });
}

/**
 *
 * @returns {boolean|{options: {}, super_attribute: {}}}
 */
export const extractPDPOptions = () => {
    let f = new FormData(selectDoc('#product_addtocart_form'));
    if (f) {
        let atcData = {
            options: {},
            super_attribute: {},
        };
        for (var prop of f.entries()) {
            let input = selectDoc(`[name="${prop[0]}"]`)
            if (prop[1]) {
                let id = prop[0].replace(']', '').replace('options[', '').replace('super_attribute[', '');
                if (input) {
                    removeClass(input, 'mage-error');
                    let mageError = selectDoc(`#${input.id}-error`);
                    mageError ? (mageError.style.display = 'none') : '';
                }

                if (prop[0].includes('options[')) {
                    atcData.options[id] = prop[1];
                } else {
                    if (prop[0].includes('super_attribute[')) {
                        atcData.super_attribute[id] = prop[1];
                    } else {
                        if (!prop[0].includes('form_key')) {
                            atcData[prop[0]] = prop[1];
                        }
                    }
                }
            } else {
                if (input) {
                    if (input.getAttribute('aria-required')) {
                        return false;
                    }
                }
            }
        }

        return atcData;
    } else {
        return false;
    }
}

/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    toggleClass, observeAll, hasClass, addClass, removeClass, removeClassAll, ajax
} from "../utils/wonder";
import {renderCart} from "../helpers/service.cart";
import {initMenuItems, matchMenuItemsWUrl} from "../helpers/service.menus";
import {showLoader} from "../helpers/service.dialog";
import {lazyLoadIcons} from "../helpers/service.scroll";
import {formatDollar} from "../helpers/service.markup";

export class Header {
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
        this.initNav();
        this.mobileNav();
        this.initMinicart()

        if(selectDoc('#checkout-success-kip')) {
            localStorage.removeItem('kipcart');
        } else {
            renderCart(null,null,true);
        }
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.nav = selectDoc("header");
        this.hams = selectArr("#hamburger");
        this.toggleMenu = selectDoc("#toggle-menu");
        this.submenusOpen = selectArr("#toggle-menu .submenu-open");
        this.showSearch = selectDoc(".minisearch-wrapper .action.showsearch");
        this.search = selectDoc(".header .block.block-search");
        this.searchInput = selectDoc("#search_mini_form #search");

        this.minicart = selectDoc('#minicart-content-wrapper');

        this.loggedInAction = selectDoc('#logged-in-nav-action')
    }

    /**
     *
     */
    initNav() {
        let self = this;

        ajax('GET',
            `/rest/V1/kip/customer-data`
        ).then(function (response) {
            let data = (JSON.parse(response));
            if (data.status === 200) {
                let data = JSON.parse(response),
                    customer = JSON.parse(data.response);

                if(customer.data && self.loggedInAction) {
                    let avatar = selectDoc('.avatar', self.loggedInAction),
                        name = selectDoc('.name', self.loggedInAction);

                    if(avatar && customer.data.avatar) {
                        avatar.innerHTML = `<img src="${customer.data.avatar}" alt="avatar">`;
                    }

                    if(name && customer.data.firstname) {
                        name.innerHTML = `Hola ${customer.data.firstname}`;
                    }
                }
            }
        });

        this.initReferrals();

        //Current main nav item
        selectArr("nav.navigation ul li a").map(a => {
            if (a.href == window.location.href) {
                addClass(a.parentNode, 'current');
            }
        });
    }

    /**
     *
     */
    mobileNav() {
        let self = this;

        //Open/Close
        this.hams.map((ham) => {
            event(ham, "click", function () {
                toggleClass(ham, "opened");
                toggleClass(self.toggleMenu, "open");
                toggleClass(self.html, "lock");
            });
        });

        //Dropdown menus
        this.submenusOpen.map((open) => {
            let submenu = selectDoc(`#${open.getAttribute("data-target")}`);
            if (submenu) {
                event(open, "click", function () {
                    toggleClass(submenu, "open");
                    toggleClass(self.toggleMenu, "submenu");
                });

                let back = selectDoc("#back", submenu);
                if (back) {
                    event(back, "click", function () {
                        toggleClass(submenu, "open");
                        toggleClass(self.toggleMenu, "submenu");
                    });
                }
            }
        });

        //Search
        if (this.showSearch) {
            // DATALAYER-CODITY 
            window.dataLayer.push({'event': 'search'});
            event(this.showSearch, 'click', function () {
                toggleClass(self.search, 'show-mobile')
                toggleClass(self.showSearch, 'showsearch-active')
                if(hasClass(self.search,'show-mobile')) {
                    selectDoc('input#search', self.search).focus();
                }
            })
        }

        //Categories
        initMenuItems('#nav-menu-categories')
        matchMenuItemsWUrl('#nav-menu-categories')
        //#ui-id-1 > li:nth-child(2)    

        // let firstNavElement = selectDoc('nav.navigation ul li a'), //old
        let firstNavElement = selectDoc('nav.navigation ul > li:nth-child(2) a'),
            navMenuCat = selectDoc('#nav-menu-categories'),
            navMenuCatBack = selectDoc('#nav-menu-categories-back'),
            livechat = selectDoc('#chat-widget-container');

        if(firstNavElement && navMenuCat && window.innerWidth <= 768) {
            firstNavElement.setAttribute("href", "javascript:void(0);");
            navMenuCat.style.height = (window.innerHeight - 205) + 'px';
            event(firstNavElement, 'click', function (e) {
                e.preventDefault();
                toggleClass(navMenuCat, 'show');
                livechat = selectDoc('#chat-widget-container')
                addClass(livechat, 'minicart');
                navMenuCat.scrollTop = 0;
                lazyLoadIcons();
                //DATALAYER-CODITY                
                window.dataLayer.push({'event': 'clicktowhatsapp'});
            });

            event(navMenuCatBack, 'click', function (e) {
                e.preventDefault();
                toggleClass(navMenuCat, 'show');
                livechat = selectDoc('#chat-widget-container')
                removeClass(livechat, 'minicart')
            });

            const attrObserver = new MutationObserver((mutations) => {
                mutations.forEach(mu => {
                    if (mu.type !== "attributes" && mu.attributeName !== "class") return;
                    if(!hasClass(selectDoc('html'), 'nav-open') && hasClass(navMenuCat, 'show')) {
                        navMenuCatBack.click();
                    }
                });
            });
            attrObserver.observe(selectDoc('html'), {attributes: true})
        }
    }

    /**
     *
     */
    initMinicart() {
        let opener = selectDoc('.minicart-wrapper .action.showcart');
        observeAll(opener, function (nodes) {
          if(nodes.length > 0) {
              //toggleMinicartLiveChatZindex
              let livechat = selectDoc('#chat-widget-container');
              if(livechat) {
                  if(hasClass(nodes[0].target, 'active')) {
                      addClass(livechat, 'minicart');
                  } else {
                      removeClass(livechat, 'minicart')
                  }
              }

              //Mobile height
              let container = document.querySelector('.block.block-minicart.ui-dialog-content');
              if(container) {
                  container.style.height = (window.innerHeight) + 'px';
              }
          }
        })

        event(window, 'resize', function () {
            //Mobile height
            let container = document.querySelector('.block.block-minicart.ui-dialog-content');
            if(container) {
                container.style.height = window.innerHeight + 'px';
            }
        })
    }

    /**
     *
     */
    initReferrals() {
        const copy = () => {
            let copyText = selectDoc("#referrals-code-copy"),
                copy = selectDoc("#referrals-code"),
                range = document.createRange(),
                selection = window.getSelection();

            range.selectNodeContents(copyText);
            selection.removeAllRanges();
            selection.addRange(range);
            document.execCommand("copy");

            range.selectNodeContents(copy);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        const openReferralModal = (referral) => {
            let modal = selectDoc('#referrals-modal');
            if(modal) {
                let code = selectDoc('#referrals-code'),
                    copyText = selectDoc("#referrals-code-copy"),
                    times = selectDoc('#referrals-times'),
                    earned = selectDoc('#referrals-earned'),
                    spent = selectDoc('#referrals-spent'),
                    left = selectDoc('#referrals-left'),
                    daysLeft = selectDoc('#referrals-days-left'),
                    expire = selectDoc('#referrals-expire'),
                    coupon = selectDoc('#referrals-coupon'),
                    couponMax = selectDoc('#referrals-coupon-max'),
                    win = selectDoc('#referrals-win'),
                    timesUsed = parseInt(referral.times_used);

                code.innerHTML = referral.referral_code;
                copyText.innerHTML = referral.share_copies.copy;

                if (timesUsed > 0) {
                    times.innerHTML = timesUsed > 1 ? `${timesUsed} veces` : `${timesUsed} vez`;
                } else {
                    times.innerHTML = '';
                }

                earned.innerHTML = formatDollar(referral.total_earned);
                spent.innerHTML = formatDollar(referral.total_spent);
                left.innerHTML = formatDollar(referral.total_left);
                daysLeft.innerHTML = referral.days_left;
                expire.innerHTML = referral.days_expire;

                coupon.innerHTML = referral.coupon_amount;
                couponMax.innerHTML = formatDollar(referral.coupon_max_amount);
                win.innerHTML = formatDollar(referral.you_win);

                addClass(modal, 'active');
            }
        }

        ajax('GET',
            `/rest/V1/referral/customer`
        ).then(function (response) {
            let data = JSON.parse(response);
            if (data.status === 200) {
                let referral = JSON.parse(data.response);
                if(referral.output) {
                    referral = referral.output;
                    let renderContainer = selectDoc('.header.content .block.block-search');
                    if(renderContainer) {
                        addClass(renderContainer, 'referral');

                        let referralBtn = document.createElement('button'),
                            referralBtnMobile = selectDoc('#referrals-open-mobile');

                        referralBtn.id = 'block-referral-kip';
                        referralBtn.innerHTML = `<span><svg width="23" height="21" viewBox="0 0 23 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <path d="M12.75 10.5C15.2245 10.5 17.4108 8.16984 17.625 5.30531C17.7314 3.86625 17.28 2.52422 16.3538 1.52719C15.4373 0.542344 14.1562 0 12.75 0C11.3325 0 10.0505 0.539062 9.14062 1.51781C8.22047 2.50734 7.77187 3.85219 7.875 5.30438C8.08547 8.16938 10.2712 10.5 12.75 10.5ZM22.4709 19.1138C22.0753 16.9191 20.8402 15.0755 18.8995 13.7817C17.1759 12.6328 14.992 12 12.75 12C10.508 12 8.32406 12.6328 6.60047 13.7812C4.65984 15.075 3.42469 16.9186 3.02906 19.1133C2.93859 19.6162 3.06141 20.1136 3.36609 20.4778C3.50429 20.6438 3.67781 20.7768 3.87399 20.8672C4.07016 20.9575 4.28404 21.0029 4.5 21H21C21.2161 21.0031 21.4301 20.9578 21.6265 20.8676C21.8228 20.7773 21.9965 20.6443 22.1348 20.4783C22.4386 20.1141 22.5614 19.6167 22.4709 19.1138ZM4.125 12V10.125H6C6.19891 10.125 6.38968 10.046 6.53033 9.90533C6.67098 9.76468 6.75 9.57391 6.75 9.375C6.75 9.17609 6.67098 8.98532 6.53033 8.84467C6.38968 8.70402 6.19891 8.625 6 8.625H4.125V6.75C4.125 6.55109 4.04598 6.36032 3.90533 6.21967C3.76468 6.07902 3.57391 6 3.375 6C3.17609 6 2.98532 6.07902 2.84467 6.21967C2.70402 6.36032 2.625 6.55109 2.625 6.75V8.625H0.75C0.551088 8.625 0.360322 8.70402 0.21967 8.84467C0.0790176 8.98532 0 9.17609 0 9.375C0 9.57391 0.0790176 9.76468 0.21967 9.90533C0.360322 10.046 0.551088 10.125 0.75 10.125H2.625V12C2.625 12.1989 2.70402 12.3897 2.84467 12.5303C2.98532 12.671 3.17609 12.75 3.375 12.75C3.57391 12.75 3.76468 12.671 3.90533 12.5303C4.04598 12.3897 4.125 12.1989 4.125 12Z" fill="#F9A000"/>
                                                  </svg></span><span>¡Refiere y gana!</span>`;
                        renderContainer.appendChild(referralBtn);

                        event(referralBtn, 'click', function () {
                            openReferralModal(referral)
                        });
                        event(referralBtnMobile, 'click', function () {
                            openReferralModal(referral)
                        });
                    //init to handle other actions
                    openReferralModal(referral);
                    let modal = selectDoc('#referrals-modal');
                    removeClass(modal, 'active');
                        //Share actions
                        let copyAction = selectDoc('#referrals-copy'),
                            wpAction = selectDoc('#referrals-wp'),
                            fbAction = selectDoc('#referrals-fb'),
                            mailAction = selectDoc('#referrals-email');

                        mailAction.href = `mailto:?subject=${encodeURI(referral.share_copies.mail_subject)}&body=${encodeURI(referral.share_copies.mail_body)}`;
                        wpAction.href = `https://api.whatsapp.com/send?text=${encodeURI(referral.share_copies.wp)}`;
                        event(copyAction, 'click', function () {
                            copy();
                        })
                        event(fbAction, 'click', function () {
                            FB.ui({
                                display: 'popup',
                                method: 'share',
                                href: 'https://kip.sv',
                                quote: referral.share_copies.fb,
                                //hashtag: '#kipcash'
                            }, function(response){});
                        })
                    }
                }
            }
        });
    }
}

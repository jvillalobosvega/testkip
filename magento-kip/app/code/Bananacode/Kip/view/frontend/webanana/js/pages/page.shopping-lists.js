/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    selectArr,
    event,
    selectDoc,
    addClass,
    removeClassAll, ajax,
    removeClass,
    hasClass
} from "../utils/wonder";

import {showLoader} from "../helpers/service.dialog";

import {shoppingLine, shoppingListSearch, shoppingListSuccess} from "../helpers/service.markup";

import {inputEmpty} from "../helpers/service.validator";

export class ShoppingLists {
    /**
     * ShoppingLists constructor
     */
    constructor() {
        if (selectDoc('#shopping-lists') || selectDoc('body.catalogsearch-result-index')) {
            this.init();
        }
    }

    /**
     * Initialize class
     */
    init() {
        this.props();
        this.initTabs();
        this.initActions();
        this.listLists();
        this.initSearch()
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.newListContainer = selectDoc(".content.new-list");
        this.editListContainer = selectDoc(".content.edit-list");
        this.tabs = selectArr(".tabs .tab");
        this.createList = selectDoc(".create-list");
        this.editLines = selectArr('.edit-list #page form .line .field');
        this.containers = selectArr(".content");

        this.token = selectDoc("input[name='token']");
        this.customerId = selectDoc("input[name='customer_id']");

        this.searchBtn = selectDoc("#search-list");
        this.searchUnSavedBtn = selectDoc("#search-unsaved-list");
        this.customerLists = selectDoc('#customer-lists');

        this.popUp = selectDoc('#shopping-lists-popup');
        this.popUpModes = selectArr('#shopping-lists-popup .mode');

        this.saveBtns = selectArr("#save-list");
        this.popUpSaveMode = selectDoc('#shopping-lists-popup .edit.mode');
        this.popUpSaveGo = selectDoc('#shopping-lists-popup .edit.mode button.primary-btn');
        this.popUpSaveName = selectDoc('#shopping-lists-popup .edit.mode [name="list-name"]');

        this.deleteBtn = selectDoc("#delete-list");
        this.popUpDeleteMode = selectDoc('#shopping-lists-popup .delete.mode');
        this.popUpDeleteGo = selectDoc('#shopping-lists-popup .delete.mode button.primary-btn');

        this.popUpCancel = selectArr('#shopping-lists-popup .mode .cancel-modal');

        this.popUpSuccessMode = selectDoc('#shopping-lists-popup .success.mode');

        this.currentList = null;
        this.goBack = selectDoc('#go-back');

        this.searchPage = selectDoc('body.catalogsearch-result-index');
        this.currentLine = 1;
        this.maxLines = 10;
    }

    initTabs() {
        let self = this;
        if (this.newListContainer) {
            this.tabs.map(tab => {
                event(tab, 'click', function () {
                    removeClassAll(self.tabs, 'active');
                    addClass(tab, 'active');

                    let content = selectDoc(`.content.${tab.id}`);
                    if (content) {
                        removeClassAll(self.containers, 'content-active');
                        addClass(content, 'content-active');
                        self.currentList = null;
                        self.popUpSaveName.value = '';

                        if (tab.id === 'new-list') {
                            let firstInput = selectDoc('.line .field.value input', content);
                            if (firstInput) {
                                self.currentLine = 1;
                                firstInput.focus();
                            }
                        }
                    }
                })
            });

            event(this.createList, 'click', function () {
                self.tabs[0].click();
            })
        }
    }

    initLines() {
        let self = this;

        selectArr('form .line').map(line => {
            if (!hasClass(line, 'event')) {
                addClass(line, 'event');

                event(line, 'click', function (e) {
                    e.preventDefault();
                    self.currentLine = parseInt(line.getAttribute('data-line'))
                });

                let input = selectDoc('.field.value input', line);
                if (input) {
                    event(input, 'focus', function (e) {
                        self.currentLine = parseInt(this.parentNode.parentNode.getAttribute('data-line'));
                        if (self.currentLine >= self.maxLines) {
                            let contentActiveForm = selectDoc('.content-active form');
                            if (contentActiveForm) {
                                self.maxLines++;
                                let newLine = document.createElement('div');
                                newLine.className = 'line';
                                newLine.setAttribute('data-line', self.maxLines);
                                newLine.innerHTML = shoppingLine();
                                contentActiveForm.appendChild(newLine);
                                self.initLines();
                            }
                        }
                    })
                }
            }
        })
    }

    initActions() {
        let self = this;

        this.saveBtns.map(saveBtn => {
            event(saveBtn, 'click', function () {
                self.popUp.classList.add('active');
                self.html.classList.add('noscroll');
                removeClassAll(self.popUpModes, 'active')
                addClass(self.popUpSaveMode, 'active')
            })
        })

        if (this.popUpSaveGo) {
            event(this.popUpSaveGo, 'click', function () {
                if (self.popUpSaveName.value) {
                    let items = self.currentList ? selectArr('div.edit-list .line') : selectArr('div.new-list .line'),
                        filteredItems = [];

                    items.map(item => {
                        let input = selectDoc('input[name="searchProduct"]', item);
                        if (input.value) {
                            if (selectDoc('.field.choice.checked', item)) {
                                filteredItems.push({
                                    "v": input.value,
                                    "s": true,
                                });
                            } else {
                                filteredItems.push({
                                    "v": input.value,
                                    "s": false,
                                });
                            }
                        }
                    });

                    let data = {
                        "name": self.popUpSaveName.value,
                        "items": filteredItems
                    };

                    if (self.currentList) {
                        data.list_id = self.currentList;
                    }

                    showLoader();
                    ajax('POST',
                        `/rest/V1/bananacodeShoppingList/${self.currentList ? 'edit' : 'add'}`,
                        JSON.stringify({
                            data: JSON.stringify(data)
                        }),
                        'application/json',
                        null,
                        'Bearer ' + self.token.value
                    ).then(function () {
                        self.listLists(`${self.currentList ? 'editada' : 'agregada'}`);
                    })
                } else {
                    addClass(self.popUpSaveName, 'mage-error')
                }
            })
        }

        if (this.deleteBtn) {
            event(this.deleteBtn, 'click', function () {
                self.popUp.classList.add('active');
                self.html.classList.add('noscroll');
                removeClassAll(self.popUpModes, 'active')
                addClass(self.popUpDeleteMode, 'active')
            })

            event(this.popUpDeleteGo, 'click', function () {
                if (self.currentList) {
                    showLoader();
                    ajax('POST',
                        `/rest/V1/bananacodeShoppingList/delete/${self.currentList}`,
                        JSON.stringify({}),
                        'application/json',
                        null,
                        'Bearer ' + self.token.value
                    ).then(function () {
                        self.listLists('eliminada');
                    })
                }
            })
        }

        if (this.goBack) {
            event(this.goBack, 'click', function () {
                removeClassAll(self.containers, 'content-active');
                self.tabs[1].click();
                self.currentList = null;
                self.popUpSaveName.value = ''
            })
        }

        const searchShoppingList = () => {
            let items = selectArr('div.edit-list .line'),
                filteredItems = [],
                searchItems = [];

            if (!self.currentList) {
                items = selectArr('div.new-list .line')
            }

            items.map(item => {
                let input = selectDoc('input[name="searchProduct"]', item);
                if (input.value) {
                    if (selectDoc('.field.choice.checked', item)) {
                        filteredItems.push({
                            "v": input.value,
                            "s": true,
                        });
                    } else {
                        filteredItems.push({
                            "v": input.value,
                            "s": false,
                        });
                        searchItems.push(input.value);
                    }
                }
            });

            let data = {
                "name": !inputEmpty(self.popUpSaveName.value) ? self.popUpSaveName.value : 'Lista',
                "items": filteredItems,
                "list_id": self.currentList
            };

            showLoader();
            ajax('POST',
                `/rest/V1/bananacodeShoppingList/${self.currentList ? 'edit' : 'add'}`,
                JSON.stringify({
                    data: JSON.stringify(data)
                }),
                'application/json',
                null,
                'Bearer ' + self.token.value
            ).then(function (response) {
                let data = (JSON.parse(response));
                if (data.status === 200 && searchItems.length > 0) {
                    let list = JSON.parse(data.response)
                    self.currentList = list.id;
                    window.location.href = `/catalogsearch/result/?q=${searchItems[0]}&sp=${list.id}`
                } else {
                    showLoader(false);
                }
            })
        }
        if (this.searchBtn) {
            event(this.searchBtn, 'click', function () {
                searchShoppingList();
            })
        }

        if (this.searchUnSavedBtn) {
            event(this.searchUnSavedBtn, 'click', function () {
                searchShoppingList();
            })
        }

        this.popUpCancel.map(cancel => {
            event(cancel, 'click', function () {
                self.popUp.classList.remove('active');
                self.html.classList.remove('noscroll');
                removeClassAll(self.popUpModes, 'active')
            })
        })

        event(document, 'keypress', function (e) {
            let keyNum;
            if (window.event) { // IE
                keyNum = e.keyCode;
            } else if (e.which) { // Netscape/Firefox/Opera
                keyNum = e.which;
            }

            if (keyNum === 13) {
                let nextInput = selectDoc(`.line:nth-child(${(self.currentLine + 1)}) .field.value input`, selectDoc('.content-active'));
                if (nextInput) {
                    self.currentLine++;
                    nextInput.focus();
                }
            }
        });

        this.initLines()
    }

    listLists(action = false) {
        let self = this;
        if (this.customerLists) {
            showLoader();
            ajax('GET',
                `/rest/V1/bananacodeShoppingList/getListByCustomer`,
                {},
                null,
                null,
                'Bearer ' + self.token.value
            ).then(function (response) {
                showLoader(true);
                let data = (JSON.parse(response));
                if (data.status === 200) {
                    let lists = JSON.parse(data.response);

                    self.customerLists.innerHTML = '';
                    if (lists.length > 0) {
                        //Append lists
                        lists.map(list => {
                            self.customerLists.innerHTML += `<div class="col col-sm-6 col-md-half col-lg-6 list-card">
                                                                <div class="card">
                                                                    <span class="bold-18">${list.name}</span>
                                                                    <span class="medium-12 count">Creada el ${list.created_at}</span>
                                                                    <button class="delete-list" data-list="${list.list_id}"></button>
                                                                    <button data-name="${list.name}" data-items='${list.items}' data-list="${list.list_id}" class="edit-list"></button>
                                                                </div>
                                                            </div>`
                        })

                        addClass(self.customerLists, 'active')

                        //Edit actions
                        selectArr('button.edit-list').map(edit => {
                            event(edit, 'click', function () {
                                self.currentList = edit.getAttribute('data-list')
                                self.popUpSaveName.value = edit.getAttribute('data-name')

                                //Clear lines
                                self.editLines.map(line => {
                                    removeClass(line, 'checked')
                                    let input = selectDoc('input', line);
                                    if (input) {
                                        input.value = '';
                                        input.checked = false;
                                    }
                                })

                                //Add lines
                                let contentActiveForm = selectDoc('form', self.editListContainer);
                                JSON.parse(edit.getAttribute('data-items'))
                                    .map((item, i) => {
                                        let line = selectDoc(`div.edit-list #page form .line:nth-child(${i + 1}) .value input`),
                                            checkboxField = selectDoc(`div.edit-list #page form .line:nth-child(${i + 1}) .field.choice`);

                                        if (!line || !checkboxField) {
                                            self.maxLines++;
                                            let newLine = document.createElement('div');
                                            newLine.className = 'line';
                                            newLine.setAttribute('data-line', self.maxLines);
                                            newLine.innerHTML = shoppingLine();
                                            contentActiveForm.appendChild(newLine);
                                            line = selectDoc(`div.edit-list #page form .line:nth-child(${i + 1}) .value input`);
                                            checkboxField = selectDoc(`div.edit-list #page form .line:nth-child(${i + 1}) .field.choice`);
                                        }

                                        line.value = item.v;
                                        if (item.s) {
                                            let checkbox = selectDoc('input', checkboxField)
                                            addClass(checkboxField, 'checked')
                                            checkbox.checked = false;
                                        }
                                    })

                                self.initLines()

                                //Show container
                                removeClassAll(self.containers, 'content-active');
                                addClass(self.editListContainer, 'content-active');

                                let firstInput = selectDoc('.line .field.value input', selectDoc('.content.edit-list'));
                                if (firstInput) {
                                    self.currentLine = 1;
                                    firstInput.focus();
                                }
                            })
                        });

                        //Delete actions
                        selectArr('button.delete-list').map(del => {
                            event(del, 'click', function () {
                                self.currentList = del.getAttribute('data-list')

                                //Shop popup
                                self.popUp.classList.add('active');
                                self.html.classList.add('noscroll');
                                removeClassAll(self.popUpModes, 'active')
                                addClass(self.popUpDeleteMode, 'active')
                            })
                        });
                    } else {
                        removeClass(self.customerLists, 'active');
                    }

                    if (action) {
                        removeClassAll(self.popUpModes, 'active')
                        self.popUpSuccessMode.innerHTML = shoppingListSuccess(action);
                        addClass(self.popUpSuccessMode, 'active')
                        self.tabs[1].click();
                        scroll({
                            top: 0,
                            behavior: "smooth"
                        });
                    }
                }
            })
        }
    }

    initSearch() {
        const scrollEvents = () => {
            let arrows = selectArr('span.chevron', self.searchContainer);
            arrows.map(arrow => {
                event(arrow, 'click', function () {
                    let step = -30,
                        limit = (window.innerWidth / 3),
                        scrollContainer = selectDoc('.list-search', self.searchContainer);

                    if (arrow.className.includes('right')) {
                        step = 30;
                    }
                    if (window.innerWidth <= 768) {
                        limit = (window.innerWidth - 40) / 1.5;
                    }
                    let scrollAmount = 0,
                        slideTimer = setInterval(function () {
                            scrollContainer.scrollLeft += step;
                            scrollAmount += 30;
                            if (scrollAmount >= limit) {
                                window.clearInterval(slideTimer);
                            }
                        }, 25);
                })
            });
        }

        let self = this;
        if (self.searchPage) {
            let params = new URL(document.URL).searchParams;
            if (params.get('q') && params.get('sp')) {
                showLoader();

                //List current list items
                ajax('GET', `/rest/V1/bananacodeShoppingList/${params.get('sp')}`)
                    .then(function (response) {
                        showLoader(true);
                        let data = (JSON.parse(response));
                        if (data.status === 200) {
                            let list = JSON.parse(data.response),
                                filterItems = [];
                            //Add items
                            if (list.items) {
                                JSON.parse(list.items)
                                    .map((item, i) => {
                                        filterItems.push({
                                            value: item.v,
                                            status: item.s,
                                            link: `/catalogsearch/result/?q=${item.v}&sp=${list.list_id}`
                                        })
                                    });

                                if(selectDoc('[class*="algolia"]')) {
                                    let shippingListAlgoliaInt = setInterval(function () {
                                        if(selectDoc('#kip-search-shopping-list-algolia')) {
                                            self.searchContainer = selectDoc('#kip-search-shopping-list-algolia');
                                            self.searchContainer.innerHTML = shoppingListSearch(list.name, params.get('q'), filterItems);
                                            scrollEvents();
                                            clearInterval(shippingListAlgoliaInt);
                                        }
                                    }, 300);
                                } else {
                                    self.searchContainer = selectDoc('#kip-search-shopping-list');
                                }

                                if(self.searchContainer) {
                                    self.searchContainer.innerHTML = shoppingListSearch(list.name, params.get('q'), filterItems);
                                    scrollEvents();
                                }
                            } else {
                                addClass(self.body, 'no-shopping')
                            }
                        } else {
                            addClass(self.body, 'no-shopping')
                        }
                    });
            } else {
                addClass(self.body, 'no-shopping')
            }
        }
    }
}

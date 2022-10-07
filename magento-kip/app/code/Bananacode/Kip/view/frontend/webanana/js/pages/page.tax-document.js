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
    removeClass, addClassAll,
    hasClass,
} from "../utils/wonder";

import {showLoader} from "../helpers/service.dialog";

import {taxDocuments, taxDocumentSearch, taxDocumentSuccess} from "../helpers/service.markup";

import {inputEmpty} from "../helpers/service.validator";

import {displayModalError} from "../helpers/service.dialog";

export class TaxDocument {
    /**
     * ShoppingDocuments constructor
     */
    constructor() {
        if (selectDoc('#tax-documents')) {
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
        this.listDocuments();
        this.loadCategories();
    }

    /**
     * Initialize class props
     */
    props() {
        this.tabs = selectArr("#customer-documents .tabs .tab");
        this.containers = selectArr(".content");

        this.tabsNew = selectArr("#tax-documents-popup .tabs .tab");
        this.docContainers = selectArr("#tax-documents-popup .doc");

        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.currentDocumentContainer = selectDoc(".content.current-documents");
        this.editDocumentContainer = selectDoc(".content.edit-document");
        this.lines = selectArr('.edit-document #page form .line .field');

        this.token = selectDoc("input[name='token']");
        this.customerId = selectDoc("input[name='customer_id']");

        this.customerDocumentsContainer = selectDoc('#customer-documents');
        this.customerDocuments = selectDoc('#customer-documents .list');

        this.popUp = selectDoc('#tax-documents-popup');
        this.saveBtns = selectArr("#save-document");

        this.popUpDelete = selectDoc('#tax-documents-popup-delete');
        this.popUpDeleteGo = selectDoc('#tax-documents-popup-delete button.primary-btn');

        this.currentDocument = null;
        this.goBack = selectDoc('#go-back');

        this.categories = selectDoc('#tax-documents-popup #document-categories');
        this.categoriesEdit = selectDoc('.edit-document form.edit #document-categories');
        this.fileInputs = selectArr('.field.file input');
        this.postDocument = selectArr(".post-document");
        this.createDocumentOpens = selectArr(".create-document");
        this.documentType = 'CCF';

        this.documentFrontImage = null;
        this.documentBackImage = null;
        this.currentDocumentFrontImage = selectDoc('#current-doc-img-front');
        this.currentDocumentBackImage = selectDoc('#current-doc-img-back');

        this.editDocumentIva = selectDoc('.edit-document form.edit .EIVA');
        this.editDocumentCredit = selectDoc('.edit-document form.edit .CCF');
    }

    initActions() {
        let self = this;

        if (this.popUpDeleteGo) {
            event(this.popUpDeleteGo, 'click', function (e) {
                e.preventDefault();
                if (self.currentDocument) {
                    showLoader();
                    ajax('POST',
                        `/rest/V1/bananacodeTaxDocument/delete/${self.currentDocument}`,
                        JSON.stringify({}),
                        'application/json',
                        null,
                        self.token ? 'Bearer ' + self.token.value : null
                    ).then(function () {
                        self.listDocuments(true);
                    })
                }
            })
        }

        if (this.goBack) {
            event(this.goBack, 'click', function (e) {
                e.preventDefault();
                removeClassAll(self.containers, 'content-active');
                self.currentDocumentContainer.classList.add('content-active');
                self.currentDocument = null;
                self.documentFrontImage = null;
                self.documentBackImage = null;
            })
        }

        this.fileInputs.map(fileInput => {
            event(fileInput, 'change', function (event) {
                if (fileInput.files && fileInput.files[0]) {
                    let filePreview = selectDoc(`.field.file .preview.${fileInput.className}`, fileInput.parentNode.parentNode),
                        filePreviewImg = selectDoc(`.field.file .preview.${fileInput.className} img`, fileInput.parentNode.parentNode),
                        filePreviewName = selectDoc(`.field.file .preview.${fileInput.className} .filename`, fileInput.parentNode.parentNode);


                    console.log(fileInput.parentNode.parentNode);
                    console.log(`.field.file .preview.${fileInput.className}`);
                    console.log(filePreview);
                    console.log(filePreviewImg);
                    console.log(filePreviewName);

                    filePreviewName.innerHTML = fileInput.files[0].name;
                    filePreview.classList.add('active');
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        if (hasClass(fileInput, 'front')) {
                            self.documentFrontImage = e.target.result;
                        } else {
                            self.documentBackImage = e.target.result;
                        }
                        filePreviewImg.src = e.target.result;
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            })
        });

        this.createDocumentOpens.map(createDocumentOpen => {
            event(createDocumentOpen, 'click', function (e) {
                e.preventDefault();
                self.currentDocument = null;
                self.popUp.classList.add('active');
                self.html.classList.add('noscroll');
                self.tabsNew[0].click();
            });
        });

        this.postDocument.map(post => {
            event(post, 'click', function (e) {
                e.preventDefault();

                let container = '#tax-documents-popup form.add';
                if (self.currentDocument) {
                    container = '.edit-document form.edit'
                }

                let inputData = selectArr(`${container} .doc.${self.documentType} input`),
                    textareaData = selectArr(`${container} .doc.${self.documentType} textarea`),
                    selectData = selectArr(`${container} .doc.${self.documentType} select`),
                    valid = true,
                    data = {
                        "customer_id": self.customerId ? self.customerId.value : '',
                    };

                data.document_type = self.documentType;
                inputData.map(input => {
                    if (inputEmpty(input.value)) {
                        input.classList.add('mage-error');
                        valid = false;
                    } else {
                        data[input.getAttribute('name')] = input.value;
                        input.classList.remove('mage-error')
                    }
                });

                selectData.map(select => {
                    if (inputEmpty(select.value)) {
                        select.classList.add('mage-error');
                        valid = false;
                    } else {
                        data[select.getAttribute('name')] = select.value;
                        select.classList.remove('mage-error')
                    }
                });

                textareaData.map(textarea => {
                    if (inputEmpty(textarea.value)) {
                        textarea.classList.add('mage-error');
                        valid = false;
                    } else {
                        data[textarea.getAttribute('name')] = textarea.value;
                        textarea.classList.remove('mage-error')
                    }
                });

                let fileInputParents = selectArr(`${container} .field.file.input`);
                data.document_image = [];
                fileInputParents.map(fileInputParent => {
                    let imgTypeResult;
                    if (hasClass(fileInputParent, 'front')) {
                        imgTypeResult = self.documentFrontImage;
                    } else {
                        imgTypeResult = self.documentBackImage
                    }

                    if (inputEmpty(imgTypeResult) && !self.currentDocument) {
                        fileInputParent.classList.add('error');
                        valid = false;
                    } else {
                        data.document_image.push(imgTypeResult);
                        fileInputParent.classList.remove('error');
                    }
                })

                if (self.currentDocument) {
                    data.document_id = self.currentDocument;
                }

                if (valid) {
                    showLoader();
                    ajax('POST',
                        `/rest/V1/bananacodeTaxDocument/${self.currentDocument ? 'edit' : 'add'}`,
                        JSON.stringify({
                            data: JSON.stringify(data)
                        }),
                        'application/json',
                        null,
                        self.token ? 'Bearer ' + self.token.value : null
                    ).then(function (response) {
                        let data = (JSON.parse(response));
                        if (data.status === 200) {
                            let result = JSON.parse(data.response);
                            if(result.status !== 200) {
                                if (result.output) {
                                    displayModalError(result.output)
                                }
                            }
                        }

                        showLoader(true);
                        self.html.classList.remove('noscroll');

                        inputData.map(input => {
                            let inputEvent = new Event('input');
                            input.value = '';
                            input.dispatchEvent(inputEvent);
                        });
                        selectData.map(select => {
                            let inputEvent = new Event('input');
                            select.selectedIndex = 0;
                            select.dispatchEvent(inputEvent);
                        });
                        textareaData.map(textarea => {
                            let inputEvent = new Event('input');
                            textarea.value = '';
                            textarea.dispatchEvent(inputEvent);
                        });

                        self.documentFrontImage = null;

                        self.fileInputs.map(fileInput => {
                            fileInput.value = '';
                            let filePreviews = selectArr('.field.file .preview', fileInput.parentNode.parentNode);
                            removeClassAll(filePreviews, 'active');
                        });

                        if (self.goBack) {
                            self.goBack.click();
                        } else {
                            removeClass(self.popUp, 'active');
                        }

                        if (self.tabs.length > 0) {
                            self.tabs[0].click();
                        } else {
                            removeClass(self.popUp, 'active');
                        }

                        self.listDocuments(true);
                    })
                }
            });
        });
    }

    listDocuments(action = false) {
        let self = this;
        if (this.customerDocuments && self.customerId) {
            showLoader();
            ajax('GET',
                `/rest/V1/bananacodeTaxDocument/getDocumentsByCustomer`,
                {},
                null,
                null,
                self.token ? 'Bearer ' + self.token.value : null
            ).then(function (response) {
                showLoader(true);
                self.html.classList.remove('noscroll');
                self.popUpDelete.classList.remove('active');
                let data = (JSON.parse(response));
                if (data.status === 200) {
                    let documents = JSON.parse(data.response);
                    if (documents.output.totalRecords > 0) {
                        self.customerDocuments.innerHTML = '';

                        //Append documents
                        documents.output.items.map(document => {
                            self.customerDocuments.innerHTML += `<div class="col col-sm-6 col-md-half col-lg-6 document-card document-${document.name} ${document.name === 'CCF' ? 'active' : ''}">
                                                                    <div class="card">
                                                                        <span class="bold-18 id status-${document.status}">${document.registry_number ?? document.id_number}</span>
                                                                        <span class="medium-12 count">${document.customer_name}</span>
                                                                        <button data-document="${document.document_id}" class="delete-document"></button>
                                                                        <button data-document="${document.document_id}" data-type="${document.name}" class="edit-document"></button>
                                                                    </div>
                                                                </div>`
                        });

                        addClass(self.customerDocumentsContainer, 'active');

                        //Edit actions
                        selectArr('button.edit-document').map(edit => {
                            event(edit, 'click', function (e) {
                                e.preventDefault();
                                self.currentDocument = edit.getAttribute('data-document');

                                let type = '';
                                if (edit.getAttribute('data-type') === 'CCF') {
                                    self.editDocumentIva.classList.remove('active')
                                    self.editDocumentCredit.classList.add('active')
                                    type = '.CCF';
                                    self.documentType = 'CCF';
                                } else {
                                    self.editDocumentIva.classList.add('active')
                                    self.editDocumentCredit.classList.remove('active')
                                    type = '.EIVA';
                                    self.documentType = 'EIVA';
                                }

                                showLoader();
                                ajax('GET',
                                    `/rest/V1/bananacodeTaxDocument/${self.currentDocument}`,
                                    {},
                                    null,
                                    null,
                                    self.token ? 'Bearer ' + self.token.value : null
                                ).then(function (response) {
                                    showLoader(true);
                                    self.html.classList.remove('noscroll');
                                    let data = (JSON.parse(response));
                                    if (data.status === 200) {
                                        let document = JSON.parse(data.response).output;
                                        for (let prop in document) {
                                            let input = selectDoc(`.edit-document form.edit ${type} [name="${prop}"]`),
                                                inputEvent = new Event('input');
                                            if (input) {
                                                if (prop == 'expiration_date') {
                                                    input.value = new Date(document[prop]).toISOString().split('T')[0];
                                                } else {
                                                    input.value = document[prop];
                                                }
                                                input.dispatchEvent(inputEvent);
                                            }
                                        }

                                        let currentImages = document.document_image ? JSON.parse(document.document_image) : '';
                                        self.currentDocumentFrontImage.src = currentImages[0];
                                        self.currentDocumentBackImage.src = currentImages[1];

                                        //Show container
                                        removeClassAll(self.containers, 'content-active');
                                        addClass(self.editDocumentContainer, 'content-active');
                                    }
                                })
                            })
                        });

                        //Delete actions
                        selectArr('button.delete-document').map(del => {
                            event(del, 'click', function (e) {
                                e.preventDefault();
                                self.currentDocument = del.getAttribute('data-document')
                                self.popUpDelete.classList.add('active');
                                self.html.classList.add('noscroll');
                            })
                        });
                    } else {
                        removeClass(self.customerDocumentsContainer, 'active');
                    }

                    if (action) {
                        if (self.tabs[0]) {
                            self.tabs[0].click();
                        }
                        removeClass(self.popUp, 'active');
                        scroll({
                            top: 0,
                            behavior: "smooth"
                        });
                    }
                }
            })
        } else {
            let taxDocumentsSelector = selectDoc('#tax-documents select#customer-documents'),
                taxTypesSelector = selectDoc('#tax-documents select#documents-types');
            if (taxDocumentsSelector) {
                ajax('GET',
                    `/rest/V1/bananacodeTaxDocument/getDocumentsByCustomer`
                ).then(function (response) {
                    let data = (JSON.parse(response));
                    if (data.status === 200) {
                        if (self.popUp) {
                            removeClass(self.popUp, 'active');
                        }

                        let documents = JSON.parse(data.response);
                        if (documents.output.totalRecords > 0) {
                            taxDocumentsSelector.innerHTML = '<option value="" class="active first">Selecciona un documento fiscal</option>';
                            documents.output.items.map(document => {
                                taxDocumentsSelector.innerHTML += `<option class="document-${document.name} ${document.name === 'CCF' ? 'active' : ''}" value="${document.document_id}">${document.registry_number ?? document.id_number}</option>`
                            });
                        }

                        let changeEvent = new Event('change');
                        taxTypesSelector.dispatchEvent(changeEvent);
                    }
                })
            }
        }
    }

    loadCategories() {
        let self = this;
        if (this.categories) {
            ajax('GET',
                `/rest/V1/bananacodeTaxCategories`,
                {},
                null,
                null,
                self.token ? 'Bearer ' + self.token.value : null
            ).then(function (response) {
                let data = (JSON.parse(response));
                if (data.status === 200) {
                    let categories = JSON.parse(data.response);
                    if (categories.output.totalRecords > 0) {
                        categories.output.items.map(category => {
                            if (self.categories) {
                                self.categories.innerHTML += `<option value="${category.entity_id}">${category.name}</option>`;
                            }
                            if (self.categoriesEdit) {
                                self.categoriesEdit.innerHTML += `<option value="${category.entity_id}">${category.name}</option>`;
                            }
                        });
                    }
                }
            })
        }
    }

    initTabs() {
        let self = this;
        this.tabsNew.map(tab => {
            event(tab, 'click', function (e) {
                e.preventDefault();
                removeClassAll(self.tabsNew, 'active');
                addClass(tab, 'active');
                let content = selectDoc(`#tax-documents-popup .doc.${tab.id}`);
                if (content) {
                    removeClassAll(self.docContainers, 'active');
                    addClass(content, 'active');
                    self.documentType = tab.id;
                }
            })
        });

        this.tabs.map(tab => {
            event(tab, 'click', function (e) {
                e.preventDefault();
                removeClassAll(self.tabs, 'active');
                addClass(tab, 'active');
                removeClassAll(selectArr('#customer-documents .list .document-card'), 'active');
                addClassAll(selectArr(`.${tab.id}`), 'active');
            })
        })
    }
}

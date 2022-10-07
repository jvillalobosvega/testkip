/*
 *
 * Add all your project HTML markups here
 *
 * */

import {selectDoc, selectArr, removeClass, addClass} from "../utils/wonder";

export const dialogMarkup = (data) => {
    return `<div class="modal">
            <div class="header">
                <h3 class="h3">${data.title}</h3>
            </div>
            <div class="content">
                ${data.message}
            </div>
            <div class="footer row">
                <div class="col col-sm-3 col-md-half col-lg-6">
                    <a href="${data.ctaUrl}" id="cta-notify" class="btn">${
        data.cta
    }</a>
                </div>
                <div class="col col-sm-3 col-md-half col-lg-6">
                    <a target="_blank" id="cancel-notify">${
        data.yesno ? "No" : "Cancel"
    }</a>
                </div>
            </div>
          </div>`;
};

export const captchaMarkup = (captchaContainer, siteKey) => {
    return `var onCaptchaSuccessCustom = function () {
                var event;
                try {
                    event = new Event('captchaSuccessCustom', {bubbles: true, cancelable: true});
                } catch (e) {
                    event = document.createEvent('Event');
                    event.initEvent('captchaSuccessCustom', true, true);
                }
                window.dispatchEvent(event);
            }

            var recaptchaCallbackCustom = function () {
                let widgetId = grecaptcha.render('${captchaContainer}', {
                    sitekey: ${siteKey},
                    size: (window.innerWidth > 320) ? 'normal' : 'compact',
                    callback: 'onCaptchaSuccessCustom',
                })
            ;};
            `;
};

export const wishlistPopup = () => {
    return `<div class="modal">
                  <div class="header"><span class="close-modal"></span></div>
                  <div class="logout mode content">
                      <h4 class="bold-18">¿Quieres ahorrar tiempo?</h4>
                      <p class="medium-14">Compra en línea con tus propias colecciones de favoritos.</p>
                      <a href="/customer/account/login/" class="primary-btn">Iniciar Sesión</a>
                      <hr/>
                      <p class="medium-14">¿Aún no tienes una cuenta?</p>
                      <a href="/customer/account/create/" class="secondary-btn">Crear Cuenta</a>
                  </div>
                  <div class="logged mode content">
                      <div class="sub-content active old">
                          <h4 class="bold-18">Guardar <span class="current-product"></span> en Favoritos</h4>
                          <form class="search">
                            <input name="search-wishes" id="search-wishes" placeholder="Buscar">
                          </form>
                          <p class="medium-14">Todas las colecciones</p>
                          <div id="wish-lists"></div>
                          <div id="create-wish-lists">
                            <button class="bold-18">Crear lista de favoritos</button>
                          </div>
                      </div>
                      <div class="sub-content new">
                          <h4 class="bold-18">Crear lista de favoritos</h4>
                          <p class="medium-14">Escribe el nombre para tu nueva colección de favoritos.</p>
                          <form class="add">
                            <input name="list-name" placeholder="Nombre">
                          </form>
                          <button class="secondary-btn">Cancelar</button>
                          <button class="primary-btn">Guardar</button>
                      </div>
                  </div>
          </div>`
}

export const loginPopup = (fk) => {
    return `<div class="modal">
                  <div class="header"><span class="close-modal"></span></div>
                  <div class="logout mode content active">
                      <p class="medium-14">Favor inicia sesión para agregar productos al carrito</p>
                      <form class="form form-login" action="" method="post" id="login-form" novalidate="novalidate">
                        <input name="form_key" type="hidden" value="${fk}" />
                        <input name="send" type="hidden" value="" />
                        <fieldset class="fieldset login" data-hasrequired="">
                            <div class="field email required">
                                <label class="label" for="email"><span>Correo Electrónico</span></label>
                                <div class="control">
                                    <input placeholder="Correo Electrónico" name="login[username]" value="" autocomplete="off" id="email" type="text" class="input-text" title="Correo Electrónico" data-validate="{required:true}" aria-required="true" />
                                </div>
                            </div>
                            <div class="field password required">
                                <label for="pass" class="label"><span>Contraseña</span></label>
                                <div class="control">
                                    <input placeholder="Contraseña" name="login[password]" type="password" autocomplete="off" class="input-text" id="pass" title="Contraseña" data-validate="{required:true}" aria-required="true" />
                                </div>
                            </div>
                            <div class="actions-toolbar">
                                <div class="primary">
                                    <button type="submit" class="action login primary" name="send" id="kip-ajax-login">
                                        <span>Iniciar Sesión</span>
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                      </form>
                      <hr/>
                      <p class="medium-14">¿Aún no tienes una cuenta?</p>
                      <a href="/customer/account/create/" class="secondary-btn">Crear Cuenta</a>
                  </div>
            </div>`
}

export const shoppingListSuccess = (action) => {
    return `<span class="icon"></span><h4 class="bold-18">Lista ${action} con éxito</h4>`
}

export const shoppingListSearch = (shoppingList, current, items) => {
    return `<h2 class="bold-24">Listas de búsqueda</h2>
              <div class="breadcrumbs breadcrumbs-searchlist">
                <ul class="items">
                    <li>
                        <a href="">
                          Listas de búsqueda
                        </a>
                    </li>
                    <li>
                        <a href="">
                          Mis listas de búsqueda
                        </a>
                    </li>
                    <li>
                        <a href="">
                          ${shoppingList}
                        </a>
                    </li>
                    <li>
                        <a href="">
                          Búsqueda de productos
                        </a>
                    </li>
                    <li>
                        <a href="">
                          ${current}
                        </a>
                    </li>
                </ul>
              </div>
              <div class="row">
                <span class="chevron left"></span>
                <ul class="list-search">
                  ${items.map(item => {
        return `<li class="bold-14 ${item.status ? 'checked' : ''} ${item.value === current ? 'current' : ''}"><a href="${item.link}">${item.value}</a></li>`
    }).join('')}
                </ul>
                <span class="chevron right"></span>
              </div>`;
}

export const customShippingOptions = () => {
    let html = ``;

    if (window.checkoutConfig) {
        let kippingConfig = window.checkoutConfig.kipping;

        html += `<div id="shipping-type">
              <p class="bold-14">Tipo de envío</p>
              <p class="medium-12">Selecciona el tipo de envío.</p>
              <div class="row">
                 ${kippingMethod(kippingConfig.scheduled, 'scheduled_kipping', 'kipping_scheduled')}
                 ${kippingMethod(kippingConfig.scheduled_today, 'scheduled_today_kipping', 'kipping_scheduled_today')}
                 ${kippingMethod(kippingConfig.scheduled_add, 'scheduled_add_kipping', 'kipping_scheduled_add')}
                 ${kippingMethod(kippingConfig.express, 'express_kipping', 'kipping_express')}
                 ${kippingMethod(kippingConfig.flash, 'flash_kipping', 'kipping_flash')}
                 ${kippingMethod(kippingConfig.scheduled_festivity, 'scheduled_festivity_kipping', 'kipping_scheduled_festivity')}
              </div>
            </div>`;
    }

    html += `<div id="packaging">
            <p class="medium-12">Selecciona el empaque de tu preferencia.</p>
            <div class="row">
                <div id="no-package" class="packaging-type">
                    <p class="medium-14">Sin empaque</p>
                    <p class="medium-10">Se entrega en jaba. El conductor la esperará de vuelta.</p>
                </div>
                <div id="package" class="packaging-type">
                    <p class="medium-14">Bolsas plásticas</p>
                    <p class="medium-10">Puedes retornarlas en tu próximo envio y nosotros las reciclamos.</p>
                </div>
            </div>
          </div>`;

    if (window.checkoutConfig) {
        html += `<div id="scheduled">
                    <p class="bold-14">Tiempo de entrega</p>
                    <div class="row">
                        <select id="scheduled-date">
                            <option class="medium-14" value="">Fecha de entrega</option>
                        </select>
                        <select id="scheduled-hours">
                             <option class="medium-14" value="">Horas disponibles</option>
                        </select>
                    </div>
                  </div>`;

        html += `<div id="scheduled_festivity">
                    <p class="bold-14">Tiempo de entrega</p>
                    <div class="row">
                        <select id="scheduled-date">
                            <option class="medium-14" value="">Fecha de entrega</option>
                        </select>
                        <select id="scheduled-hours">
                             <option class="medium-14" value="">Horas disponibles</option>
                        </select>
                    </div>
                 </div>`;
    }

    html += `<div id="shipping-estimated">
            <div class="row">
                <p class="medium-14">
                    Valor de envío
                </p>
                <p id="total">
                     $00
                </p>
            </div>
          </div>`;

    html += `<div id="order-note">
            <p class="bold-14">Agregar nota</p>
            <p class="medium-12">Agrega instrucciones especiales para tu pedido (es opcional)</p>
            <textarea class="medium-14" placeholder="Escribe aquí..."></textarea>
            <p class="medium-10">Quedan <span class="left">140</span> caracteres</p>
          </div>`;

    return html;
}

const kippingMethod = (settings, id, input) => {
    let isAvailable = selectDoc(`input.radio[value="${input}"]`);

    if(!settings.title || !settings.title) {
        return '';
    }

    return `<div id="${id}" class="shipping-type ${(!settings.active || !isAvailable) ? 'disabled' : ''}" data-price="${settings.price}">
                <p class="medium-14">${settings.title}</p>
                <p class="medium-10">${settings.disclaimer}</p>
            </div>`;
}

export const formatDollar = (value) => {
    return  (isNaN(value) || !value) ? '$0.00' : (parseFloat(value).toLocaleString('en-US', {style: 'currency', currency: 'USD',}));
}

export const nextStepShipping = () => {
    return `<button class="action primary">
                  Continuar
            </button>`
}

export const shippingSteps = () => {
    return `<button id="button-step1" class="active">
                <span class="bold-18 number">1</span>
                <span>Paso 1</span>
            </button>
            <button id="button-step2">
                <span class="bold-18 number">2</span>
                <span>Paso 2</span>
            </button>
            <p id="current-address-step-label" class="medium-16">Busca y señala tu ubicación en el mapa. Puedes mover el PIN o dar clic en el lugar.</p>`;
}

export const orderSummaryHtml = (data, simple = false) => {
    if (isNaN(parseFloat(data.subtotal))) {
        return '';
    }

    return !simple ? `
                    <div class="row">
                        <p class="bold-18">Subtotal:</p>
                        <p class="medium-16">${formatDollar(data.subtotal)}</p>
                    </div>
                    <div class="row">
                        <p class="bold-14">Descuento:</p>
                        <p class="medium-14 discount">${formatDollar(data.discount)}</p>
                    </div>
                    <div class="row">
                        <p class="bold-14">Kipuntos:</p>
                        <p class="medium-14 referral" style="color: #F9A000">${formatDollar(data.referral)}</p>
                    </div>
                    <div class="row" style="display: none">
                        <p class="bold-14">Puntos Kip:</p>
                        <p class="medium-14">${formatDollar(data.points)}</p>
                    </div>
                    <div class="row last">
                        <p class="bold-14">Envío:</p>
                        <p class="medium-14">${formatDollar(data.shipping)}</p>
                    </div>
                    <div class="row total">
                        <p class="bold-24">Total:</p>
                        <p class="medium-24 grand-total" data-total="${data.total}">${formatDollar(data.total)}</p>
                    </div>
                    <button id="kip-place-order">Realizar Pedido</button>
                    <p class="disclaimer">Únicamente reservamos fondos en tu tarjeta. Al facturar tu pedido, se realiza el cobro con el monto exacto de lo que recibirás.</p>` :
                    `<div class="row">
                        <p class="bold-14">Subtotal:</p>
                        <p class="medium-14">${formatDollar(data.subtotal)}</p>
                     </div>
                     <div class="row">
                        <p class="bold-14">Descuento:</p>
                        <p class="medium-14 discount">${formatDollar(data.discount)}</p>
                     </div>
                     <div class="row total">
                        <p class="bold-18">Total:</p>
                        <p class="medium-16">${formatDollar(data.total - data.shipping)}</p>
                    </div>`;
};

const taxDocumentsModal = () => {
    return `<div id="tax-documents-popup" class="modal-container">
                <div class="modal">
                    <div class="header">
                        <h4 class="bold-18">Agregar Documento</h4>
                        <span class="close-modal"></span>
                        <div class="tabs">
                            <button id="credit" class="active tab">Crédito Fiscal</button>
                            <button id="iva" class="tab"><?= $block->Exento de IVA</button>
                        </div>
                    </div>
                    <div class="content">
                        <form class="add">
                            <div class="doc credit active">
                                <div class="field">
                                    <input type="text" name="customer_name" placeholder="Nombre del contribuyente">
                                </div>
                                <div class="field">
                                    <select name="category_id" id="document-categories">
                                        <option value="">Categoría del contribuyente</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <input type="text" name="tax_identification_number" placeholder="Nº de Identificación tributaria (NIT)">
                                </div>
                                <div class="field">
                                    <input type="text" name="registry_number" placeholder="Nºde Registro (NCR)">
                                </div>
                                <div class="field">
                                    <textarea name="economic_activity" placeholder="GIRO/Actividad Económica"></textarea>
                                </div>
                                <div class="field">
                                    <textarea name="head_office_address" placeholder="Dirección de casa matriz"></textarea>
                                </div>
                            </div>
                            <div class="doc iva">
                                <div class="field">
                                    <input type="text" name="customer_name" placeholder="Nombre y apellidos">
                                </div>
                                <div class="field">
                                    <input type="text" onfocus="(this.type='date')" name="expiration_date" placeholder="Fecha de expiración">
                                </div>
                                <div class="field">
                                    <input type="text" name="id_number" placeholder="Número de Carnet ">
                                </div>
                                <div class="field">
                                    <input type="text" name="entity" placeholder="Entidad">
                                </div>
                            </div>

                            <div class="field file">
                                <div class="preview front">
                                    <img src="">
                                    <span class="filename medium-14"></span>
                                </div>
                            </div>
                            <div class="field file input front">
                                <input type="file" name="" class="front">
                                <div class="bold-18 cover">
                                    Subir imagen delantera
                                </div>
                            </div>

                            <div class="field file">
                                <div class="preview back">
                                    <img src="">
                                    <span class="filename medium-14"></span>
                                </div>
                            </div>
                            <div class="field file input back">
                                <input type="file" name="" class="back">
                                <div class="bold-18 cover">
                                    Subir imagen trasera
                                </div>
                            </div>

                            <button class="primary-btn post-document" id="add-document">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>
            </div>`;
};

export const taxDocuments = (documents) => {
    let ccf = documents.filter(function (e) {
        return e.name === 'CCF';
    });

    let eiva = documents.filter(function (e) {
        return e.name === 'EIVA';
    });

    return `<p class="bold-14">Documento Fiscal</p>
             <p class="medium-14">Seleccione el tipo de documento</p>
             <select id="documents-types">
                <option value="">Seleccionar</option>
                <option value="FCF">Factura</option>
                ${ccf.length > 0 ? '<option value="CCF">Crédito Fiscal</option>' : ''}
                ${eiva.length > 0 ? '<option value="EIVA">Exento de IVA</option>' : ''}
             </select>
             <div id="stored-tax-docs">
                <p class="medium-14">Elija sus datos guardados anteriormente.</p>
                 <select id="customer-documents">
                    <option value="" class="active first">Selecciona un documento fiscal</option>
                    ${documents.map(document => {
                        return `<option class="document-${document.name} ${document.name === 'CCF' ? 'active' : ''}" value="${document.document_id}">${document.registry_number ?? document.id_number}</option>`
                    }).join('')}
                 </select>
             </div>
             <p class="medium-12">Para Crédito Fiscal o Factura Exento de IVA, debes agregar la información en <a target="_blank" href="/kip/tax/document/">Mi Cuenta</a>. Estos deben de ser aprobados para su uso (max. 2 hrs en horario laboral). No se puede cambiar tipo de documento fiscal luego de hacer el pedido.</p>
             <!--<p class="medium-14">O configura una nuevo documento fiscal:</p>
             <button id="add-tax-document">Agregar</button>-->
             ${''/*taxDocumentsModal()*/}`;
};

export const referralsInputHTML = () => {
    return `<button class="bold-14 toggle">
                <svg width="23" height="21" viewBox="0 0 23 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.75 10.5C15.2245 10.5 17.4108 8.16984 17.625 5.30531C17.7314 3.86625 17.28 2.52422 16.3538 1.52719C15.4373 0.542344 14.1562 0 12.75 0C11.3325 0 10.0505 0.539062 9.14062 1.51781C8.22047 2.50734 7.77187 3.85219 7.875 5.30438C8.08547 8.16938 10.2712 10.5 12.75 10.5ZM22.4709 19.1138C22.0753 16.9191 20.8402 15.0755 18.8995 13.7817C17.1759 12.6328 14.992 12 12.75 12C10.508 12 8.32406 12.6328 6.60047 13.7812C4.65984 15.075 3.42469 16.9186 3.02906 19.1133C2.93859 19.6162 3.06141 20.1136 3.36609 20.4778C3.50429 20.6438 3.67781 20.7768 3.87399 20.8672C4.07016 20.9575 4.28404 21.0029 4.5 21H21C21.2161 21.0031 21.4301 20.9578 21.6265 20.8676C21.8228 20.7773 21.9965 20.6443 22.1348 20.4783C22.4386 20.1141 22.5614 19.6167 22.4709 19.1138ZM4.125 12V10.125H6C6.19891 10.125 6.38968 10.046 6.53033 9.90533C6.67098 9.76468 6.75 9.57391 6.75 9.375C6.75 9.17609 6.67098 8.98532 6.53033 8.84467C6.38968 8.70402 6.19891 8.625 6 8.625H4.125V6.75C4.125 6.55109 4.04598 6.36032 3.90533 6.21967C3.76468 6.07902 3.57391 6 3.375 6C3.17609 6 2.98532 6.07902 2.84467 6.21967C2.70402 6.36032 2.625 6.55109 2.625 6.75V8.625H0.75C0.551088 8.625 0.360322 8.70402 0.21967 8.84467C0.0790176 8.98532 0 9.17609 0 9.375C0 9.57391 0.0790176 9.76468 0.21967 9.90533C0.360322 10.046 0.551088 10.125 0.75 10.125H2.625V12C2.625 12.1989 2.70402 12.3897 2.84467 12.5303C2.98532 12.671 3.17609 12.75 3.375 12.75C3.57391 12.75 3.76468 12.671 3.90533 12.5303C4.04598 12.3897 4.125 12.1989 4.125 12Z" fill="#F9A000"/>
                </svg> Aplicar Kipuntos
            </button>
            <p class="medium-12">Saldo disponible&nbsp;<span class="available"></span></p>
            <div class="form">
                <input type="number" step=".01" min="0">
                <button class="apply">Aplicar</button>
            </div>
            <p class="bold-14 next-step">Método de Pago</p>`
}

export const shoppingLine = () => {
    return `<div class="field choice">
                <input type="checkbox" name="searchProductReady" />
            </div>
            <div class="field value">
                <input name="searchProduct"/>
            </div>`;
}

export const modalRestrictedHTML = (error = null) => {
    return `<div class="modal">
                <div class="mode active">
                    <span class="icon"></span>
                    <p class="medium-14">${error ? error : 'Error...'}</p>
                </div>
            </div>`
}

export const minicartSummaryItems = (qtyAdded, priceAdded) => {
    return `<p class="counter qty">
                <span class="cart-icon"></span>
                <span class="counter-number">${qtyAdded}</span>
                <span class="cart-subtotal"><span class="price">${formatDollar(priceAdded)}</span></span>
            </p>`
}

export const kipCartHtml = (cart) => {
    let totalQty = 0;
    Object.keys(cart).map(item => {
        totalQty += cart[item].q;
    });

    return `<div class="block-content">
                <p class="available-shippings">
                    <strong>Envíos disponibles:</strong>
                    <span class="scheduled">Programado</span> |
                    <span class="scheduled_today">Mismo día</span> |
                    <span class="scheduled_festivity">Pre-Order</span> <d>|</d>
                    <span class="express">Express</span> |
                    <span class="flash">Flash</span>
                </p>

                <span class="express-left">Express restantes: </span>
                <br/>
                <span class="flash-left">Flash restantes: </span>

                <div data-action="scroll" class="minicart-items-wrapper">
                    <ol id="mini-cart" class="minicart-items">
                        ${Object.keys(cart).map(item => {
                            return cart[item].q > 0 ?
                                `<li class="item product product-item kiptem-id-${item}" data-kip="${item}" data-role="product-item">
                                                            <div class="product">
                                                                <a class="product-item-photo" href="#">
                                                                    <span class="product-image-container">
                                                                        <span class="product-image-wrapper">
                                                                            <img class="product-image-photo" src="${cart[item].i}" alt="${cart[item].n}" style="width: auto; height: auto;">
                                                                        </span>
                                                                    </span>
                                                                </a>
                                                                <div class="product-item-details">
                                                                    <div class="product-item-name">
                                                                        <a href="#">
                                                                            ${cart[item].n}
                                                                        </a>
                                                                        ${cart[item].d}
                                                                    </div>

                                                                    <div class="details-qty qty">
                                                                        <label class="label">Cantidad</label>
                                                                        <span class="minus"></span>
                                                                        <input data-min="${cart[item].m}" data-cart-item="${item}" id="cart-item-${item}-qty" type="number" size="4" class="item-qty cart-item-qty" value="${cart[item].q}">
                                                                        <span class="add"></span>
                                                                    </div>

                                                                    <div class="product-item-pricing">
                                                                        <div class="price-container">
                                                                            <span class="price-wrapper">
                                                                                <span class="price-excluding-tax" data-label="Sin Impuesto">
                                                                                    <span class="minicart-price">
                                                                                        <span class="price">${formatDollar(cart[item].p * cart[item].q)}</span>
                                                                                    </span>
                                                                                </span>
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="product actions">
                                                                        <div class="secondary">
                                                                            <a href="#" class="action kip-delete" data-cart-item="${item}">
                                                                                <span data-bind="i18n: 'Remove'">Remover</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                         </li>` : '';
                        }).join('')}
                    </ol>
                </div>
            </div>`
}

export const kipImpulseHtml = (impulse) => {
    return `<h4>🍫 Gustitos para agregar:</h4>
            <ol>
                ${Object.keys(impulse).map(item => {
                   return `<li>
                                <div class="impulse-product">
                                    <img src="${impulse[item].i}" alt="${impulse[item].n}">
                                    <div class="name">
                                        <p>${impulse[item].n}</p>
                                        ${impulse[item].d}
                                        <p>${formatDollar(impulse[item].p)}</p>
                                    </div>
                                    <div class="atc add-to-cart">
                                        <input class="atc" type="hidden" name="product" value="${item}">
                                        <div class="atc qty-controls">
                                            <span class="minus"></span>
                                            <input name="qty" class="atc" id="qty" type="number" value="${impulse[item].m}" data-min="${impulse[item].m}">
                                            <span class="add"></span>
                                        </div>
                                        <button class="action tocart primary"><span>Add to Cart</span></button>
                                    </div>
                                </div>
                          </li>`
                }).join('')}
            </ol>
            <div class="row total">
                <p class="bold-18">Subtotal:</p>
                <p class="medium-16 update"></p>
            </div>
            <a href="/checkout" class="checkout" data-role="proceed-to-checkout">Continuar a pago</a>`
}

/**
 *
 * @param cart
 * @param item
 * @returns {HTMLLIElement}
 */
export const kipCartItemHtml = (cart, item) => {
    let newItem = document.createElement('li');
    newItem.className = `item product product-item kiptem-id-${item}`;
    newItem.setAttribute('data-kip', item)
    newItem.innerHTML = `<div class="product">
                            <a class="product-item-photo" href="#">
                                <span class="product-image-container">
                                    <span class="product-image-wrapper">
                                        <img class="product-image-photo" src="${cart[item].i}" alt="${cart[item].n}" style="width: auto; height: auto;">
                                    </span>
                                </span>
                            </a>
                            <div class="product-item-details">
                                <div class="product-item-name">
                                    <a href="#">
                                        ${cart[item].n}
                                    </a>
                                    ${cart[item].d}
                                </div>

                                <div class="details-qty qty">
                                    <label class="label">Cantidad</label>
                                    <span class="minus"></span>
                                    <input data-min="${cart[item].m}" data-cart-item="${item}" id="cart-item-${item}-qty" type="number" size="4" class="item-qty cart-item-qty" value="${cart[item].q}">
                                    <span class="add"></span>
                                </div>

                                <div class="product-item-pricing">
                                    <div class="price-container">
                                        <span class="price-wrapper">
                                            <span class="price-excluding-tax" data-label="Sin Impuesto">
                                                <span class="minicart-price">
                                                    <span class="price">${formatDollar(cart[item].p * cart[item].q)}</span>
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="product actions">
                                    <div class="secondary">
                                        <a href="#" class="action kip-delete" data-cart-item="${item}">
                                            <span data-bind="i18n: 'Remove'">Remover</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;

    return newItem;
}

/**
 *
 * @param cart
 * @param container
 */
export const kipCartItemsHtml = (cart, container) => {
    //Add new items
    Object.keys(cart).map(cartSessionItem => {
        let htmlItem = selectDoc(`ol#mini-cart > li[data-kip="${cartSessionItem}"]`, container);
        if(!htmlItem) {
            let kipCartItems = selectDoc('ol#mini-cart', container)
            kipCartItems.appendChild(kipCartItemHtml(cart, cartSessionItem))
        }
    })

    //Update old items
    let htmlItems = selectArr(`ol#mini-cart > li`, container);
    htmlItems.map(htmlItem => {
        let id = htmlItem.getAttribute('data-kip');
        if (!cart[id]) {
            htmlItem.parentNode.removeChild(htmlItem)
        } else {
            let img = selectDoc(`.product-image-wrapper .product-image-photo`, htmlItem),
                qty = selectDoc(`#cart-item-${id}-qty`, htmlItem),
                price = selectDoc(`.product-item-pricing .minicart-price .price`, htmlItem),
                name = selectDoc('.product-item-name', htmlItem);

            let magCartItem = selectDoc(`#shopping-cart-table .kiptem-id-${id}`);

            if (img) {
                img.src = cart[id].i
            }

            if (price) {
                price.innerText = formatDollar(cart[id].p * cart[id].q)
            }

            if (qty) {
                qty.value = cart[id].q.toString().replace(/\.0+$/, '');
                qty.setAttribute('value', cart[id].q.toString().replace(/\.0+$/, ''));
                qty.dispatchEvent(new Event('change', {'bubbles': true}));
            }

            if (name) {
                let pst = selectDoc('.presentacion', name);
                if (!pst) {
                    name.innerHTML += cart[id].d
                } else {
                    pst.innerHTML = cart[id].d;
                }
            }

            //------------- MAG ITEM

            if (magCartItem) {
                let cartItemPrice = selectDoc('.subtotal .cart-price .price', magCartItem);
                if (cartItemPrice) {
                    cartItemPrice.innerText = formatDollar(cart[id].p * cart[id].q)
                }

                let cartItemQty = selectDoc('.qty input', magCartItem);
                if (cartItemQty) {
                    cartItemQty.value = cart[id].q.toString().replace(/\.0+$/, '');
                    cartItemQty.setAttribute('value', cart[id].q.toString().replace(/\.0+$/, ''));
                    cartItemQty.dispatchEvent(new Event('change', {'bubbles': true}));
                }
            }
        }
    })
}




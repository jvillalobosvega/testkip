import {event, observeAll, selectDoc} from "../utils/wonder";

export const initInput = (input) => {
    inputAnimatedLabel(input)

    event(input,'input', function () {
        inputAnimatedLabel(input)
    })

    observeAll(input, function (mutationsList) {
        inputAnimatedLabel(input)
    })
};

const inputAnimatedLabel = (input) => {
    if(input.value) {
        if(!selectDoc('span.input-floating-label', input.parentNode)) {
            let placeholder = input.getAttribute('placeholder') ?? input.getAttribute('title');
            if(!placeholder) {
                let label = selectDoc('label', input.parentNode) ?? selectDoc('label', input.parentNode.parentNode);
                if(label) {
                    placeholder = label.innerHTML.replace(/(<([^>]+)>)/gi, "");
                    input.setAttribute('placeholder', placeholder)
                }
            }

            let inputContainer = input.parentNode;

            if(placeholder &&
                (inputContainer.className.includes('field') || inputContainer.className.includes('control'))) {
                let span = document.createElement('span');
                span.className = 'input-floating-label';
                span.innerHTML = placeholder
                inputContainer.appendChild(span)
                inputContainer.style.position = 'relative';
                setTimeout(function () {
                    span.classList.add('show')
                },100)
            }
        }
    } else {
        let placeholder = input.getAttribute('placeholder') ?? input.getAttribute('title');

        if(!placeholder) {
            let label = selectDoc('label', input.parentNode) ?? selectDoc('label', input.parentNode.parentNode);
            if(label) {
                input.setAttribute('placeholder', label.innerHTML.replace(/(<([^>]+)>)/gi, "").trim())
            }
        } else {
            if(!input.getAttribute('placeholder')) {
                input.setAttribute('placeholder', placeholder)
            }
        }

        let floatingLabel = selectDoc('span.input-floating-label', input.parentNode);
        if(floatingLabel) {
            floatingLabel.parentNode.removeChild(floatingLabel);
        }
    }
}

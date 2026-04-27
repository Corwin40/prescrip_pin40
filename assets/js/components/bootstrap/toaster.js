import * as bootstrap from "bootstrap";

export function toasterMessage(message) {
    const options = { animation: true, autohide: true, delay: 3000 };
    const toastHTMLElement = document.getElementById('toaster');
    toastHTMLElement.querySelector('.toast-body').innerHTML = message;
    new bootstrap.Toast(toastHTMLElement, options).show();   // eslint-disable-line no-new
}
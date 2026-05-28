import * as bootstrap from "bootstrap";

export function toasterMessage(message) {
    const options = { animation: true, autohide: true, delay: 3000 };
    const toastHTMLElement = document.getElementById('toaster');

    const defaultHTML = `
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <small>à l'instant</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Hello, world! This is a toast message.
        </div>
    `;

    toastHTMLElement.querySelector('.toast-body').innerHTML = message;

    const toast = new bootstrap.Toast(toastHTMLElement, options);

    toastHTMLElement.addEventListener('hidden.bs.toast', () => {
        toastHTMLElement.innerHTML = defaultHTML;
        toastHTMLElement.className = 'toast';   // remet uniquement la classe "toast"
    }, { once: true });  // "once" pour éviter l'accumulation des listeners

    toast.show();   // eslint-disable-line no-new
}

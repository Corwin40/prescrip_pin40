import * as bootstrap from 'bootstrap';

export function removeOptions(selectElement) {
    for (let i = selectElement.options.length - 1; i >= 0; i -= 1) {
        selectElement.remove(i);
    }
}

export function initModal(id = 'modal') {
    const modalEl = document.getElementById(id);
    if (!modalEl) return null;
    return { modalEl, modal: new bootstrap.Modal(modalEl) };
}

export function parseModalTrigger(e) {
    e.preventDefault();
    const a = e.currentTarget;
    const [crud, contentTitle, option] = a.dataset.bsData.split('-');
    return { url: a.href, crud, contentTitle, option };
}

// Remet la modale dans son état par défaut avant d'y injecter un nouveau contenu.
// Sans ça, les classes/attributs posés par un type de modale (ex: setupIframeModal)
// restent collés sur les ouvertures suivantes, même d'un type de modale différent.
export function resetModal(modalEl) {
    const title = modalEl.querySelector('.modal-title');
    const dialog = modalEl.querySelector('.modal-dialog');
    const body = modalEl.querySelector('.modal-body');
    const footerLink = modalEl.querySelector('.modal-footer a');

    if (title) {
        title.classList.remove('d-none', 'text-danger');
    }
    if (dialog) {
        dialog.classList.remove('modal-xl', 'modal-lg');
    }
    if (body) {
        body.classList.remove('p-0', 'p-1');
        body.innerHTML = '';
    }
    if (footerLink) {
        footerLink.classList.remove('d-none', 'btn-outline-danger');
        footerLink.classList.add('btn-outline-primary');
        footerLink.href = '#';
        footerLink.textContent = '';
    }
}

export function setupIframeModal(modalEl, modal, url) {
    modalEl.querySelector('.modal-title').classList.add('d-none');
    modalEl.querySelector('.modal-dialog').classList.add('modal-xl');
    modalEl.querySelector('.modal-body').classList.add('p-0');
    modalEl.querySelector('.modal-body').innerHTML = '<iframe src="" width="100%" height="600px"></iframe>';
    modalEl.querySelector('.modal-body iframe').src = url;
    modalEl.querySelector('.modal-footer a').classList.add('d-none');
    modal.show();
}

export function bindModalEvents(openFn, submitFn = null) {
    document.querySelectorAll('.openModal').forEach(link => {
        link.addEventListener('click', openFn);
    });
    if (submitFn) {
        const btnSubmit = document.getElementById('btnSubmitModal');
        if (btnSubmit) btnSubmit.addEventListener('click', submitFn);
    }
}

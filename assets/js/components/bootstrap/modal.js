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

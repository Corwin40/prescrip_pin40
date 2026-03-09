import * as bootstrap from 'bootstrap';
import axios from 'axios';

export function initIndex_Dashboard() {
    console.log('Bonjour, vous êtes sur la page dédiée à l\'accueil.')

    const modalEl = document.getElementById('modal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);



    /** reset modal automatique après fermeture */
    modalEl.addEventListener('hidden.bs.modal', () => {
        const deleteUrl = modal.dataset.deleteUrl;
        if (deleteUrl) {
            axios.post(deleteUrl)
                .then(() => console.log('Entité temporaire supprimée'))
                .catch(err => console.error('Erreur lors de la suppression', err));

            // Nettoyage de la valeur
            delete modal.dataset.deleteUrl;
        }

        modalEl.querySelector('.modal-dialog').classList.remove('modal-lg', 'modal-xl');
        modalEl.querySelector('.modal-body').innerHTML = `
              <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading…</span>
                </div>
              </div>`;
        modalEl.querySelector('.modal-footer').innerHTML = '\n' +
            '<a href="#" type="button" class="btn btn-sm btn-primary btnModalSubmit">Ajouter</a>\n' +
            '<button type="button" class="btn btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>';
    });

    function openModal(e){
        e.preventDefault()

        let a = e.currentTarget;
        let url = a.href;
        const [crud, contentTitle, option] = a.dataset.bsData.split('-');
        modalEl.querySelector('.modal-title').classList.add('d-none');
        if(crud === "VIEW_PRESCRIPTION")
        {
            modalEl.querySelector('.modal-dialog').classList.add('modal-xl');
            modalEl.querySelector('.modal-body').classList.add(('p-0'));
            modalEl.querySelector('.modal-body').innerHTML = '<iframe src="" width="100%" height="600px"></iframe>';
            modalEl.querySelector('.modal-body iframe').src = url;
            const footer = modalEl.querySelector('.modal-footer');
            const confirmBtn = footer.querySelector('a');
            confirmBtn.classList.add('d-none');
            modal.show();
        }
    }

    function reloadEvent(){
        let btnSubmitModal = document.getElementById('btnModalSubmit');
        let btnsOpenModal = document.querySelectorAll('.openModal');

        btnsOpenModal.forEach(function(link){
            link.addEventListener('click', openModal);
        });
    }

    reloadEvent();

}

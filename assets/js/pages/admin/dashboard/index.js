import * as bootstrap from 'bootstrap';
import {toasterMessage} from "../../../components/bootstrap/toaster";
import axios from 'axios';

export function initIndex_Dashboard() {
    console.log('Bonjour, vous êtes sur la page dédiée à l\'accueil.')

    const modalEl = document.getElementById('modal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);



    /** reset modal automatique après fermeture */
    modalEl.addEventListener('hidden.bs.modal', () => {
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

        if(crud === "VIEW_PRESCRIPTION") {
            modalEl.querySelector('.modal-title').classList.add('d-none');
            modalEl.querySelector('.modal-dialog').classList.add('modal-xl');
            modalEl.querySelector('.modal-body').classList.add(('p-0'));
            modalEl.querySelector('.modal-body').innerHTML = '<iframe src="" width="100%" height="600px"></iframe>';
            modalEl.querySelector('.modal-body iframe').src = url;
            const footer = modalEl.querySelector('.modal-footer');
            const confirmBtn = footer.querySelector('a');
            confirmBtn.classList.add('d-none');
            modal.show();
        }
        else if(crud === "UPLOAD_PRESCRIPTIONSIGNED"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-body').classList.add(('p-1'));
            const footer = modalEl.querySelector('.modal-footer');
            const confirmBtn = footer.querySelector('a');
            confirmBtn.href = url;
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.formView;
                })
                .catch(error => {console.log(error)})
            ;
            modal.show();
        }
    }

    function submitModal(e){
        e.preventDefault()
        let modalContent = e.currentTarget.parentNode.parentElement;
        let form = modalContent.querySelector('form');
        let action = form.action
        let data = new FormData(form)
        axios
            .post(action, data)
            .then(({data}) => {
                modal.hide()
                toasterMessage(data.message);
                reloadEvent();
            })
            .catch(error => {
                console.log(error)
            })
    }

    function reloadEvent(){
        let btnSubmitModal = document.getElementById('btnSubmitModal');
        let btnsOpenModal = document.querySelectorAll('.openModal');

        btnsOpenModal.forEach(function(link){
            link.addEventListener('click', openModal);
        });
        btnSubmitModal.addEventListener('click', submitModal);
    }

    reloadEvent();

}

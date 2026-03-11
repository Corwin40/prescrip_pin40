import * as bootstrap from 'bootstrap';
import {toasterMessage} from "../../../components/bootstrap/toaster";
import axios from 'axios';

export function initIndex_Member() {
    console.log('Bonjour, vous êtes sur la page dédiée à la gestion des Membres.')

    const modalEl = document.getElementById('modal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    modalEl.addEventListener('hidden.bs.modal', () => {
        // Remise à zero HTML de la modal
        modalEl.querySelector('.modal-dialog').classList.remove('modal-lg', 'modal-xl');
        modalEl.querySelector('.modal-body').innerHTML = `
              <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading…</span>
                </div>
              </div>`;
    });

    function openModal(e){
        e.preventDefault()

        let a = e.currentTarget;
        let url = a.href;
        const [crud, contentTitle, option] = a.dataset.bsData.split('-');

        if(crud === "RESETPASSWORD")
        {
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.formView;
                    const confirmBtn = modalEl.querySelector('.modal-footer a');
                    confirmBtn.textContent = 'Mettre à jour le password';
                    confirmBtn.href = url;
                    reloadEvent()
                })
                .catch(error => {
                    console.log(error)
                })
            modal.show();
        }
        else{
            reloadEvent()
            toasterMessage('une erreur est survenue');
        }
    }

    function submitModal(e){
        e.preventDefault()
        const listForm = ['form_ResetPassword'];
        let modalContent = e.currentTarget.parentNode.parentElement;
        let form = modalContent.querySelector('form');
        if(form){
            let nameForm = form.id;
            let action = form.action;
            let data = new FormData(form);
            if(listForm.includes(nameForm)){
                axios
                    .post(action, data)
                    .then(function({data}) {
                        modal.hide();
                        toasterMessage(data.message);
                        reloadEvent();
                    })
                    .catch(error => {
                        console.log(error)
                    })
            }
        }
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

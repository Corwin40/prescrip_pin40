import {toasterMessage} from "../../../components/bootstrap/toaster";
import * as bootstrap from 'bootstrap';
import axios from 'axios';

export function initAdmin_ListPrescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la liste des prescriptions pour responsable du dispositif.')

    const modalEl = document.getElementById('modal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    function openModal(e){
        e.preventDefault()
        let a = e.currentTarget;
        let url = a.href;
        const [crud, contentTitle, option] = a.dataset.bsData.split('-');
        if(crud === "UPLOADSIGNEDELEC")
        {
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
        else{
            reloadEvent()
            toasterMessage('une erreur est survenue');
        }
    }

    function reloadEvent(){
        //let btnSubmitModal = document.getElementById('btnModalSubmit');
        let btnsOpenModal = document.querySelectorAll('.openModal');

        btnsOpenModal.forEach(function(link){
            link.addEventListener('click', openModal);
        });
        //btnSubmitModal.addEventListener('click', submitModal);
    }

    reloadEvent();

}

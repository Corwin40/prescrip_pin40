import {toasterMessage} from "../../../components/bootstrap/toaster";
import * as bootstrap from 'bootstrap';
import axios from 'axios';

export function initIndex_Prescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la liste des prescriptions.')

    const modalEl = document.getElementById('modal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    function openModal(e){
        e.preventDefault()

        let a = e.currentTarget;
        let url = a.href;
        const [crud, contentTitle, option] = a.dataset.bsData.split('-');

        if(crud === "VIEW_PRESCRIPTION")
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
        else if(crud === "SUBMISSION_PRESCRIPTION_DOCUSEAL"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-dialog').classList.add('modal-lg');
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.view;
                    modalEl.querySelector('.modal-footer a').href = data.url
                    modalEl.querySelector('.modal-footer a').textContent = "Le document vient d'être signé par le bénéficiaire"
                })
                .catch()
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
        else if(crud === "SHOW_PRESCRIPTIONQRCODE"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-dialog').classList.add('modal-lg');
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.view;
                    modalEl.querySelector('.modal-footer a').href = data.url
                    modalEl.querySelector('.modal-footer a').textContent = "Le document vient d'être signé par le bénéficiaire"
                })
                .catch(error => {console.log(error)})
            ;
            modal.show();
        }
        else if(crud === "DEL_PRESCRIPTION"){
            modalEl.querySelector('.modal-title').innerText = contentTitle
            modalEl.querySelector('.modal-title').classList.add('text-danger');
            modalEl.querySelector('.modal-body').innerHTML = `
                <p class="mb-0">
                    <span class="text-danger">
                        <i class="fa-light fa-circle-exclamation"></i>&nbsp;Attention,
                    </span>
                    <br>
                    En cliquant sur le bouton ci-dessous, cette prescription sera supprimée définitivement.
                </p>`
            modalEl.querySelector('.modal-footer a').textContent = "Supprimer la prescription"
            modalEl.querySelector('.modal-footer a').classList.remove('btn-outline-primary')
            modalEl.querySelector('.modal-footer a').classList.add('btn-outline-danger')
            modalEl.querySelector('.modal-footer a').href = url
            modal.show();
        }
        else{
            reloadEvent()
            toasterMessage('une erreur est survenue');
        }
    }

    function submitModal(e){
        e.preventDefault()
        let modalContent = e.currentTarget.parentNode.parentElement;
        let form = modalContent.querySelector('form');
        if(form) {
            let action = form.action
            let data = new FormData(form)
            axios
                .post(action, data)
                .then(({data}) => {
                    let select = document.getElementById('prescription_beneficiaire')
                    removeOptions(select)
                    let label = data.beneficiaire ;
                    let value = data.value
                    const opt = new Option(label, value);
                    select.options.add(opt);
                })
                .catch(error => {
                    console.log(error)
                })
            modal.hide()
            reloadEvent()
        }
        else{
            e.preventDefault()
            let url = e.currentTarget.href
            axios
                .post(url)
                .then(({data}) => {
                    if(data.code === 422){
                        modal.hide()
                        let toaster = document.getElementById('toaster');
                        toaster.classList.add('bg-danger', 'text-white','border' ,'border-danger');
                        toasterMessage(data.message);
                    }
                    else{
                        modal.hide()
                        toasterMessage(data.message);
                    }
                })
                .catch()
            reloadEvent()
        }

    }

    function removeOptions(selectElement) {
        for (let i = selectElement.options.length - 1; i >= 0; i -= 1) {
            selectElement.remove(i);
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

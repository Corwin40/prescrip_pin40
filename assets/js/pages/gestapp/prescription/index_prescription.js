import {toasterMessage} from "../../../components/bootstrap/toaster";
import {initModal, parseModalTrigger, setupIframeModal, bindModalEvents, removeOptions} from "../../../components/bootstrap/modal";
import axios from 'axios';

export function initIndex_Prescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la liste des prescriptions.')

    const modalCtx = initModal();
    if (!modalCtx) return;
    const { modalEl, modal } = modalCtx;

    function openModal(e){
        const { url, crud, contentTitle } = parseModalTrigger(e);

        if(crud === "VIEW_PRESCRIPTION") {
            setupIframeModal(modalEl, modal, url);
        }
        else if(crud === "SUBMISSION_PRESCRIPTION_DOCUSEAL"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-dialog').classList.add('modal-lg');
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.view;
                    modalEl.querySelector('.modal-footer a').href = data.url;
                    modalEl.querySelector('.modal-footer a').textContent = "Le document vient d'être signé par le bénéficiaire";
                })
                .catch()
            modal.show();
        }
        else if(crud === "UPLOAD_PRESCRIPTIONSIGNED"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-body').classList.add('p-1');
            modalEl.querySelector('.modal-footer a').href = url;
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.formView;
                })
                .catch(error => { console.log(error) })
            modal.show();
        }
        else if(crud === "SHOW_PRESCRIPTIONQRCODE"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-dialog').classList.add('modal-lg');
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-body').innerHTML = data.view;
                    modalEl.querySelector('.modal-footer a').href = data.url;
                    modalEl.querySelector('.modal-footer a').textContent = "Le document vient d'être signé par le bénéficiaire";
                })
                .catch(error => { console.log(error) })
            modal.show();
        }
        else if(crud === "DEL_PRESCRIPTION"){
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            modalEl.querySelector('.modal-title').classList.add('text-danger');
            modalEl.querySelector('.modal-body').innerHTML = `
                <p class="mb-0">
                    <span class="text-danger">
                        <i class="fa-light fa-circle-exclamation"></i>&nbsp;Attention,
                    </span>
                    <br>
                    En cliquant sur le bouton ci-dessous, cette prescription sera supprimée définitivement.
                </p>`
            modalEl.querySelector('.modal-footer a').textContent = "Supprimer la prescription";
            modalEl.querySelector('.modal-footer a').classList.remove('btn-outline-primary');
            modalEl.querySelector('.modal-footer a').classList.add('btn-outline-danger');
            modalEl.querySelector('.modal-footer a').href = url;
            modal.show();
        }
        else{
            bindModalEvents(openModal, submitModal);
            toasterMessage('une erreur est survenue');
        }
    }

    function submitModal(e){
        e.preventDefault()
        let modalContent = e.currentTarget.parentNode.parentElement;
        let form = modalContent.querySelector('form');
        if(form) {
            let action = form.action;
            let data = new FormData(form);
            axios
                .post(action, data)
                .then(({data}) => {
                    let select = document.getElementById('prescription_beneficiaire');
                    removeOptions(select);
                    const opt = new Option(data.beneficiaire, data.value);
                    select.options.add(opt);
                })
                .catch(error => { console.log(error) })
            modal.hide();
            bindModalEvents(openModal, submitModal);
        }
        else{
            let url = e.currentTarget.href;
            axios
                .post(url)
                .then(({data}) => {
                    if(data.code === 422){
                        modal.hide();
                        let toaster = document.getElementById('toaster');
                        toaster.classList.add('bg-danger', 'text-white', 'border', 'border-danger');
                        toasterMessage(data.message);
                    }
                    else{
                        if(data.liste){
                            document.getElementById('liste').innerHTML = data.liste;
                        }
                        modal.hide();
                        toasterMessage(data.message);
                    }
                })
                .catch()
            bindModalEvents(openModal, submitModal);
        }
    }

    bindModalEvents(openModal, submitModal);
}

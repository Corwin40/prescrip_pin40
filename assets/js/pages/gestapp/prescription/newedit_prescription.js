import {toasterMessage} from "../../../components/bootstrap/toaster";
import {initModal, parseModalTrigger, bindModalEvents, removeOptions} from "../../../components/bootstrap/modal";
import axios from 'axios';

export function initNewEdit_Prescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la gestion des prescriptions.')

    const modalCtx = initModal();
    if (!modalCtx) return;
    const { modalEl, modal } = modalCtx;

    function openModal(e){
        const { url, crud, contentTitle } = parseModalTrigger(e);
        if(crud === "ADD_BENEFICIARY") {
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            axios
                .get(url)
                .then(({data}) => {
                    modalEl.querySelector('.modal-dialog').classList.add('modal-xl');
                    modalEl.querySelector('.modal-body').innerHTML = data.formView;
                    const confirmBtn = modalEl.querySelector('.modal-footer a');
                    confirmBtn.textContent = 'Ajouter le bénéficiaire';
                    confirmBtn.href = url;
                    bindModalEvents(openModal, submitModal);
                })
                .catch(error => { console.log(error) })
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
        let action = form.action;
        let data = new FormData(form);
        axios
            .post(action, data)
            .then(({data}) => {
                if(data.code === 422){
                    modalEl.querySelector('.modal-body').innerHTML = data.formView;
                    bindModalEvents(openModal, submitModal);
                }
                else{
                    let selectBeneficiaire = document.getElementById('prescription_beneficiaire');
                    removeOptions(selectBeneficiaire);
                    selectBeneficiaire.options.add(new Option(data.nameBeneficiaire, data.valueBeneficiaire));

                    let selectPrescripteur = document.getElementById('prescription_prescriptor');
                    if(selectPrescripteur !== null){
                        removeOptions(selectPrescripteur);
                        selectPrescripteur.options.add(new Option(data.namePrescripteur, data.valuePrescripteur));
                    }
                }
                toasterMessage();
                modal.hide();
                bindModalEvents(openModal, submitModal);
            })
            .catch(error => { console.log(error) })
    }

    bindModalEvents(openModal, submitModal);
}

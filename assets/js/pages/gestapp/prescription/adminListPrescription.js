import {toasterMessage} from "../../../components/bootstrap/toaster";
import {initModal, parseModalTrigger, setupIframeModal, bindModalEvents} from "../../../components/bootstrap/modal";

export function initAdmin_ListPrescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la liste des prescriptions pour responsable du dispositif.')

    const modalCtx = initModal();
    if (!modalCtx) return;
    const { modalEl, modal } = modalCtx;

    function openModal(e){
        const { url, crud } = parseModalTrigger(e);
        if(crud === "UPLOADSIGNEDELEC") {
            setupIframeModal(modalEl, modal, url);
        }
        else{
            bindModalEvents(openModal);
            toasterMessage('une erreur est survenue');
        }
    }

    bindModalEvents(openModal);
}

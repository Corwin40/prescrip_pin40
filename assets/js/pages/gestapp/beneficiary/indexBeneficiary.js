import {parseModalTrigger, bindModalEvents} from "../../../components/bootstrap/modal";

export function initIndex_Beneficiary() {
    console.log('Bonjour, vous êtes sur la page dédiée à la liste des bénéficiaires.')

    function openModal(e){
        const { url, crud, contentTitle } = parseModalTrigger(e);
    }

    function submitModal(e){
        e.preventDefault();
    }

    bindModalEvents(openModal, submitModal);
}
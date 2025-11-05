
import * as bootstrap from 'bootstrap';
import axios from 'axios';

export function initNewEdit_Prescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la gestion des prescriptions.')

    const btnAddBeneficiary = document.getElementById('btnAddBeneficiary')
    const modalBS = document.getElementById('modal');
    const modal = new bootstrap.Modal(modalBS)

    btnAddBeneficiary.addEventListener('click', openModal)

    let btnsSubmit = document.querySelectorAll('.btnSubmit')
    btnsSubmit.forEach(function(link){
        link.addEventListener('click', submitModal);
    });

    function openModal(e){
        e.preventDefault()
        let url = btnAddBeneficiary.href
        axios
            .get(url)
            .then(({data}) => {
                modalBS.querySelector('.modal-title').innerHTML = "Ajouter un nouvel bénéficiaire"
                modalBS.querySelector('.modal-footer .btnSubmit').href = url
                modalBS.querySelector('.modal-body').innerHTML = data.formView

            })
            .catch(error => {
                console.log(error)
            })
        modal.show()
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
    }

    function removeOptions(selectElement) {
        for (let i = selectElement.options.length - 1; i >= 0; i -= 1) {
            selectElement.remove(i);
        }
    }

}

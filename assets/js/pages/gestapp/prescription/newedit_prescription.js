
import * as bootstrap from 'bootstrap';
import axios from 'axios';

export function initNewEdit_Prescription() {
    console.log('Bonjour, vous êtes sur la page dédiée à la gestion des prescriptions.')

    const btnAddBeneficiary = document.getElementById('btnAddBeneficiary')
    const modalBS = document.getElementById('modal');
    const modal = new bootstrap.Modal(modalBS)

    btnAddBeneficiary.addEventListener('click', openModal)

    function openModal(e){
        e.preventDefault()
        let url = btnAddBeneficiary.href

        axios
            .get(url)
            .then(({data}) => {
                modalBS.querySelector('.modal-title').innerHTML = "Ajouter un nouvel bénéficiaire"
                modalBS.querySelector('.modal-footer .btnSubmit').href = url
                modalBS.querySelector('.modal-body').innerHTML = data.formView
                modal.show()
            })
            .catch(error => {
                console.log(error)
            })
    }

}

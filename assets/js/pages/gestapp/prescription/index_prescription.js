
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
        console.log(a)
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
        else{
            reloadEvent()
            toasterMessage('une erreur est survenue');
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

    function reloadEvent(){
        let btnSubmitModal = document.getElementById('btnModalSubmit');
        let btnsOpenModal = document.querySelectorAll('.openModal');

        btnsOpenModal.forEach(function(link){
            link.addEventListener('click', openModal);
        });
        //btnSubmitModal.addEventListener('click', submitModal);
    }

    reloadEvent();

}

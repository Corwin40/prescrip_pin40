import './bootstrap.js';
import './styles/app.css';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

import { initNewEdit_Prescription } from './js/pages/gestapp/prescription/newedit_prescription.js';

document.addEventListener('DOMContentLoaded', () => {
    initDropdowns();
    const page = document.body.dataset.page;
    switch (page) {
        case 'mac_admin_association_new':
        case 'mac_admin_association_edit':
            initNewEdit_Prescription();
            break;
        default:
            console.log('Page non reconnue ou pas de JS spécifique');
    }

});

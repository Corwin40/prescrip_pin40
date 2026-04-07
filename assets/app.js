/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
import 'bootstrap';
import './bootstrap.js';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import './styles/app.scss';

import { initNewEdit_Prescription } from "./js/pages/gestapp/prescription/newedit_prescription";
import { initIndex_Member } from "./js/pages/admin/member";
import {initIndex_Dashboard} from "./js/pages/admin/dashboard";

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;

    switch (page) {
        case 'app_admin_dashboard_index':
            initIndex_Dashboard();
            break;
        case 'app_admin_member_index':
            initIndex_Member();
            break;
        case 'app_gestapp_prescription_new':
        case 'app_gestapp_prescription_edit':
            initNewEdit_Prescription();
            break;
        default:
            console.log('Page non reconnue ou pas de JS spécifique');
    }
});

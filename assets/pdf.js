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
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import './styles/pdf.scss';

import {initNewEdit_Prescription} from "./js/pages/gestapp/prescription/newedit_prescription";


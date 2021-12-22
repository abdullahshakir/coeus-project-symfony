/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/seller/seller-admin.css';

import $ from 'jquery';

global.jQuery = $;
global.$ = $;
// start the Stimulus application
import 'bootstrap';

import './scripts/seller/popper.js/popper.min.js';
import './scripts/seller/jquery/jquery.min.js';
import './scripts/seller/jquery-ui/jquery-ui.min.js';
// import './scripts/seller/bootstrap/js/bootstrap.min.js';
import './scripts/seller/excanvas.js';
import './scripts/seller/waves.min.js';
import './scripts/seller/jquery.dataTables.min.js';
// import './scripts/seller/datatables/dataTables.bootstrap4.min.js';
import './scripts/seller/jquery-slimscroll/jquery.slimscroll.js';
// import './scripts/seller/modernizr.js';
import './scripts/seller/SmoothScroll.js';
// import './scripts/seller/jquery.mCustomScrollbar.concat.min.js';
import './scripts/seller/pcoded.min.js';
import './scripts/seller/vertical-layout.min.js';
// import './scripts/seller/custom-dashboard.js';
import './scripts/seller/script.js';
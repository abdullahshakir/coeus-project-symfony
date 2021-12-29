/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/buyer/app.css';

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

// start the Stimulus application
import 'bootstrap';

import './scripts/buyer/owl.carousel.min.js';
import './scripts/buyer/jquery-ui.min.js';
import './scripts/buyer/jquery.meanmenu.min.js';
import './scripts/buyer/slick.min.js';
import './scripts/buyer/jquery.countdown.min.js';
import './scripts/buyer/jquery.counterup.min.js';
import './scripts/buyer/jquery.barrating.min.js';
import './scripts/buyer/jquery.nice-select.min.js';
import './scripts/buyer/scrollUp.min.js';
import './scripts/buyer/main.js';
import './scripts/buyer/datatables.bootstrap4.min.js';
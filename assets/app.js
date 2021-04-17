/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'source-sans-pro/source-sans-pro.css';
import 'admin-lte/plugins/fontawesome-free/css/all.css';
import 'admin-lte/dist/css/adminlte.min.css';
import './styles/app.scss';

// start the Stimulus application
//import './bootstrap';

import $ from 'admin-lte/plugins/jquery/jquery.min';
global.$ = global.jQuery = $;

import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min';
import 'admin-lte/dist/js/adminlte.min';


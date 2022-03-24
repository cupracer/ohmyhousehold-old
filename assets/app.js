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

import 'admin-lte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css';
import 'admin-lte/plugins/select2/css/select2.min.css';
import 'admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css';
import 'admin-lte/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css';

global.$ = global.jQuery = $;

import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min';
import 'admin-lte/dist/js/adminlte.min';

import 'admin-lte/plugins/moment/moment.min';
import 'admin-lte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min';
import 'admin-lte/plugins/select2/js/select2.full.min';
import 'admin-lte/plugins/bootstrap-switch/js/bootstrap-switch.min';

$(document).ready(function () {
    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });

    let refreshToasts = require('./js/refreshToasts');
});
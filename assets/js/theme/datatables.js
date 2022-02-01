/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Datatables
import 'admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css';
import 'admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css';
import 'admin-lte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css';

import 'admin-lte/plugins/datatables/jquery.dataTables.min';
import 'admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min';
import 'admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min';
import 'admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min';
import 'admin-lte/plugins/datatables-buttons/js/dataTables.buttons.min';
import 'admin-lte/plugins/datatables-buttons/js/buttons.bootstrap4.min';
import 'admin-lte/plugins/jszip/jszip.min';
import 'admin-lte/plugins/pdfmake/pdfmake.min';
import 'admin-lte/plugins/pdfmake/vfs_fonts';
import 'admin-lte/plugins/datatables-buttons/js/buttons.html5.min';

export function generateDatatablesEditButton(url) {
    let output = '<div class="btn-group">';
    output+= '<a href="' + url + '" class="btn btn-xs btn-outline-primary">';
    output+= '<i class="fas fa-edit"></i>';
    output+= '</a>';
    output+= '</div>';

    return output;
}

export function generateDatatablesEditStateCheckbox(completed, url) {
    let checkedStr = completed === true ? 'checked="checked"' : '';

    return '<input type="checkbox" class="checkbox_transaction_state" ' +
        checkedStr +' data-json-url="' + url + '">';
}

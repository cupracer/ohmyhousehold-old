import {generateDatatablesEditButton} from "../../theme/datatables";

$(document).ready(function () {
    let datatable = $('#revenueAccounts');
    datatable.DataTable({
        paging: true,
        serverSide: true,
        lengthChange: true,
        searching: true,
        ajax: datatable.data('jsonUrl'),
        language: {
            url: datatable.data('i18nUrl')
        },
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[ 0, "asc" ]],
        columns: [
            {
                data: "name",
                defaultContent: "-",
            },
            {
                data: "iban",
                defaultContent: "-",
            },
            {
                data: "balance",
                class: "text-right min",
                defaultContent: "-",
                orderable: false,
            },
            {
                class: "min",
                searchable: false,
                orderable: false,
                defaultContent: "-",
                render: function (data, type, row) {
                    let editButton = '';

                    if(row['editLink']) {
                        editButton = generateDatatablesEditButton(row['editLink']);
                    }

                    return editButton;
                },
            },
        ],
    });
});

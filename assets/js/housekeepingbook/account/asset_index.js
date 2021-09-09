import {generateDatatablesEditButton} from "../../theme/datatables";

$(document).ready(function () {
    let datatable = $('#assetAccounts');
    datatable.DataTable({
        paging: true,
        serverSide: true,
        lengthChange: true,
        lengthMenu: [[50, 100, 250, 500, -1], [50, 100, 250, 500, "All"]],
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
                data: "accountType",
                defaultContent: "-",
            },
            {
                data: "iban",
                defaultContent: "-",
            },
            {
                data: "owners",
                defaultContent: "-",
                orderable: false,
                render: function (data, type, row) {
                    let owners = '';

                    for (let i=0; i<data.length; i++) {
                        owners+= data[i];
                        if(i < data.length-1) {
                            owners+= '\n';
                        }
                    }

                    return owners;
                },
            },
            {
                data: "initialBalance",
                class: "text-right min",
                defaultContent: "-",
                orderable: false,
            },
            {
                data: "initialBalanceDate",
                class: "text-center min",
                defaultContent: "-",
                orderable: false,
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

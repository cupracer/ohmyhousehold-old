import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#supplies');
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
                data: "category",
                defaultContent: "-",
            },
            {
                data: "minimumNumber",
                defaultContent: "-",
                class: "min text-center",
            },
            {
                data: "usageCount",
                defaultContent: "-",
                class: "min text-right",
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
                }
            },
        ],
    });
});

import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#bookingCategories');
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
                data: "usageCount",
                defaultContent: "-",
                class: "min text-right",
                orderable: false,
            },
            {
                data: "createdAt",
                defaultContent: "-",
                class: "min",
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

import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#products');
    datatable.DataTable({
        paging: true,
        serverSide: true,
        lengthChange: true,
        searching: true,
        ajax: datatable.data('jsonUrl'),
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
                data: "brand",
                defaultContent: "-",
            },
            {
                data: "ean",
                defaultContent: "-",
            },
            {
                data: "category",
                defaultContent: "-",
            },
            {
                data: "packaging",
                defaultContent: "-",
            },
            {
                data: "amount",
                defaultContent: "-",
                class: "min text-right",
                orderable: false,
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

import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#items');
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
        order: [[ 1, "asc" ]],
        columns: [
            {
                data: "product",
                defaultContent: "-",
            },
            {
                data: "brand",
                defaultContent: "-",
            },
            {
                data: "category",
                defaultContent: "-",
            },
            {
                data: "amount",
                defaultContent: "-",
                class: "min text-right",
            },
            {
                data: "purchaseDate",
                defaultContent: "-",
                class: "min text-center",
            },
            {
                data: "bestBeforeDate",
                defaultContent: "-",
                class: "min text-center",
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

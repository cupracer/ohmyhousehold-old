import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#items');
    datatable.DataTable({
        paging: true,
        serverSide: true,
        lengthChange: true,
        lengthMenu: [[50, 100, 250, 500, -1], [50, 100, 250, 500, "All"]],
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
                    let buttons = '';
                    let rowId = row['id'];

                    if(rowId) {
                        buttons += '<button id="checkout_button" class="btn btn-xs btn-outline-secondary mr-3" value="' + rowId + '" title="checkout"><i class="fas fa-shopping-basket text-secondary"></i></button>';
                    }

                    if(row['editLink']) {
                        buttons+= generateDatatablesEditButton(row['editLink']);
                    }

                    return buttons;
                }
            },
        ],
    });
});

import 'admin-lte/plugins/toastr/toastr.min.css';
import toastr from 'admin-lte/plugins/toastr/toastr.min';

import {generateDatatablesEditButton} from "../theme/datatables";

let datatable;

$(document).ready(function () {
    let tableToUse = $('#items');
    datatable = tableToUse.DataTable({
        paging: true,
        serverSide: true,
        lengthChange: true,
        lengthMenu: [[50, 100, 250, 500, -1], [50, 100, 250, 500, "All"]],
        searching: true,
        ajax: tableToUse.data('jsonUrl'),
        language: {
            url: tableToUse.data('i18nUrl')
        },
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[ 0, "asc" ]],
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

                    if(row['checkoutLink']) {
                        buttons += '<button id="checkout_button" class="btn btn-xs btn-outline-secondary" value="' + row['checkoutLink'] + '" title="checkout"><i class="fas fa-shopping-basket text-secondary"></i></button>';
                    }

                    return buttons;
                }
            },
            {
                class: "min",
                searchable: false,
                orderable: false,
                defaultContent: "-",
                render: function (data, type, row) {
                    let buttons = '';

                    if(row['editLink']) {
                        buttons+= generateDatatablesEditButton(row['editLink']);
                    }

                    return buttons;
                }
            },
        ],
    });

    $(document).on("click", "#checkout_button", function(){
        let button = $(this);
        let checkoutLink = button.val();
        button.prop('disabled', true);

        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "200",
            "hideDuration": "200",
            "timeOut": "5000",
            "extendedTimeOut": "3000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        $.ajax({
            url: checkoutLink,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                if(data.status === "success") {
                    datatable.draw();
                    toastr["success"](data.message + '<br>' + '<button id="checkin_button" class="btn btn-xs bg-white" value="' + data.checkinUrl + '" title="cancel"><i class="far fa-times-circle text-danger"> Cancel checkout</i></button>', "checked out");
                }else if(data.status === "error") {
                    toastr.options.timeOut = "5000"
                    toastr["error"](data.message, "error occurred");
                }
            }
        });
    });

    $(document).on("click", "#checkin_button", function(){
        let button = $(this);
        let checkinUrl = button.val();
        button.prop('disabled', true);

        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "200",
            "hideDuration": "200",
            "timeOut": "3000",
            "extendedTimeOut": "3000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        $.ajax({
            url: checkinUrl,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                if(data.status === "success") {
                    datatable.draw();
                    toastr["info"](data.message, "cancelled checkout");
                }else if(data.status === "error") {
                    toastr.options.timeOut = "5000"
                    toastr["error"](data.message, "error occurred");
                }
            }
        });
    });
});

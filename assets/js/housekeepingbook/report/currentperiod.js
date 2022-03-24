import {generateDatatablesEditStateCheckbox} from "../../theme/datatables";

$(document).ready(function () {
    let datatable = $('#transactions');
    datatable.DataTable({
        dom: 'f<"householdUsers">Brtip',
        buttons: [
            {
                extend: 'csvHtml5',
                exportOptions: {
                    modifier: {
                        search: 'none'
                    },
                    orthogonal: 'sort'
                }
            },
        ],
        paging: false,
        serverSide: false,
        lengthChange: true,
        searching: true,
        language: {
            url: datatable.data('i18nUrl')
        },
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[ 2, "asc" ]],
        "columnDefs":
        [
            {
                "targets": '_all',
                "createdCell": function (td, cellData, rowData) {
                    if(rowData[11]) {
                        $(td).css('font-style', 'italic');
                    }

                    if(rowData[1] === 'periodicDeposit' || rowData[1] === 'periodicWithdrawal' || rowData[1] === 'periodicTransfer' ) {
                        $(td).css('color', 'gray');
                    }

                    if(rowData[3] > Math.round(+new Date()/1000)) {
                        $(td).css('background-color', 'lightyellow');
                    }
                },
            },
            {
                "targets": [ 0, ],
                "class": "min",
                "searchable": false,
                "orderable": false,
                render: function (data, type, row) {
                    return generateDatatablesEditStateCheckbox(data, row[12]);
                },
            },
            {
                "targets": [ 1, ],
                "orderable": false,
                "class": "min text-right",
                "render": function (data, type) {
                    let output = '';

                    if(type ==="display"){
                        // if(row[10]) {
                        //     output+= '<i class="fas fa-user-lock text-yellow mr-2"></i>';
                        // }

                        switch (data) {
                            case "deposit":
                                output+= '<i class="text-green far fa-smile"></i>';
                                break;
                            case "withdrawal":
                                output+= '<i class="text-red far fa-frown"></i>';
                                break;
                            case "transfer":
                                output+= '<i class="far fa-meh"></i>';
                                break;
                            case "periodicDeposit":
                                output+= '<i class="text-lightgreen far fa-smile"></i>';
                                break;
                            case "periodicWithdrawal":
                                output+= '<i class="text-lightred far fa-frown"></i>';
                                break;
                            case "periodicTransfer":
                                output+= '<i class="text-gray far fa-meh"></i>';
                                break;
                            default:
                                output = data;
                        }
                    }else {
                        output = data;
                    }

                    return output;
                },
            },
            {
                "targets": [2,],
                "class": "min text-center",
                "render": function (data, type, row) {
                    if(type ==="display" || type === "filter"){
                        return data;
                    }else {
                        return row[3];
                    }
                },
            },
            {
                "targets": [3, ],
                "visible": false,
            },
            {
                "targets": [4, 5, ],
                "class": "min",
            },
            {
                "targets": [6, 7, 8, ],
            },
            {
                "targets": [ 9, ],
                "class": "min text-right",
                "render": function (data, type, row) {
                    const floatVal = parseFloat(row[9]);

                    if(type ==="display" || type === "filter"){
                        switch (row[1]) {
                            case "deposit":
                                return '<span class="text-green">' + data + '</span>';
                            case "withdrawal":
                                return '<span class="text-red">' + data + '</span>';
                            case "transfer":
                                return '<span class="text-black">' + data + '</span>';
                            case "periodicDeposit":
                                return '<span class="text-lightgreen">' + data + '</span>';
                            case "periodicWithdrawal":
                                return '<span class="text-lightred">' + data + '</span>';
                            case "periodicTransfer":
                                return '<span class="text-gray">' + data + '</span>';
                            default:
                                return data;
                        }
                    }else {
                        return floatVal;
                    }
                },
            },
            {
                "targets": [10, 11, 12, ],
                "visible": false,
            },
        ],
        // "footerCallback": function ( row, data, start, end, display ) {
        //     var api = this.api();
        //     var floatVal = function(i) {
        //         return parseFloat(i);
        //     }
        //
        //     var colNumber = 8;
        //
        //     var pageTotal = api
        //         .column(colNumber, {page: 'current'})
        //         .data()
        //         .reduce(function (a, b) {
        //             return floatVal(a) + floatVal(b);
        //         }, 0);
        //
        //     $( api.column(7).footer() ).html(
        //         (Math.round(pageTotal * 100)/100).toFixed(2)
        //     );
        // }
        initComplete: function(settings, json) {
            let householdUsers = $("div.householdUsers");
            householdUsers.html(datatable.data('householduserHtml'));
            householdUsers.css('float', 'left').addClass('mr-3');

            $('#periodical_report_member').select2({
                placeholder: '',
                theme: 'bootstrap4',
                allowClear: true,
            });

            $('#periodical_report_apply').addClass('float-right');

            // $('#transactions_filter').css('float', 'right');
            $('#transactions_filter').addClass('float-right');
        },
    });
});

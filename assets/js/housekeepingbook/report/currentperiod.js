$(document).ready(function () {
    let datatable = $('#transactions');
    datatable.DataTable({
        paging: false,
        serverSide: false,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[ 1, "asc" ]],
        "columnDefs":
        [
            {
                "targets": '_all',
                "createdCell": function (td, cellData, rowData, row, col) {
                    if(rowData[10]) {
                        $(td).css('font-style', 'italic');
                    }

                    if(rowData[0] === 'periodicDeposit' || rowData[0] === 'periodicWithdrawal' || rowData[0] === 'periodicTransfer' ) {
                        $(td).css('color', 'lightgray');
                    }

                    if(rowData[2] > Math.round(+new Date()/1000)) {
                        $(td).css('background-color', 'lightyellow');
                    }
                },
            },
            {
                "targets": [ 0, ],
                "orderable": false,
                "class": "min text-right",
                "render": function ( data, type, row, meta ) {
                    var output = '';

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
                                output+= '<i class="text-lightgray far fa-meh"></i>';
                                break;
                            default:
                                output+= data;
                        }
                    }else {
                        output+= data;
                    }

                    return output;
                },
            },
            {
                "targets": [1,],
                "class": "min text-center",
                "render": function ( data, type, row, meta ) {
                    if(type ==="display" || type === "filter"){
                        return data;
                    }else {
                        return row[2];
                    }
                },
            },
            {
                "targets": [2, ],
                "visible": false,
            },
            {
                "targets": [3, 4, ],
                "class": "min",
            },
            {
                "targets": [5, 6, 7, ],
            },
            {
                "targets": [ 8, ],
                "class": "min text-right",
                "render": function ( data, type, row, meta ) {
                    var floatVal = parseFloat(row[8]);

                    if(type ==="display" || type === "filter"){
                        switch (row[0]) {
                            case "deposit":
                                return '<span class="text-green">' + data + '</span>';
                            case "withdrawal":
                                return '<span class="red">' + data + '</span>';
                            case "transfer":
                                return '<span class="text-black">' + data + '</span>';
                            case "periodicDeposit":
                                return '<span class="text-lightgreen">' + data + '</span>';
                            case "periodicWithdrawal":
                                return '<span class="text-lightred">' + data + '</span>';
                            case "periodicTransfer":
                                return '<span class="text-lightgray">' + data + '</span>';
                            default:
                                return data;
                        }
                    }else {
                        return floatVal;
                    }
                },
            },
            {
                "targets": [9, 10, ],
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
    });
});
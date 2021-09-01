import {generateDatatablesEditButton} from "../../theme/datatables";

$(document).ready(function () {
    let datatable = $('#periodicTransferTransactions');
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
                data: "startDate",
                class: "min",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "endDate",
                class: "min",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "bookingInterval",
                class: "min",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "bookingDayOfMonth",
                class: "min",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "user",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "bookingCategory",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "source",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "destination",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "description",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "amount",
                class: "min text-right",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
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

import {generateDatatablesEditButton, generateDatatablesEditStateCheckbox} from "../../theme/datatables";

$(document).ready(function () {
    let datatable = $('#depositTransactions');
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
        order: [[ 1, "desc" ]],
        columns: [
            {
                data: "completed",
                class: "min",
                searchable: false,
                orderable: false,
                render: function (data, type, row) {
                    return generateDatatablesEditStateCheckbox(data, row['editStateLink']);
                },
            },
            {
                data: "bookingDate",
                class: "min",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "user",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "bookingCategory",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "source",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "destination",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "description",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
                    if(rowData.private) {
                        $(cell).css('font-style', 'italic');
                    }
                },
            },
            {
                data: "amount",
                class: "min text-right",
                defaultContent: "-",
                createdCell: function(cell, cellData, rowData) {
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

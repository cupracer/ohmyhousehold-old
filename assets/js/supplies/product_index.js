import {generateDatatablesEditButton} from "../theme/datatables";

$(document).ready(function () {
    let datatable = $('#products');
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
                render: function (data, type, row) {
                    let minCount = row['minimumNumber'];
                    let buttonColor = 'primary';
                    let buttonText = row['usageCount'];
                    let titleText = "current: " + row['usageCount'];

                    if(minCount) {
                        buttonText = row['usageCount'] + ' / ' + minCount;
                        titleText = "current: " + row['usageCount'] + ' / min: ' + minCount;
                    }

                    if(minCount && row['usageCount'] >= minCount) {
                        buttonColor = 'success';
                    }

                    if(minCount && row['usageCount'] < minCount && row['usageCount'] > 0) {
                        buttonColor = 'warning';
                    }

                    if(minCount && row['usageCount'] < minCount && row['usageCount'] === 0) {
                        buttonColor = 'danger';
                    }

                    let button = '<button class="btn btn-xs btn-block bg-gradient-' + buttonColor + '" title="' + titleText + '">' + buttonText + '</button>';

                    if(!minCount && row['usageCount'] === 0) {
                        return '';
                        // }else if(row['usageCount'] > 0) {
                        //     return '<a href="/supplies/supply_items/by_supply/' + row['id'] + '/">' + button + '</a>';
                    }else {
                        return button;
                    }
                }
            },
            {
                data: "orderValue",
                visible: false,
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

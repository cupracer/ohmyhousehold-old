$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $("#transfer_transaction_private").bootstrapSwitch();

    let bookingCategorySelect = $('#transfer_transaction_bookingCategory');
    bookingCategorySelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let sourceSelect = $('#transfer_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let destinationSelect = $('#transfer_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });
});

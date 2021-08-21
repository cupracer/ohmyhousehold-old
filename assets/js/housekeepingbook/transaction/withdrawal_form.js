$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $("#withdrawal_transaction_private").bootstrapSwitch();

    let bookingCategorySelect = $('#withdrawal_transaction_bookingCategory');
    bookingCategorySelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let sourceSelect = $('#withdrawal_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let destinationSelect = $('#withdrawal_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
        tags: true,
    });
});

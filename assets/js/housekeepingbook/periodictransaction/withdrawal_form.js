$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $('#datetimepicker2').datetimepicker({
        format: 'LT',
    });

    $("#periodic_withdrawal_transaction_private").bootstrapSwitch();

    let bookingCategorySelect = $('#periodic_withdrawal_transaction_bookingCategory');
    bookingCategorySelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let sourceSelect = $('#periodic_withdrawal_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let destinationSelect = $('#periodic_withdrawal_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
        tags: true,
    });
});

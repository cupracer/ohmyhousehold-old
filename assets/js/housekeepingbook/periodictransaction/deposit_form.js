$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $('#datetimepicker2').datetimepicker({
        format: 'LT',
    });

    $("#periodic_deposit_transaction_private").bootstrapSwitch();

    let bookingCategorySelect = $('#periodic_deposit_transaction_bookingCategory');
    bookingCategorySelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let sourceSelect = $('#periodic_deposit_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
        tags: true,
    });

    let destinationSelect = $('#periodic_deposit_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });
});

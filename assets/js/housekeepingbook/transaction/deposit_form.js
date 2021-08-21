$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $("#deposit_transaction_private").bootstrapSwitch();

    let bookingCategorySelect = $('#deposit_transaction_bookingCategory');
    bookingCategorySelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let sourceSelect = $('#deposit_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
        tags: true,
    });

    let destinationSelect = $('#deposit_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });
});

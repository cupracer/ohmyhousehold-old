$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $('#datetimepicker2').datetimepicker({
        format: 'LT',
    });

    $("#periodic_transfer_transaction_private").bootstrapSwitch();

    let sourceSelect = $('#periodic_transfer_transaction_source');
    sourceSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });

    let destinationSelect = $('#periodic_transfer_transaction_destination');
    destinationSelect.select2({
        placeholder: '',
        theme: 'bootstrap4',
    });
});

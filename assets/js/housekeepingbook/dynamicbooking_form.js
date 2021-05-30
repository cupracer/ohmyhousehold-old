$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $("#dynamic_booking_private").bootstrapSwitch();

    let bookingCategorySelect = $('#dynamic_booking_bookingCategory');
    bookingCategorySelect.select2({
        ajax: {
            placeholder: '',
            url: bookingCategorySelect.data('jsonUrl'),
            delay: 250,
        }
    });

    let accountHolderSelect = $('#dynamic_booking_accountHolder');
    accountHolderSelect.select2({
        ajax: {
            placeholder: '',
            url: accountHolderSelect.data('jsonUrl'),
            delay: 250,
        }
    });
});

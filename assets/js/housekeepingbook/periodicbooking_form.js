$(document).ready(function () {
    $('#datetimepicker1').datetimepicker({
        format: 'LT',
    });
    $('#datetimepicker2').datetimepicker({
        format: 'LT',
    });

    let bookingCategorySelect = $('#periodic_booking_bookingCategory');
    bookingCategorySelect.select2({
        ajax: {
            placeholder: '',
            url: bookingCategorySelect.data('jsonUrl'),
            delay: 250,
        }
    });

    let accountHolderSelect = $('#periodic_booking_accountHolder');
    accountHolderSelect.select2({
        ajax: {
            placeholder: '',
            url: accountHolderSelect.data('jsonUrl'),
            delay: 250,
        }
    });
});

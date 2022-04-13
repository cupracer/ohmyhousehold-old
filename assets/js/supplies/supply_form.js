$(document).ready(function () {
    let categorySelect = $('#supply_category');
    categorySelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
        allowClear: true,
    });
});

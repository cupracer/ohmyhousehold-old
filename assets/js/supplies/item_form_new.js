$(document).ready(function () {
    let productSelect = $('#item_product');
    let storageLocationSelect = $('#item_storageLocation');

    productSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
        ajax: {
            dataType: 'json',
            url: productSelect.data('json-url'),
            delay: 250,
        }
    });
    productSelect.select2('open');

    storageLocationSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
        ajax: {
            dataType: 'json',
            url: storageLocationSelect.data('json-url'),
            delay: 250,
        }
    });
});

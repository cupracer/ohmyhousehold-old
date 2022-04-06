$(document).ready(function () {
    let supplySelect = $('#product_supply');
    supplySelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
        tags: true,
        ajax: {
            dataType: 'json',
            url: supplySelect.data('json-url'),
            delay: 250,
        }
    });
    let brandSelect = $('#product_brand');
    brandSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
        tags: true,
        ajax: {
            dataType: 'json',
            url: brandSelect.data('json-url'),
            delay: 250,
        }
    });
    let measureSelect = $('#product_measure');
    measureSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
    });
    let packagingSelect = $('#product_packaging');
    packagingSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
    });
});

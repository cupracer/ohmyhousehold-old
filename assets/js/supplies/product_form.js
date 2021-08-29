$(document).ready(function () {
    let supplySelect = $('#product_supply');
    supplySelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
    });
    let brandSelect = $('#product_brand');
    brandSelect.select2({
        theme: 'bootstrap4',
        placeholder: '',
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

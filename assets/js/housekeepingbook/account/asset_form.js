$(document).ready(function () {
    let accountTypeSelect = $('#asset_account_accountType');
    accountTypeSelect.select2({
        theme: 'bootstrap4',
    });
    let ownersSelect = $('#asset_account_owners');
    ownersSelect.select2({
        placeholder: '',
        multiple: true,
        theme: 'bootstrap4',
    });
});

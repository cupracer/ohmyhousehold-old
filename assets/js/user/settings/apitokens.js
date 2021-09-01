$(document).ready(function () {
    $('#apiTokens').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
        language: {
            url: datatable.data('i18nUrl')
        },
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "columnDefs": [ {
            "targets": [ 4, ],
            "orderable": false,
            "searchable": false,
        } ]
    });
});

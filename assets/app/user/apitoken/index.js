$(document).ready(function () {
    $('#apiTokens').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
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

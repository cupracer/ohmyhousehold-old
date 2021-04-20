$(document).ready(function () {
    $('#apiTokens').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "columnDefs": [ {
            "targets": [ 3, ],
            "orderable": false,
            "searchable": false,
        } ]
    });
});

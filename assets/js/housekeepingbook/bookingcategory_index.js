$(document).ready(function () {
    $('#bookingCategories').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 0, "asc" ]],
        "columnDefs": [ {
            "targets": [ 2, ],
            "orderable": false,
            "searchable": false,
        } ]
    });
});

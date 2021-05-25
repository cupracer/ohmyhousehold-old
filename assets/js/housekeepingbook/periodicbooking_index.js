$(document).ready(function () {
    $('#periodicBookings').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 0, "desc" ]],
        "columnDefs": [ {
            "targets": [ 7, ],
            "orderable": false,
            "searchable": false,
        } ]
    });
});

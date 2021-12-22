jQuery(document).ready(function () {
    // Datatable 
    jQuery('#myTable').DataTable({
        "order": [
            [0, 'DESC']
        ],
        "bStateSave": true
    });
})
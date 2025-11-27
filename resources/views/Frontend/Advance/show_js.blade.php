<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    
    $(document).ready(function () {
        var advanceId = {{ $advance->id }};
        
        var table = $('.data-table-logs').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [{
                extend: 'csvHtml5',
                text: 'CSV',
                charset: 'UTF-8',
                bom: true,
                title: function () {
                    return 'Advance Logs - Advance ID ' + advanceId;
                },
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: function () {
                    return 'Advance Logs - Advance ID ' + advanceId;
                },
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: 'Print',
                title: function () {
                    return 'Advance Logs - Advance ID ' + advanceId;
                },
                exportOptions: {
                    columns: ':visible'
                }
            }],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('advance.show', $advance->id) }}",
                error: function (xhr, error, thrown) {
                    console.log(xhr.responseText);
                },
            },
            columns: [{
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'advance_amount',
                name: 'advance_amount'
            },
            {
                data: 'advance_date',
                name: 'advance_date'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'created_by',
                name: 'created_by'
            },
            {
                data: 'remark',
                name: 'remark'
            }]
        });
    });
</script>


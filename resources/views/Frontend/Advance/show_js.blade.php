<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    
    $(document).ready(function () {
       var advanceId = {{$advance->id}};
        
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
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }]
        });

        // Handle view application IDs button click
        $(document).on('click', '.view-app-ids', function() {
            var logId = $(this).data('log-id');
            var modal = $('#viewAppIdsModal');
            
            // Show loading state
            modal.find('#app-ids-list').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            modal.modal('show');

            // Fetch application IDs via AJAX
            $.ajax({
                url: "{{ route('advance.log.application-ids', ':logId') }}".replace(':logId', logId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        if (response.applications && response.applications.length > 0) {
                            html += '<div class="mb-3"><strong>Total Cases: ' + response.count + '</strong></div>';
                            html += '<div class="table-responsive">';
                            html += '<table class="table table-sm table-bordered table-hover">';
                            html += '<thead class="table-light">';
                            html += '<tr>';
                            html += '<th>App ID</th>';
                            html += '<th>Bank Name</th>';
                            html += '<th>Product Name</th>';
                            html += '<th>Product %</th>';
                            html += '<th>Disbursement Amount</th>';
                            html += '<th>Advance Amount</th>';
                            html += '<th>Disbursement Date</th>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody>';
                            response.applications.forEach(function(app) {
                                html += '<tr>';
                                html += '<td><strong>' + (app.app_id || '-') + '</strong></td>';
                                html += '<td>' + (app.bank_name || '-') + '</td>';
                                html += '<td>' + (app.product_name || '-') + '</td>';
                                html += '<td>' + (app.product_percent || '-') + '</td>';
                                html += '<td>₹' + (app.disburse_amount || '0.00') + '</td>';
                                html += '<td>₹' + (app.advance_payment_amount || '-') + '</td>';
                                html += '<td>' + (app.disbursement_date || '-') + '</td>';
                                html += '</tr>';
                            });
                            html += '</tbody>';
                            html += '</table>';
                            html += '</div>';
                        } else {
                            html = '<div class="text-muted">No application IDs found for this log entry.</div>';
                        }
                        modal.find('#app-ids-list').html(html);
                    } else {
                        modal.find('#app-ids-list').html('<div class="text-danger">Error: ' + (response.message || 'Failed to fetch application IDs') + '</div>');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Failed to fetch application IDs.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    modal.find('#app-ids-list').html('<div class="text-danger">' + errorMsg + '</div>');
                }
            });
        });

        // Admin: delete add-log (releases linked cases if any)
        $(document).on('click', '.delete-log-btn', function() {
            var logId = $(this).data('log-id');
            var logAmount = parseFloat($(this).data('log-amount') || 0).toFixed(2);

            if (!confirm('Delete this advance log of ₹' + logAmount + '? If this is case-based, linked cases will become available again.')) {
                return;
            }

            $.ajax({
                url: "{{ route('advance.log.destroy', ':logId') }}".replace(':logId', logId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert(response.message || 'Advance log deleted successfully.');
                    $('.data-table-logs').DataTable().ajax.reload();
                    window.location.reload();
                },
                error: function(xhr) {
                    var msg = 'Failed to delete advance log.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });
    });
</script>


<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';

    function getInitialStatusFromQuery() {
        var params = new URLSearchParams(window.location.search);
        var status = (params.get('status') || '').toLowerCase();
        var allowed = ['pending', 'approved', 'completed', 'rejected', 'cancelled'];
        return allowed.includes(status) ? status : '';
    }

    function load_data(status = '', channel_id = '', date_range = '') {
        var table2 = $('.data-table-2').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Transactions',
                    charset: 'UTF-8',
                    bom: true,
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Transactions',
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: 'Transactions',
                },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('transactions.index') }}",
                data: {
                    status: status,
                    channel_id: channel_id,
                    date_range: date_range
                },
                error: function(xhr, error, thrown) {
                    console.log(xhr.responseText);
                },
            },
            columns: [
                {
                    data: null,
                    name: 'srno',
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'transaction_id',
                    name: 'transaction_id'
                },
                {
                    data: 'channel_name',
                    name: 'channel_name'
                },
                {
                    data: 'gross_amount_display',
                    name: 'gross_amount_display'
                },
                {
                    data: 'tds_display',
                    name: 'tds_display'
                },
                {
                    data: 'advance_display',
                    name: 'advance_display'
                },
                {
                    data: 'net_payable_display',
                    name: 'net_payable_display'
                },
                {
                    data: 'created_date',
                    name: 'created_date'
                },
                {
                    data: 'status_display',
                    name: 'status_display'
                },
                {
                    data: 'rejection_reason_display',
                    name: 'rejection_reason_display',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
    };

    $(document).ready(function() {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        var initialStatus = getInitialStatusFromQuery();
        if (initialStatus) {
            $('#status').val(initialStatus).trigger('change');
        }

        if (initialStatus) {
            load_data(initialStatus);
        } else {
            load_data();
        }

        $('#filter').click(function() {
            var status = $('#status').val();
            var channel_id = $('#channel_id').val();
            var date_range = $('#date-range-picker').val();

            if (status || channel_id || date_range) {
                $('.data-table-2').DataTable().destroy();
                load_data(status, channel_id, date_range);
            } else {
                alert('Select at least one filter!');
            }
        });

        $('#refresh').click(function() {
            window.location.reload();
        });

        // Complete transaction (Checker)
        $(document).on('click', '.complete-btn', function() {
            var transactionId = $(this).data('id');
            if (!confirm('Are you sure you want to mark this transaction as completed?')) {
                return;
            }

            $.ajax({
                url: '/transactions/complete/' + transactionId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.success);
                    $('.data-table-2').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Error completing transaction.';
                    alert(msg);
                }
            });
        });

        // Reprocess rejected transaction (Checker)
        $(document).on('click', '.reprocess-btn', function() {
            var transactionId = $(this).data('id');
            if (!confirm('Are you sure you want to reprocess this transaction? The current transaction will be cancelled and distributions will be unlinked for reprocessing.')) {
                return;
            }

            // Create and submit a form for POST request
            var form = $('<form>', {
                method: 'POST',
                action: '/transactions/reprocess/' + transactionId
            });
            form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
            $('body').append(form);
            form.submit();
        });

        // Read More / Read Less toggle for rejection reason
        $(document).on('click', '.read-more-reason', function() {
            var $parent = $(this).closest('.reason-text');
            var $short = $parent.find('.reason-short');
            var $full = $parent.find('.reason-full');

            if ($full.is(':visible')) {
                $full.hide();
                $short.show();
                $(this).text('Read More');
            } else {
                $short.hide();
                $full.show();
                $(this).text('Read Less');
            }
        });
    });
</script>

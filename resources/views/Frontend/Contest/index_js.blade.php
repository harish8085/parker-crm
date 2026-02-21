<script>
    $.fn.dataTable.ext.errMode = 'none';

    $(document).ready(function() {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('contest.index') }}",
                data: function(d) {
                    d.bank_id = $('#bank_id').val();
                    d.channel_id = $('#channel_id').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [{
                    data: null,
                    name: 'srno',
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'application_no',
                    name: 'application_no'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name',
                    orderable: false
                },
                {
                    data: 'channel_name',
                    name: 'channel_name',
                    orderable: false
                },
                {
                    data: 'parent_name',
                    name: 'parent_name',
                    orderable: false
                },
                {
                    data: 'location',
                    name: 'location'
                },
                {
                    data: 'disbursement_date',
                    name: 'disbursement_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'loan_amt',
                    name: 'loan_amt'
                },
                {
                    data: 'contest_rate',
                    name: 'contest_rate'
                },
                {
                    data: 'contest_amt',
                    name: 'contest_amt'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#filter').on('click', function() {
            table.ajax.reload();
        });

        $('#refresh').on('click', function() {
            $('#bank_id').val('').trigger('change');
            $('#channel_id').val('').trigger('change');
            $('#from_date').val('');
            $('#to_date').val('');
            table.ajax.reload();
        });
    });
</script>

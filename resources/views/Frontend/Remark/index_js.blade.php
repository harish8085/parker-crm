
<script>
        $(document).ready(function() {
                // Set CSRF token globally for AJAX requests
                $.ajaxSetup({
                        headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });

                // Event delegation for dynamic elements
                $(document).on('click', '.delete-dsacode-btn', function() {
                        if (confirm('Are you sure you want to delete this Bank Code?')) {
                                performAjaxRequest('/remark-status/delete/' + $(this).data('dsacode-id'), 'DELETE', {}, function() {
                                        location.reload();
                                });
                        }
                });


                $(document).on('click', '.edit-dsacode-btn', async function() {
                        await performAjaxRequest('/remark-status/update/' + $(this).data('dsacode-id'), 'GET', {}, async function(response) {
                                await performAjaxRequest('/getProduct', 'POST', {
                                        bank_id: response.bank_id,
                                        group: response.group,
                                }, async function(response) {
                                        var select = $('#edit_product_id')
                                        select.empty().append($('<option>', {
                                                value: '',
                                                text: 'Select Product',
                                                disabled: true,
                                                selected: true
                                        }));

                                        $.each(response, function(key, value) {
                                                select.append($('<option>', {
                                                        value: value.id,
                                                        text: value.name
                                                }));
                                        });

                                });

                                setTimeout(() => {
                                        $('#edit_bank_id').val(response.bank_id)
                                        $('#edit_product_id').val(response.product_id)
                                        $('#edit_group').val(response.group)
                                        $('#edit_code').val(response.code)
                                }, 1000);

                        });
                        $('#editDSAForm').attr('action', '/remark-status/update/' + $(this).data('dsacode-id'));

                        setTimeout(() => {

                                $('#editDSAModal').modal('show')
                        }, 1000);

                });
               
                $('#editDSAForm').submit(function(event) {
                        event.preventDefault();
                        performAjaxRequest($(this).attr('action'), 'PUT', $(this).serialize(), function() {
                                location.reload();
                        });
                });

                function performAjaxRequest(url, type, data, successCallback) {
                        $.ajax({
                                url: url,
                                type: type,
                                data: data,
                                success: successCallback,
                                error: function(xhr) {
                                        console.log(xhr.responseText);
                                }
                        });
                }

                function populateDropdown(select, data, placeholder) {
                        select.empty().append($('<option>', {
                                value: '',
                                text: placeholder,
                                disabled: true,
                                selected: true
                        }));

                        $.each(data, function(key, value) {
                                select.append($('<option>', {
                                        value: value.id,
                                        text: value.name
                                }));
                        });
                }
        });
</script>


<!-- Date picker -->
<script type="text/javascript">
        $(document).ready(function() {
                // Initialize the date range picker
                $('#date-range-picker').daterangepicker({
                        opens: 'right',
                        locale: {
                                format: 'YYYY-MM-DD',
                                separator: ' to '
                        }
                });

                // Show or hide the date range picker based on the selected option
                $('#date').on('change', function() {
                        var val = this.value;
                        if (val == 'custom') {
                                $('.date_range').show(); // Show date range picker
                        } else {
                                $('.date_range').hide(); // Hide date range picker
                        }
                });
        });
</script>

<!-- Datatable -->
<script type="text/javascript">
                $.fn.dataTable.ext.errMode = 'none';

        function load_data(date = '', date_range = '') {

                var table = $('.data-table').DataTable({
                        debug: false, // Disable debugging

                        dom: 'Bfrtip<"bottom"l>',
                        lengthMenu: [[10, 25, 50, 100,500,-1],[10, 25, 50, 100,500,'All']], // Options for the "Show entries" dropdown
                       
                        processing: true,
                        serverSide: true,
                        ajax: {
                                url: "{{ route('remark.index') }}",
                                data: {
                                        date: date,
                                        date_range: date_range,
                                },
                                error: function(xhr, error, thrown) {
                                        console.log(xhr.responseText);
                                },
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
                                        data: 'title',
                                        name: 'title'
                                },
                                {
                                        data: 'status',
                                        name: 'status'
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
                load_data();

                $('.select').select2({
                        placeholder: "Select an option",
                        allowClear: true
                });

                $('#filter').click(function() {
                        var date = $('#date').val();
                        var date_range = $('#date-range-picker').val();
                       
                        if (date ) {
                                $('.data-table').DataTable().destroy();
                                load_data(date, date_range);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });
</script>
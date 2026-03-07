<script>
    $(document).on('change', '.status-toggle', function () {
        var toggleElement = $(this);
        var advanceId = toggleElement.data('advance-id');
        var isChecked = toggleElement.is(':checked');
        var action = isChecked ? 'activate' : 'deactivate';
        var actionText = isChecked ? 'activate' : 'deactivate';
        
        bootbox.confirm({
            message: 'Are you sure you want to ' + actionText + ' this advance?',
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-secondary'
                }
            },
            callback: function (result) {
                if (result) {
                    var url = isChecked ? '/advance/activate/' + advanceId : '/advance/deactivate/' + advanceId;
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            bootbox.alert({
                                message: 'Advance ' + actionText + 'd successfully.',
                                callback: function () {
                                    $('.data-table').DataTable().ajax.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            // Revert the toggle if there's an error - use the captured element directly
                            toggleElement.prop('checked', !isChecked);
                            
                            console.log(xhr.responseText);
                            bootbox.alert({
                                message: 'An error occurred while ' + actionText + 'ing the advance.',
                                className: 'bootbox-danger'
                            });
                        }
                    });
                } else {
                    // Revert the toggle if user cancels
                    toggleElement.prop('checked', !isChecked);
                }
            }
        });
    });

    // Admin: delete advance amount from listing
    $(document).on('click', '.advance-delete-btn', function () {
        var advanceId = $(this).data('advance-id');
        var remainingAmount = parseFloat($(this).data('remaining-amount') || 0);

        if (remainingAmount <= 0) {
            bootbox.alert('Whole advance amount is already settled in previous settlements. Nothing left to delete.');
            return;
        }

        bootbox.prompt({
            title: 'Enter delete amount (max ₹' + remainingAmount.toFixed(2) + ')',
            value: remainingAmount.toFixed(2),
            callback: function (result) {
                if (result === null) {
                    return;
                }

                var deleteAmount = parseFloat(result);
                if (isNaN(deleteAmount) || deleteAmount <= 0) {
                    bootbox.alert('Please enter a valid delete amount greater than 0.');
                    return;
                }

                $.ajax({
                    url: '/advance/delete/' + advanceId,
                    type: 'DELETE',
                    data: {
                        delete_amount: deleteAmount
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        bootbox.alert(response.message || 'Advance deleted successfully.', function () {
                            $('.data-table').DataTable().ajax.reload();
                        });
                    },
                    error: function (xhr) {
                        var msg = 'Failed to delete advance.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        bootbox.alert(msg);
                    }
                });
            }
        });
    });

</script>

<!-- Datatable -->
<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';
    function load_data(user_id = '') {

        var table = $('.data-table').DataTable({
            debug: false, // Disable debugging
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ], // Options for the "Show entries" dropdown
            buttons: [{
                extend: 'csvHtml5',
                text: 'CSV',
                charset: 'UTF-8',
                bom: true,
                title: function () {
                    return 'Advance Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (csv) {
                    var header = '';
                    var user_id = $("#user_id option:selected").html();
                    if (user_id) {
                        header += 'Channel Partner: ' + user_id + '\n';
                    }
                    return header + csv; // Prepend the filter information to the CSV content
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: function () {
                    return 'Advance Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml']; // Access the sheet XML
                    // Construct the custom header
                    var header = '';
                    var user_id = $("#user_id option:selected").html();
                    if (user_id) {
                        header += 'Channel Partner: ' + user_id + '\n';
                    }
                    // Add the header in the first row
                    var rows = $('row', sheet); // Get all rows
                    var firstRow = rows[0]; // Access the first row
                    var newRow = '<row r="1">' +
                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                        '</row><row r="2">' +
                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                        '</row>';

                    $(firstRow).before(newRow); // Insert the custom header row before the first row
                }
            },
            {
                extend: 'print',
                text: 'Print',
                title: function () {
                    return 'Advance Details';
                },
                exportOptions: {
                    columns: function (index, data, node) {
                        // Exclude the action column (assuming it's the last column)
                        return index !== table.column(':last').index();
                    }
                },
                customize: function (win) {
                    var filters = '';
                    var user_id = $("#user_id option:selected").html();
                    if (user_id) {
                        filters += '<h4>Filters Applied:</h4>';
                        filters += '<p>Channel Partner: ' + user_id + '</p>';
                    }

                    $(win.document.body).prepend(filters);
                }
            },
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('advance.index') }}",
                data: {
                    user_id: user_id,
                },
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
                data: 'user_id',
                name: 'user_id'
            },
            {
                data: 'advance_amount',
                name: 'advance_amount'
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

    $(document).ready(function () {
        load_data();

        // Initialize Select2 for regular selects
        $('.select').not('.user-select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        // Initialize Select2 with AJAX search for Channel Partner
        $('.user-select').select2({
            placeholder: 'Select Channel Partner',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('advance.users.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $('#filter').click(function () {
            var user_id = $('#user_id').val();

            if (user_id) {
                $('.data-table').DataTable().destroy();
                load_data(user_id);
            } else {
                bootbox.alert({
                    message: 'Please select a Channel Partner!',
                    className: 'bootbox-warning'
                });
            }
        });

        $('#refresh').click(function () {
            $('#user_id').val(null).trigger('change');
            $('.data-table').DataTable().destroy();
            load_data();
        });
    });
</script>


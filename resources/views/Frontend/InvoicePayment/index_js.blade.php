<script>
    $(document).ready(function() {
        // Initialize DataTable
        load_data();

        // Show/hide the action button based on selected checkboxes
        const $actionButton = $('#actionButton');

        function updateButtonVisibility() {
            const selectedCount = $('.rowCheckbox:checked').length;
            if (selectedCount > 0) {
                $actionButton.show();
            } else {
                $actionButton.hide();
            }
        }

        // Attach event handlers to row checkboxes using delegation
        $(document).on('change', '.rowCheckbox', function() {
            updateButtonVisibility();
        });

        // Initialize with button hidden
        $actionButton.hide();

        // Date range picker initialization
        $('#date-range-picker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        // Show or hide the date range picker based on the selected option
        $('#date').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.date_range').show();
            } else {
                $('.date_range').hide();
            }
        });

        // Master checkbox functionality for bulk selection
        $(document).on('change', '#masterCheckbox', function() {
            const isChecked = $(this).prop('checked');
            $('.rowCheckbox').prop('checked', isChecked);
            updateButtonVisibility();
        });

        // Filter button
        $('#filter').click(function() {
            const date = $('#date').val();
            const dateRange = $('#date-range-picker').val();
            const bankName = $('#bank_name').val();
            const paymentStatus = $('#payment_status').val();

            load_data(date, dateRange, bankName , paymentStatus);
        });

        // Refresh button
        $('#refresh').click(function() {
            window.location.reload();
        });

        // Edit button click - open modal with data
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const paymentPaid = $(this).data('payment-paid');
            const paymentDate1 = $(this).data('payment-date1');
            const paymentDate2 = $(this).data('payment-date2');
            const remainingAmount = $(this).data('remaining-amount');

            // Set form values
            $('#paymentId').val(id);
            $('#paymentPaid').val(paymentPaid || '');
            $('#paymentDate1').val(paymentDate1 || '');
            $('#paymentDate2').val(paymentDate2 || '');
            $('#remainingAmountDisplay').val(remainingAmount || '0');
            $('#useRemainingCheckbox').prop('checked', false);

            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
            modal.show();
        });

        // Checkbox to auto-fill payment paid with remaining amount
        $(document).on('change', '#useRemainingCheckbox', function() {
            if ($(this).is(':checked')) {
                const remainingAmount = $('#remainingAmountDisplay').val();
                $('#paymentPaid').val(remainingAmount);
            } else {
                $('#paymentPaid').val('');
            }
        });

        // Form submission
        $(document).on('submit', '#editPaymentForm', function(e) {
            e.preventDefault();

            const id = $('#paymentId').val();
            const paymentPaid = $('#paymentPaid').val();
            const paymentDate1 = $('#paymentDate1').val();
            const paymentDate2 = $('#paymentDate2').val();
            const remainingAmount = $('#remainingAmountDisplay').val();

            // Validate payment_paid is not greater than remaining amount
            if (parseFloat(paymentPaid) > parseFloat(remainingAmount)) {
                alert('Payment Paid cannot be greater than Remaining Amount!');
                return;
            }

            // Calculate new remaining amount
            const newRemainingAmount = parseFloat(remainingAmount) - parseFloat(paymentPaid);

            $.ajax({
                url: '{{ route("invoice_payment.update", ":id") }}'.replace(':id', id),
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    payment_paid: paymentPaid,
                    payment_date1: paymentDate1,
                    payment_date2: paymentDate2,
                    remaining_amount: newRemainingAmount
                },
                success: function(response) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editPaymentModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Show success message
                    alert('Invoice payment updated successfully!');

                    // Reload the DataTable
                    if ($.fn.dataTable.isDataTable('#bankMisTable')) {
                        $('#bankMisTable').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', xhr.responseText);
                    alert('An error occurred while updating the payment. Please try again.');
                }
            });
        });

        $('.select').select2({
            allowClear: true
        });
    });

    // DataTable initialization function
    $.fn.dataTable.ext.errMode = 'none';

    function load_data(date = '', dateRange = '', bankName = '' , paymentStatus = '') {
        if ($.fn.dataTable.isDataTable('#bankMisTable')) {
            $('#bankMisTable').DataTable().destroy();
        }

        var table = $('#bankMisTable').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [{
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Invoice-Details'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Invoice-Details'
                },
                {
                    extend: 'print',
                    text: 'Print'
                }
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("invoice_payment.index") }}',
                type: 'GET',
                data: function(d) {
                    d.date = date;
                    d.date_range = dateRange;
                    d.bank_name = bankName;
                    d.payment_status = paymentStatus;
                },
                error: function(xhr, error, thrown) {
                    console.log('DataTable Error:', error, thrown);
                }
            },
            columns: [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'group_name',
                    name: 'group_name'
                },
                {
                    data: 'mis_month',
                    name: 'mis_month'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'bank_address',
                    name: 'bank_address'
                },
                {
                    data: 'bank_hsn_code',
                    name: 'bank_hsn_code'
                },
                {
                    data: 'taxable_value',
                    name: 'taxable_value'
                },
                {
                    data: 'CGST',
                    name: 'CGST'
                },
                {
                    data: 'SGST',
                    name: 'SGST'
                },
                {
                    data: 'IGST',
                    name: 'IGST'
                },
                {
                    data: 'TDS',
                    name: 'TDS'
                },
                {
                    data: 'invoice_value',
                    name: 'invoice_value'
                },
                {
                    data: 'bank_gst_no',
                    name: 'bank_gst_no'
                },
                {
                    data: 'dsa_gst_no',
                    name: 'dsa_gst_no'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'payment_received_bank',
                    name: 'payment_received_bank'
                },
                {
                    data: 'payment_amount',
                    name: 'payment_amount'
                },
                {
                    data: 'payment_status',
                    name: 'payment_status'
                },
                {
                    data: 'payment_paid',
                    name: 'payment_paid'
                },
                {
                    data: 'remaining_amount',
                    name: 'remaining_amount'
                },
                {
                    data: 'payment_date1',
                    name: 'payment_date1'
                }, {
                    data: 'payment_date2',
                    name: 'payment_date2'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 25
        });

        return table;
    }
    

      $(document).ready(function() {
                load_data();

                $(document).on('change', '.remark-dropdown', function() {
                        let remark = $(this).val();
                        let id = $(this).data('id');

                        $.ajax({
                                url: '{{ url("/application/update/remark") }}',
                                method: 'POST',
                                data: {
                                        _token: '{{ csrf_token() }}',
                                        id: id,
                                        remark: remark,
                                },
                                success: function(response) {
                                        if (response.success) {
                                                alert(response.message); // You can toast this or silently succeed
                                        }
                                },
                                error: function(xhr) {
                                        alert('Something went wrong.');
                                }
                        });
                });


                $('.select').select2({
                        placeholder: "Select an option",
                        allowClear: true
                });

                $('#filter').click(function() {
                        var date = $('#date').val();
                        var date_range = $('#date-range-picker').val();
                        var bank_name = $('#bank_name').val();
                        var product_name = $('#payment_status').val();

                        if (date || partner_name || bank_name || product_name || status) {
                                $('.data-table').DataTable().destroy();
                                load_data(date, date_range, partner_name, bank_name, product_name, status);
                        } else {
                                alert('Select at least one filter!');
                        }
                });

                $('#refresh').click(function() {
                        window.location.reload();
                });
        });


        
</script>
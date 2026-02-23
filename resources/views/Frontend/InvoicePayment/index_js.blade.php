<script>
    var totalPayoutAmount = 0;
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

            load_data(date, dateRange, bankName, paymentStatus);
        });

        // Refresh button
        $('#refresh').click(function() {
            window.location.reload();
        });

        // Edit button click - open modal with data
        $(document).on('click', '.edit-btn', function() {

            const row = $(this).data('row'); // already parsed by jQuery
            totalPayoutAmount = row.payment_amount;
            // Assign values

            $('#paymentId').val(row.id);
            $('#paymentPaid1').val(row.payment_paid1 ?? '');
            $('#paymentPaid2').val(row.payment_paid2 ?? '');
            $('#referanceNo1').val(row.referance_no1 ?? '');
            $('#referanceNo2').val(row.referance_no2 ?? '');
            $('#paymentDate1').val(row.payment_date1 ?? '');
            $('#paymentDate2').val(row.payment_date2 ?? '');
            $('#remainingAmountDisplay').val(row.remaining_amount ?? 0);
            $('#useRemainingCheckbox').prop('checked', false);
            $('#reference_no1').val(row.reference_no1 ?? '');
            $('#reference_no2').val(row.reference_no2 ?? '');
            $('#company_name').val(row.company_name ?? '');
            updateGSTNO_paymentBank();
            $('#dsa_gst_no').val(row.dsa_gst_no ?? '');
            $('#bank_gst_no').val(row.bank_gst_no ?? '');
            $('#invoice_no').val(row.invoice_no ?? '');
            $('#invoice_date').val(row.invoice_date ?? '');
            $('#bank_address').val(row.bank_address ?? '');
            $('#bank_hsn_code').val(row.bank_hsn_code ?? '');
            $('#payment-received-bank').val(row.payment_received_bank ?? '');
            // Open modal
            new bootstrap.Modal(document.getElementById('editPaymentModal')).show();

        });

        // view button click - open modal with data 
        $(document).on('click', '.view-btn', function() {
            const row = $(this).data('row');
            const applicationNos = row.application_nos ? row.application_nos.map(item => item.application_no) : [];

            // 1. Show a loader or clear old content immediately so user knows something is happening
            $('#invoiceCasesModal .modal-content').html('<div class="p-5 text-center">Loading...</div>');

            ;

            $.ajax({
                url: '{{ route("invoice_payment.getInvoiceCases") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    application_nos: applicationNos
                },
                success: function(response) {
                    if (response.success) {

                        // Use backticks (`) for the multi-line string
                        let modalHTML = `
                    <div class="modal-header" style="height: 50px;">
                            <h5 class="modal-title">Generate Invoice - Case Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                      <th>Application No</th>
                                        <th>Bank Name</th>
                                        <th>Product Name</th>
                                        <th>Month</th>
                                        <th>Date</th>
                                        <th>Rate</th>
                                        <th>Group</th>
                                        <th>Customer Name</th>
                                        <th>Payout Amount</th>
                                        <th>Disburse Amount</th>
                                </tr>
                            </thead>
                            <tbody>`;

                        response.cases.forEach(function(caseItem) {
                            modalHTML += `
                        <tr>
                             <td>${caseItem.app_id}</td>
                                <td>${caseItem.bank_name}</td>
                                <td>${caseItem.product_name}</td>
                                <td>${caseItem.month}</td>
                                <td>${caseItem.month_year}</td>
                                <td>${caseItem.payout_rate}</td>
                                <td>${caseItem.group}</td>
                                <td>${caseItem.customer_name}</td>
                                <td>₹${parseFloat(caseItem.payoutAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>₹${parseFloat(caseItem.disbAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>`;
                        });

                        modalHTML += `
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>`;

                        // Update the content ONLY once the HTML is ready
                        $('#invoiceCasesModal .modal-content').html(modalHTML);
                        // Initialize the modal ONCE at the start
                        const myModal = new bootstrap.Modal(document.getElementById('invoiceCasesModal'));
                        myModal.show()
                    } else {
                        $('#invoiceCasesModal .modal-content').html('<div class="alert alert-danger m-3">' + response.message + '</div>');
                    }
                },
                error: function(xhr) {
                    $('#invoiceCasesModal .modal-content').html('<div class="alert alert-danger m-3">Failed to load data.</div>');
                }
            });
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
            const paymentPaid1 = parseFloat($('#paymentPaid1').val()) || 0;
            const paymentPaid2 = parseFloat($('#paymentPaid2').val()) || 0;
            const paymentDate1 = $('#paymentDate1').val();
            const paymentDate2 = $('#paymentDate2').val();
            const referanceNo1 = $('#referanceNo1').val();
            const referanceNo2 = $('#referanceNo2').val();

            // 1. Payment 1 must be entered and <= total payout
            if (!paymentPaid1 || paymentPaid1 <= 0) {
                alert('Payment Received 1 is required and must be greater than zero.');
                return;
            }
            if (paymentPaid1 > totalPayoutAmount) {
                alert('Payment Received 1 cannot be greater than Total Payout Amount.');
                return;
            }
            if (!paymentDate1) {
                alert('Payment Date 1 is required.');
                return;
            }
            if (!referanceNo1) {
                alert('UTR No 1 is required.');
                return;
            }

            // 2. If Payment 2 is entered, validate
            let remainingAmount = totalPayoutAmount - paymentPaid1;
            if (paymentPaid2) {
                if (paymentPaid2 > remainingAmount) {
                    alert('Payment Received 2 cannot be greater than Remaining Amount.');
                    return;
                }
                if (!paymentDate2) {
                    alert('Payment Date 2 is required.');
                    return;
                }
                if (!referanceNo2) {
                    alert('UTR No 2 is required.');
                    return;
                }
                remainingAmount -= paymentPaid2;
            }

            // 3. Final check: payments must not exceed total payout
            if ((paymentPaid1 + paymentPaid2) > totalPayoutAmount) {
                alert('Total payments cannot exceed Total Payout Amount.');
                return;
            }

            // 4. Remaining amount calculation
            remainingAmount = Math.max(0, remainingAmount);

            $.ajax({
                url: '{{ route("invoice_payment.update", ":id") }}'.replace(':id', id),
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    payment_paid1: paymentPaid1,
                    payment_paid2: paymentPaid2,
                    payment_date1: paymentDate1,
                    payment_date2: paymentDate2,
                    referance_no1: referanceNo1,
                    referance_no2: referanceNo2,
                    remaining_amount: remainingAmount,
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
                    var msg = xhr.responseJSON?.message;
                    if (!msg && xhr.responseJSON?.errors) {
                        var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        msg = xhr.responseJSON.errors[firstKey][0];
                    }
                    alert(msg || 'An error occurred while updating the payment. Please try again.');
                }
            });
        });

        $('.select').select2({
            allowClear: true
        });
    });

    // Delete function
    function deleteInvoicePayment(id) {
        if (confirm('Are you sure you want to delete this invoice payment?')) {
            $.ajax({
                url: '{{ route("invoice_payment.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Invoice payment deleted successfully!');
                    if ($.fn.dataTable.isDataTable('#bankMisTable')) {
                        $('#bankMisTable').DataTable().ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', xhr.responseText);
                    alert('An error occurred while deleting the payment. Please try again.');
                }
            });
        }
    }


    // DataTable initialization function
    $.fn.dataTable.ext.errMode = 'none';

    function load_data(date = '', dateRange = '', bankName = '', paymentStatus = '') {
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
                    data: 'payment_paid1',
                    name: 'payment_paid1'
                },
                {
                    data: 'payment_paid2',
                    name: 'payment_paid2'
                },
                {
                    data: 'remaining_amount',
                    name: 'remaining_amount'
                },
                {
                    data: 'referance_no1',
                    name: 'referance_no1'
                },
                {
                    data: 'referance_no2',
                    name: 'referance_no2'
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

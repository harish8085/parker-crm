<script>
    $(document).ready(function() {
        // Set CSRF token globally for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Clear errors when modals are closed
        $('#createCategory').on('hidden.bs.modal', function() {
            $('#createCategoryForm')[0].reset();
            $('#createCategoryErrors').hide().html('');
            $('#category_name').removeClass('is-invalid');
            $('#category_name_error').text('');
        });

        $('#editCategoryModal').on('hidden.bs.modal', function() {
            $('#editCategoryErrors').hide().html('');
            $('#edit_category_name').removeClass('is-invalid');
            $('#edit_category_name_error').text('');
        });

        // Status toggle functionality
        $(document).on('change', '.status-toggle', function() {
            var toggleElement = $(this);
            var categoryId = toggleElement.data('category-id');
            var isChecked = toggleElement.is(':checked');
            var actionText = isChecked ? 'activate' : 'deactivate';
            
            if (confirm('Are you sure you want to ' + actionText + ' this category?')) {
                $.ajax({
                    url: '/announcement-categories/toggle-status/' + categoryId,
                    type: 'POST',
                    success: function(response) {
                        // Status updated successfully, datatable will show the new state
                        // Optionally show a success message
                        if (response.status === 'success') {
                            // You can add a toast notification here if needed
                        }
                    },
                    error: function(xhr) {
                        // Revert the toggle if there's an error
                        toggleElement.prop('checked', !isChecked);
                        console.log(xhr.responseText);
                        alert('An error occurred while updating the category status. Please try again.');
                    }
                });
            } else {
                // Revert the toggle if user cancels
                toggleElement.prop('checked', !isChecked);
            }
        });

        // Delete button functionality with confirmation
        $(document).on('click', '.delete-category-btn', function() {
            if (confirm('Are you sure you want to delete this announcement category?')) {
                var categoryId = $(this).data('category-id');
                $.ajax({
                    url: '/announcement-categories/delete/' + categoryId,
                    type: 'DELETE',
                    success: function(response) {
                        alert('Announcement category deleted successfully.');
                        // Reload the datatable
                        $('.data-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('An error occurred while deleting the category. Please try again.');
                    }
                });
            }
        });

        // Create form submission
        $('#createCategoryForm').submit(function(event) {
            event.preventDefault();
            
            // Clear previous errors
            $('#createCategoryErrors').hide().html('');
            $('#category_name').removeClass('is-invalid');
            $('#category_name_error').text('');
            
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: formData,
                success: function(response) {
                    // Close modal - try multiple methods to ensure it works
                    var modalElement = document.getElementById('createCategory');
                    if (modalElement) {
                        // Try Bootstrap 5 native API first
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var createModal = bootstrap.Modal.getInstance(modalElement);
                            if (createModal) {
                                createModal.hide();
                            } else {
                                var newModal = new bootstrap.Modal(modalElement);
                                newModal.hide();
                            }
                        }
                        // Fallback to jQuery
                        if ($('#createCategory').hasClass('show')) {
                            $('#createCategory').modal('hide');
                        }
                        // Direct hide as last resort
                        $(modalElement).removeClass('show').css('display', 'none');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                    }
                    
                    // Reset form and clear errors
                    $('#createCategoryForm')[0].reset();
                    $('#createCategoryErrors').hide().html('');
                    $('#category_name').removeClass('is-invalid');
                    $('#category_name_error').text('');
                    
                    // Reload datatable
                    $('.data-table').DataTable().ajax.reload(null, false);
                    
                    // Show success message
                    setTimeout(function() {
                        alert('Announcement category created successfully.');
                    }, 300);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<ul style="margin-bottom: 0;">';
                        
                        $.each(errors, function(key, value) {
                            if (key === 'name') {
                                $('#category_name').addClass('is-invalid');
                                $('#category_name_error').text(value[0]);
                            }
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul>';
                        $('#createCategoryErrors').html(errorHtml).show();
                    } else {
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'An error occurred while creating the category. Please try again.';
                        $('#createCategoryErrors').html('<ul style="margin-bottom: 0;"><li>' + errorMsg + '</li></ul>').show();
                    }
                }
            });
        });

        // Edit button functionality
        $(document).on('click', '.edit-category-btn', function() {
            // Clear previous errors
            $('#editCategoryErrors').hide().html('');
            $('#edit_category_name').removeClass('is-invalid');
            $('#edit_category_name_error').text('');
            
            var categoryId = $(this).data('category-id');
            $.get('/announcement-categories/update/' + categoryId, function(response) {
                $('#edit_category_name').val(response.name);
                $('#edit_is_active').prop('checked', response.is_active == 1);
                $('#editCategoryForm').attr('action', '/announcement-categories/update/' + categoryId);
                
                // Show modal using Bootstrap 5 API
                var editModalElement = document.getElementById('editCategoryModal');
                var editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
            });
        });

        // Edit form submission
        $('#editCategoryForm').submit(function(event) {
            event.preventDefault();
            // Clear previous errors
            $('#editCategoryErrors').hide().html('');
            $('#edit_category_name').removeClass('is-invalid');
            $('#edit_category_name_error').text('');
            
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: formData,
                success: function(response) {
                    // Close modal - try multiple methods to ensure it works
                    var modalElement = document.getElementById('editCategoryModal');
                    if (modalElement) {
                        // Try Bootstrap 5 native API first
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var editModal = bootstrap.Modal.getInstance(modalElement);
                            if (editModal) {
                                editModal.hide();
                            } else {
                                var newModal = new bootstrap.Modal(modalElement);
                                newModal.hide();
                            }
                        }
                        // Fallback to jQuery
                        if ($('#editCategoryModal').hasClass('show')) {
                            $('#editCategoryModal').modal('hide');
                        }
                        // Direct hide as last resort
                        $(modalElement).removeClass('show').css('display', 'none');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                    }
                    
                    // Clear any errors
                    $('#editCategoryErrors').hide().html('');
                    $('#edit_category_name').removeClass('is-invalid');
                    $('#edit_category_name_error').text('');
                    
                    // Reload datatable
                    $('.data-table').DataTable().ajax.reload(null, false);
                    
                    // Show success message
                    setTimeout(function() {
                        alert('Announcement category updated successfully.');
                    }, 300);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<ul style="margin-bottom: 0;">';
                        
                        $.each(errors, function(key, value) {
                            if (key === 'name') {
                                $('#edit_category_name').addClass('is-invalid');
                                $('#edit_category_name_error').text(value[0]);
                            }
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul>';
                        $('#editCategoryErrors').html(errorHtml).show();
                    } else {
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'An error occurred while updating the category. Please try again.';
                        $('#editCategoryErrors').html('<ul style="margin-bottom: 0;"><li>' + errorMsg + '</li></ul>').show();
                    }
                }
            });
        });
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

    function load_data(date = '', date_range = '', name = '', is_active = '') {
        var table = $('.data-table').DataTable({
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
                title: 'Announcement Categories Details',
                exportOptions: {
                    columns: function(index, data, node) {
                        return index !== table.column(':last').index();
                    }
                },
                customize: function(csv) {
                    var header = '';
                    var date = $("#date option:selected").html();
                    if(date == "custom"){
                        var date_range = $("#date-range-picker").val();
                    }
                    if (date || date_range || name || is_active) {
                        if (date) header += 'Date: ' + date + '\n';
                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                        if (name) header += 'Category Name: ' + name + '\n';
                        if (is_active) header += 'Status: ' + (is_active == '1' ? 'Active' : 'Inactive') + '\n';
                    }
                    return header + csv;
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: 'Announcement Categories Details',
                exportOptions: {
                    columns: function(index, data, node) {
                        return index !== table.column(':last').index();
                    }
                },
                customize: function(xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    var header = '';
                    var date = $("#date option:selected").html();
                    if(date == "custom"){
                        var date_range = $("#date-range-picker").val();
                    }
                    if (date || date_range || name || is_active) {
                        if (date) header += 'Date: ' + date + '\n';
                        if (date_range) header += 'Date Range: ' + date_range + '\n';
                        if (name) header += 'Category Name: ' + name + '\n';
                        if (is_active) header += 'Status: ' + (is_active == '1' ? 'Active' : 'Inactive') + '\n';
                    }
                    var rows = $('row', sheet);
                    var firstRow = rows[0];
                    var newRow = '<row r="1">' +
                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                        '</row><row r="2">' +
                        '<c t="inlineStr" r="A1"><is><t>' + header + '</t></is></c>' +
                        '</row>';
                    $(firstRow).before(newRow);
                }
            },
            {
                extend: 'print',
                text: 'Print',
                title: 'Announcement Categories Details',
                exportOptions: {
                    columns: function(index, data, node) {
                        return index !== table.column(':last').index();
                    }
                },
                customize: function(win) {
                    var filters = '';
                    var date = $("#date option:selected").html();
                    if(date == "custom"){
                        var date_range = $("#date-range-picker").val();
                    }
                    if (date || date_range || name || is_active) {
                        filters += '<h4>Filters Applied:</h4>';
                        if (date) filters += '<p>Date: ' + date + '</p>';
                        if (date_range) filters += '<p>Date Range: ' + date_range + '</p>';
                        if (name) filters += '<p>Category Name: ' + name + '</p>';
                        if (is_active) filters += '<p>Status: ' + (is_active == '1' ? 'Active' : 'Inactive') + '</p>';
                    }
                    $(win.document.body).prepend(filters);
                }
            }],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('announcement-categories.index') }}",
                data: {
                    date: date,
                    date_range: date_range,
                    name: name,
                    is_active: is_active,
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
                data: 'name',
                name: 'name'
            },
            {
                data: 'is_active',
                name: 'is_active'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }]
        });
    }

    $(document).ready(function() {
        load_data();

        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#filter').click(function() {
            var date = $('#date').val();
            var date_range = $('#date-range-picker').val();
            var name = $('#name').val();
            var is_active = $('#is_active').val();

            if (date || name || is_active) {
                $('.data-table').DataTable().destroy();
                load_data(date, date_range, name, is_active);
            } else {
                alert('Select at least one filter!');
            }
        });

        $('#refresh').click(function() {
            window.location.reload();
        });
    });
</script>


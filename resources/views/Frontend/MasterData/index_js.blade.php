<script>
    // Create form submission
    $('#createCategoryForm').submit(function(event) {
        event.preventDefault();

        // Clear previous errors
        $('#createCategoryErrors').hide().html('');
        $('#name').removeClass('is-invalid');
        $('#value').removeClass('is-invalid');
        $('#category_name_error').text('');
        $('#value_error').text('');

        var formData = $(this).serialize();
        $userdata =
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
                        alert('Master Code created successfully.');
                    }, 300);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<ul style="margin-bottom: 0;">';

                        $.each(errors, function(key, value) {
                            if (key === 'name') {
                                $('#name').addClass('is-invalid');
                                $('name_error').text(value[0]);
                            }
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul>';
                        $('#createCategoryErrors').html(errorHtml).show();
                    } else {
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            'An error occurred while creating the Master Data. Please try again.';
                        $('#createCategoryErrors').html('<ul style="margin-bottom: 0;"><li>' + errorMsg + '</li></ul>').show();
                    }
                }
            });
    });
</script>




<!-- Datatable -->
<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';

    let table;

    $(document).ready(function() {

        $('.data-table').DataTable({
            dom: 'Bfrtip<"bottom"l>',
            processing: true,
            serverSide: true,

            ajax: {
                url: "{{ route('master-data.index') }}",
                data: function(d) {
                    d.name = $('#name').val();
                }
            },

            columns: [{
                    data: null,
                    render: (data, type, row, meta) =>
                        meta.row + 1 + meta.settings._iDisplayStart,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name'
                },
                {
                    data: 'value'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'updated_by'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

    });

    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();

        let id = $(this).data('id');

        $.ajax({
            url: '/master-data/update/' + id,
            type: 'POST',
            data: $(this).serialize(),
            success: function() {
                $('#editCategory').modal('hide');
                $('.data-table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                alert('Update failed');
            }
        });
    });

    function updateMasterData(id) {

        $.ajax({
            url: '/master-data/' + id,
            type: 'GET',
            success: function(data) {

                $('#editCategoryForm').data('id', id);
                $('#editCategoryForm input[name="name"]').val(data.name);
                $('#editCategoryForm input[name="value"]').val(data.value);

                $('#editCategory').modal('show');
            },
            error: function() {
                alert('Failed to fetch data');
            }
        });
    }
</script>
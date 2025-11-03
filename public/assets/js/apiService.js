function apiService(url, method, formData, onSuccess, onError) {
    $('.submit-btn').hide();
    $('.loader-1').show();

    $.ajax({
        url: url,
        type: method,
        data: formData,
        contentType: false,
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        },
        success: function(data) {
            if (data.code === 200 && data.status === 'success') {
                if (onSuccess && typeof onSuccess === 'function') {
                    onSuccess(data.message);
                }
            } else if (data.code === 500 && data.status === 'fail') {
                displayErrorMessage(data.message);
            }

            $('.submit-btn').show();
            $('.loader-1').hide();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('.submit-btn').show();
            $('.loader-1').hide();
            let errorMessage = '';

            if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                $.each(jqXHR.responseJSON.errors, function(index, error) {
                    errorMessage += `<li>${error}</li>`;
                });
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            } else {
                errorMessage = 'An unexpected error occurred.';
            }

            displayErrorMessage(errorMessage);

            if (onError && typeof onError === 'function') {
                onError(jqXHR, textStatus, errorThrown);
            }
        }
    });
}

function displayErrorMessage(message) {
    var errorContainer = $('<div>').addClass('alert alert-danger').html(
        `<ul class="m-0 pl-1"><li>${message}</li></ul>`
    );
    $('.card-body').prepend(errorContainer);
}

@foreach($products as $product)
@php
$service_details = DB::table('service_details')->where('service_id', $service->id)->where('product_name', $product->id)->get();
$service_details_type = $service_details->first() ? $service_details->first()->type : '';
@endphp
<div class="bank-card bank-card1 card2">
    <div class="card-top-border">{{ $product->name }}</div>
    <div class="bank-card-inside">
        <div class="card-form2">
            <div class="toggle-contaenr">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Select Service Type</label>
                    <select class="bank-detail-input service-type" data-product="{{ $product->id }}">
                        <option value="" selected disabled>Select Service Type </option>
                        <option value="fixed" @if($service_details_type=='fixed' ) selected @endif>Fixed</option>
                        <option value="variable" @if($service_details_type=='variable' ) selected @endif>Variable</option>
                    </select>
                </div>
                <div class="bank-detail-inputs " id="add-more-section_{{ $product->id }}" style="display: {{ $service_details_type == 'variable' ? 'block' : 'none' }}">
                    <br> <button class="add-p-btn add-more" data-product="{{ $product->id }}">Add More</button>
                </div>
            </div>
        </div>
        <div class="input-grid" id="product_{{ $product->id }}_details" style="display: {{ $service_details_type ? 'grid' : 'none' }}">
            @foreach($service_details as $index => $detail)
            <div class="bank-detail-inputs percentage-section">
                <label class="bank-input-label">Percentage</label>
                <input class="bank-detail-input percentage" id="percentage_{{ $product->id }}_{{ $index + 1 }}" type="number" value="{{ $detail->percentage }}" placeholder="Enter percentage" />
            </div>
            <div class="bank-detail-inputs min-value-section" style="display: {{ ($service_details_type !='fixed') ? 'block' : 'none' }}">
                <label class="bank-input-label">Minimum Value</label>
                <input class="bank-detail-input min-value" type="number" id="min_value_{{ $product->id }}_{{ $index + 1 }}" value="{{ $detail->min_value }}" placeholder="Enter minimum value" />
            </div>
            <div class="bank-detail-inputs max-value-section" style="display: {{ ($service_details_type !='fixed') ? 'block' : 'none' }}">
                <label class="bank-input-label">Maximum Value</label>
                <input class="bank-detail-input max-value" type="number" id="max_value_{{ $product->id }}_{{ $index + 1 }}" value="{{ $detail->max_value }}" placeholder="Enter maximum value" />
            </div>
            <br><br>
            @endforeach
        </div>
    </div>
</div>
@endforeach
@if(count($products) != 0)
<div class="save-btn-container">
    <button type="submit" class="save-btn">Submit Details</button>
</div>
@endif

<script>
    $('.service-type').change(async function() {
        var product_id = $(this).data('product');
        var service_type = $(this).val();
        if (service_type === 'variable') {
            $('#product_' + product_id + '_details').show();
            $('#product_' + product_id + '_details').html('');

            $('#add-more-section_' + product_id).show();
            await addMore(product_id, 0)
        } else {
            $('#product_' + product_id + '_details').show();
            $('#product_' + product_id + '_details .percentage-section').show();
            $('#product_' + product_id + '_details .min-value-section').hide();
            $('#product_' + product_id + '_details .max-value-section').hide();
            $('#add-more-section_' + product_id).hide();

            // Remove any additional percentage input fields when 'Fixed' is selected
            $('#product_' + product_id + '_details .percentage-section').not(':first').remove();
            localStorage.setItem(product_id, 1); // Reset the counter to 1
        }
    });

    $('.add-more').click(async function() {
        var product_id = $(this).data('product');

        await addMore(product_id, localStorage.getItem(product_id) ? localStorage.getItem(product_id) : 1)
    });


    function addMore(product_id, add_more_value) {
        var value = add_more_value

            ++value;
        var html = `
            ${(value!=1)?`<br><br>`:`` }
            <div class="bank-detail-inputs percentage-section">
                <label class="bank-input-label">Percentage</label>
                <input class="bank-detail-input percentage" type="number" id="percentage_${product_id}_${value}" placeholder="Enter percentage" />
            </div>
            <div class="bank-detail-inputs min-value-section">
                <label class="bank-input-label">Minimum Value</label>
                <input class="bank-detail-input min-value" type="number" id="min_value_${product_id}_${value}" placeholder="Enter minimum value" />
            </div>
            <div class="bank-detail-inputs max-value-section">
                <label class="bank-input-label">Maximum Value</label>
                <input class="bank-detail-input max-value" type="number" id="max_value_${product_id}_${value}" placeholder="Enter maximum value" />
            </div>`;
        $('#product_' + product_id + '_details').append(html);
        localStorage.setItem(product_id, value)
    }
    $('.save-btn').click(async function() {
        // Gather data from your form or other elements
        let productsData = []; // Array to store data for each product
        $('.bank-card1').each(function() {
            let serviceTypeElement = $(this).find('.service-type');
            let serviceType = serviceTypeElement.val();
            if (serviceType !== null && serviceType !== '') { // Check if serviceType is not null or empty

                let productData = {
                    productId: $(this).find('.service-type').data('product'),
                    serviceType: $(this).find('.service-type').val(),
                    details: [] // Array to store details for each field
                };

                // Loop through each field within the card and gather data

                let additionalDetails = $(this).find('#product_' + productData.productId + '_details .bank-detail-inputs');
                var addCount = localStorage.getItem(productData.productId) ? localStorage.getItem(productData.productId) : 1
                for (var i = 1; i <= addCount; i++) {
                    let detail = {
                        "percentage": $(`#percentage_${productData.productId}_${i}`).val(),
                        "min_value": $(`#min_value_${productData.productId}_${i}`).val(),
                        "max_value": $(`#max_value_${productData.productId}_${i}`).val()
                    };
                    productData.details.push(detail);

                }
                productsData.push(productData);
            }
        });

        var data = {
            service_name: $('#service_name').val(),
            bank_id: $('#bank_id').val(),
            data: productsData
        }
        sendDataToController(data)
    });


    function sendDataToController(data) {

        $.ajax({
            url: '/services/update/{{$service->id}}',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token if you're using Laravel CSRF protection
            },
            data: JSON.stringify(data),
            success: function(response) {
                window.location.href = '/services';
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    var errorMessage = JSON.parse(xhr.responseText).message;
                    alert(errorMessage);
                } else {
                    console.error('There was a problem with the request:', error);
                    // Handle other errors
                }
            }
        });
    }
</script>
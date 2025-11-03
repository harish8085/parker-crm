/*
* update price
*/
$('#updateMasterCodeBtn').on('click',(e) => {
    e.preventDefault();
    var frm = $('#updateMasterCodeForm');
    var btn = $(this);  
    let url = frm.attr('action');
    let btnText = btn.text();
    if (frm.valid()) {
        showButtonLoader(btn, btnText, 'disabled');
        $.ajax({
            type: "POST",
            url: url,
            data: frm.serialize(),
            success: function(response) { 
                showButtonLoader(btn, btnText, 'enable');           
                if (response.success) {  
                    successToaster(response.message, 'Manage Price');  
                    setTimeout(() => { 
                        window.location.href = priceListUrl; 
                    }, 2000);                           
                } else { 
                    toastr.error(response.message, 'Manage Price');
                }
            },
            error: function(err) {
                var obj = JSON.parse(err.responseText);
                obj = obj['errors'];
                showButtonLoader(btn, btnText, 'enable');
                for (var x in obj) {
                    $('#' + x + '_error').html(obj[x]);
                    $('#' + x + '_error').parent('.form-group').removeClass('has-success').addClass('has-error');
                }
            }
        });
    }
});
    

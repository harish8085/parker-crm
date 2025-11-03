@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/permissions.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">

@endsection
@section('body')
<div>
    <h2 class="permission-heading">PERMISSIONS</h2>
    <p class="permission-pg">Select Role to Get & Set Permissions</p>
    <div>
        <div class="dropdown permission">
            <select class="form-select" required name="role" id="role">
                <option value="" selected disabled>Select Role</option>

                @foreach($roles as $key=>$role)
                <option value="{{$role->id}}">{{$role->name}}</option>
                @endforeach
            </select>
        </div>
    </div>



    <div class="table-responsive">
        <table class="table ">
            <thead>
                <tr>
                    <th class="table-header">Module </th>
                    <th class="table-header">Create </th>
                    <th class="table-header">Read</th>
                    <th class="table-header">Update</th>
                    <th class="table-header">Delete</th>
                </tr>
            </thead>
            <tbody id="tableBody">



            </tbody>
        </table>
    </div>

</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#role').change(function() {
            $.get(`{{('/staff/view/getPermission/')}}` + $(this).val(), function(response) {
                $('#tableBody').html(response)
            });
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#tableBody').on('change', '.form-check-input', function() {
            var permissionId = $(this).closest('tr').data('permission-id');
            var attribute = $(this).attr('name');
            var value = $(this).is(':checked') ? 'active' : 'in-active';

            $.ajax({
                url: `{{('/staff/create/updatePermission')}}`,
                type: 'POST',
                data: {
                    permission_id: permissionId,
                    [attribute]: value
                },
                success: function(response) {
                    if (response.success) {
                        // Optionally update UI to indicate successful update
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    // Handle error
                }
            });
        });
    });
</script>
@endsection
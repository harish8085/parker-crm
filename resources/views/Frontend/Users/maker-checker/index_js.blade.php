<script type="text/javascript">
    $.fn.dataTable.ext.errMode = 'none';

    function load_url_maker_checker_table() {
        var table = $('.data-table').DataTable({
            debug: false,
            dom: 'Bfrtip<"bottom"l>',
            lengthMenu: [
                [10, 25, 50, 100, 500, -1],
                [10, 25, 50, 100, 500, 'All']
            ],
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Maker / Checker Users',
                    charset: 'UTF-8',
                    bom: true,
                    exportOptions: {
                        columns: function (index, data, node) {
                            return index !== table.column(':last').index();
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Maker / Checker Users',
                    exportOptions: {
                        columns: function (index, data, node) {
                            return index !== table.column(':last').index();
                        }
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: 'Maker / Checker Users',
                    exportOptions: {
                        columns: function (index, data, node) {
                            return index !== table.column(':last').index();
                        }
                    }
                }
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('maker-checker.index') }}",
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            },
            columns: [
                {
                    data: null,
                    name: 'srno',
                    render: function (data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'Emp_Id',
                    name: 'Emp_Id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'role_label',
                    name: 'role_label'
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
                }
            ]
        });
    }

    $(document).ready(function () {
        load_url_maker_checker_table();
    });
</script>



@if(check_customer_project_permission($rec->settings->permissions, 'view_timesheets')) 
<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
    <thead>
    <tr>
        <th>@lang("form.member")</th>
        <th>@lang("form.task")</th>
        <th>@lang("form.start_time")</th>
        <th>@lang("form.end_time")</th>    
        <th>@lang("form.time_spent")</th>   

    </tr>
    </thead>
</table>


@section('innerPageJS')
    <script>
        $(function() {

            var dataTable = $('#data').DataTable({
                dom: 'Bfrtip',
                buttons: [

                    {
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary')
                        },
                        className: "btn-outline-info btn-sm",
                        extend: 'collection',
                        text: 'Export',
                        buttons: [
                            'copy',
                            'excel',
                            'csv',
                            'pdf',
                            'print'
                        ]
                    }
                ],

                "language": {
                    "lengthMenu": '_MENU_ ',
                    "search": '',
                    "searchPlaceholder": "{{ __('form.search') }}"
                    // "paginate": {
                    //     "previous": '<i class="fa fa-angle-left"></i>',
                    //     "next": '<i class="fa fa-angle-right"></i>'
                    // }
                }

                ,
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("cp_datatables_timesheet", $rec->id ) !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        d.component_id = "{{ COMPONENT_TYPE_PROJECT }}";
                        d.component_number = "{{ $rec->id }}";
                        // d.custom = $('#myInput').val();
                        // etc
                    }
                    

                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

            

        });


       
    </script>
@endsection

@endif
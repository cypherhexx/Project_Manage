
<div class="main-content">
    <h5>@lang('form.tasks')</h5>
<hr>



    <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.task_#")</th>
            <th>@lang("form.name")</th>
            <th>@lang("form.status")</th>
            <th>@lang("form.start_date")</th>
            <th>@lang("form.due_date")</th>
            <th>@lang("form.assigned_to")</th>
            <th>@lang("form.tags")</th>
            <th>@lang("form.priority")</th>
            <th>@lang("form.created_by")</th>
        </tr>
        </thead>
    </table>
</div>

@section('innerPageJS')
    <script>
        $(function() {

        dataTable = "";
            
        dataTable = $('#data').DataTable({
                dom: 'Bfrtip',
                buttons: [

                    {
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary')
                        },
                        className: "btn-light btn-sm",
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
                    "url": '{!! route("datatables_tasks") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        d.component_id = "{{ COMPONENT_TYPE_CUSTOMER }}";
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
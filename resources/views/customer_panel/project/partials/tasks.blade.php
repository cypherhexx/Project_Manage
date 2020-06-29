@if(check_customer_project_permission($rec->settings->permissions, 'view_tasks')) 

     <a class="btn btn-secondary btn-sm" href="{{ route('cp_show_project_page', $rec->id) }}?group=tasks&subgroup=kanban" role="button">@lang('form.switch_to_canban_view')</a>
     <!-- Modal -->



<hr>
<div>
    <?php $task_summary =  $rec->get_task_summary(); ?>
    <div class="row">
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_BACKLOG] }}</h5>
            <span class="text-secondary">@lang('form.back_log')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_IN_PROGRESS] }}</h5>
            <span class="text-primary">@lang('form.in_progress')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_TESTING] }}</h5>
            @lang('form.testing')
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_AWAITING_FEEDBACK] }}</h5>
            <span class="text-warning">@lang('form.awaiting_feedback')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_COMPLETE] }}</h5>
            <span class="text-success">@lang('form.complete')</span>
        </div>
    </div>


</div>

<hr>



    <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.name")</th>            
            <th>@lang("form.start_date")</th>
            <th>@lang("form.due_date")</th>
            <th>@lang("form.status")</th>
            <th>@lang("form.milestone")</th>
            <th>@lang("form.billable")</th>
            <th>@lang("form.priority")</th>

        </tr>
        </thead>
    </table>


@section('innerPageJS')
    <script>
        $(function() {
       
            
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
                    "url": '{!! route("cp_datatables_tasks") !!}',
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
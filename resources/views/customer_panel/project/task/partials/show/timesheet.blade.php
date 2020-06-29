@if(check_customer_project_permission($rec->settings->permissions, 'view_timesheets')) 
<br>
<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="timesheet">
    <thead>
    <tr>
        <th>@lang("form.member")</th>
        <th style="display: none;">@lang("form.task")</th>
        <th>@lang("form.start_time")</th>
        <th>@lang("form.end_time")</th>        
        <th>@lang("form.time_spent")</th>  

    </tr>
    </thead>
</table>


@section('innerPageJS')
    <script>
        $(function() {

            var dataTable = $('#timesheet').DataTable({
                dom: 'frtip',
                
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
                pageLength: 10,
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("cp_datatables_timesheet", $rec->id) !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        d.task_id = "{{ $task->id }}";
                       
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
            })
            .column(1).visible(false);

            

           

        });


    </script>
@endsection
@endif
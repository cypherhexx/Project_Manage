<?php

  $s =  (is_current_user($rec->id)) ? __('form.my_account') : __('form.team_member');
?>
@section('title',  $s . " : ". $rec->first_name)

<div class="main-content" style="margin-bottom: 10px !important">
    @include('team_member.partials.profile_menu')
</div> 

<div class="row">

    <div class="col-md-3 col-sm-4">
       @include('team_member.partials.profile_photo')
    </div>


    <div class="col-sm-8 col-md-9">

        <div class="main-content">

        
            <div class="row">
                <div class="col-md-12">
                    
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

                        </tr>
                        </thead>
                    </table>
                        


                </div>  
            </div>
        </div>


    
        

     </div>   

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
                pageLength: 10,
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
                       
                        d.assigned_to = "{{ $rec->id }}";
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
            }).column(4).visible( false );

    });

</script>
@endsection
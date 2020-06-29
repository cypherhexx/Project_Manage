@extends('layouts.main')
@section('title', __('form.projects'))
@section('content')
<div class="main-content">

    <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.projects')</h5>
      </div>
      <div class="col-md-6">
        @if(check_perm('projects_create'))
        <a class="btn btn-primary btn-sm float-md-right" href="{{ route('add_projects') }}" role="button">@lang('form.new_project')</a>
        
        @endif
      </div>
   </div>
   
   @if(check_perm('projects_view') || is_involved_in_project() )
   <hr>
   <div class="row">       


        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['not_started'] }}</h5>
            <div>@lang('form.not_started')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['in_progress'] }}</h5>
            <div class="text-success">@lang('form.in_progress')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['on_hold'] }}</h5>
            <div class="text-danger">@lang('form.on_hold')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['cancelled'] }}</h5>
            <div class="text-primary">@lang('form.cancelled')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['finished'] }}</h5>
            <div class="text-danger">@lang('form.finished')</div>
        </div>
 
    </div>
    <hr>
    <div class="form-row">
      <div class="form-group col-md-3">
         <label>@lang('form.status')</label>
         <?php
            echo form_dropdown('status_id', $data['status_id_list'] , $data['default_status_ids'] , "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      
   </div>
  
   
    <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.project_#")</th>
            <th>@lang("form.name")</th>
            <th>@lang("form.customer")</th>            
            <th>@lang("form.start_date")</th>
            <th>@lang("form.dead_line")</th>
            <th>@lang("form.billing_type")</th>
            <th>@lang("form.status")</th>
        </tr>
        </thead>
    </table>
 
 @endif

</div>
@endsection

@section('onPageJs')
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
                    "url": '{!! route("datatables_projects") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },                    
                    "data": function ( d ) {
                        
                        d.status_ids  = $("select[name=status_id]").val();                       
                       
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


            $('select').change(function(){

                dataTable.draw();
            });

        });


    </script>


@endsection
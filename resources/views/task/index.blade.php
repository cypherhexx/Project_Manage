@extends('layouts.main')
@section('title', __('form.tasks'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.tasks')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right"> 
            @if(check_perm('tasks_create'))
            <a class="btn btn-primary btn-sm" href="{{ route('add_task_page') }}" role="button">@lang('form.new_task')</a>
            @endif
            @if(check_perm('tasks_view'))
            <a class="btn btn-secondary btn-sm" href="{{ route('task_canban_view') }}" role="button">@lang('form.switch_to_canban_view')</a> 
            @endif   
         </div>
      </div>
   </div>
   <hr>
   <div>
      <?php $task_summary = $data['task_summary']; ?>
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
   <div class="form-row">
      <div class="form-group col-md-2">
         <label>@lang('form.related_to')</label>
         <?php
            echo form_dropdown('component_id', $data['component_id_list'] , [], "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.status')</label>
         <?php
            echo form_dropdown('status_id', $data['status_id_list'] , $data['default_status_ids'] , "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.priority')</label>
         <?php
            echo form_dropdown('priority_id', $data['priority_id_list'] , [], "class='form-control four-boot' multiple='multiple'");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.sort_by')</label>
         <?php
            echo form_dropdown('sort_by', $data['sort_by'] , [], "class='form-control four-boot' ");
            ?>
      </div>
      @if(check_perm('tasks_view'))
      <div class="form-group col-md-3">
         <label>@lang('form.assigned_to')</label>
         <?php
            echo form_dropdown('assigned_to', $data['assigned_to_list'] , [], "class='form-control four-boot'");
            ?>
      </div>
      @endif
   </div>
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
@endsection
@section('onPageJs')
    <script>

      var dataTable = "";
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
                    "url": '{!! route("datatables_tasks") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },                   
                    "data": function ( d ) {
                        
                        d.component_id              = $("select[name=component_id]").val();
                        d.status_id                 = $('select[name=status_id]').val();
                        d.priority_id               = $('select[name=priority_id]').val();
                        d.assigned_to               = $('select[name=assigned_to]').val();
                        d.sort_by                   = $('select[name=sort_by]').val();
                       
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


            

            // $('.status').click(function(e){
            //   e.preventDefault();
            //   e.stopPropagation();
                
            //     if($(this).hasClass('active'))
            //     {
            //         $(this).removeClass('active');
            //     }
            //     else
            //     {
            //         $(this).addClass('active');
            //     }
            //     dataTable.draw();
            // });

            

            // $('.assigned_to').click(function(e){
            //   e.preventDefault();
            //   e.stopPropagation();

            //     if($(this).hasClass('active'))
            //     {
            //         $(this).removeClass('active');                   

            //     }
            //     else
            //     {
            //         $('.assigned_to').each(function(i, obj) {
                        
            //             $(this).removeClass('active');
            //         });

            //         $(this).addClass('active');
            //     }
            //     dataTable.draw();


            // });


        });
    </script>
@endsection
@extends('layouts.main')
@section('title', __('form.leads'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.leads')</h5>
      </div>
      <div class="col-md-6">
         @if(check_perm('leads_create'))
         <div class="float-md-right">
            <a class="btn btn-primary btn-sm" href="{{ route('add_lead_page') }}" role="button">@lang('form.new_lead')</a>
            <a class="btn btn-primary btn-sm" href="{{ route('import_lead_page') }}">@lang('form.import_leads')</a>
         </div>
         @endif
      </div>
   </div>
   <hr>
   <div class="row">
      @if(count($data['stat']) > 0)   
      @foreach($data['stat'] as $stat)      
      <div class="col-md-2 bd-highlight">
         <h5>{{ $stat['total'] }}</h5>
         <div class="text-secondary">{{ $stat['name'] }}</div>
      </div>
      @endforeach
      @endif
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat_customer']['total'] }}</h5>
         <div class="text-success">{{ $data['stat_customer']['name'] }}</div>
      </div>
   </div>
   <hr>
   <div class="row">
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['lost_lead'] }}%</h5>
         <div class="text-danger">{{ __('form.lost_leads') }}</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['junk_lead'] }}%</h5>
         <div class="text-danger">{{ __('form.junk_leads') }}</div>
      </div>
   </div>
   <hr>
   <div class="form-row">
      <div class="form-group col-md-2">
         <label>@lang('form.status')</label>
         <?php
            echo form_dropdown('status_id', $data['lead_status_id_list'] , $data['default_lead_status_id_list']  , "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.source')</label>
         <?php
            echo form_dropdown('source_id', $data['lead_source_id_list'] ,  [] , "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      
      @if(check_perm('leads_view'))
       <div class="form-group col-md-3">
         <label>@lang('form.assigned_to')</label>
         <?php
            echo form_dropdown('assigned_to', $data['assigned_to_list'] , [], "class='form-control four-boot'");
            ?>
      </div>

      <div class="form-group col-md-2">
         <label>@lang('form.filter_by')</label>
         <?php
            echo form_dropdown('additional_filter', $data['additional_filter_list'] , [], "class='form-control four-boot' ");
            ?>
      </div>

     
      @endif
   </div>
   <hr>
   @if(check_perm('leads_view') || check_perm('leads_view_own'))
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.company")</th>
            <th>@lang("form.email")</th>
            <th>@lang("form.phone")</th>
            <th>@lang("form.tags")</th>
            <th>@lang("form.assigned")</th>
            <th>@lang("form.status")</th>
            <th>@lang("form.source")</th>
            <th>@lang("form.last_contacted")</th>
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

                // dom: 'B<"toolbar">frtip',
                // initComplete: function(){
                //   $("div.toolbar")
                //      .html('<button class="btn btn-light btn-sm" type="button" id="bulk_action">{{ __("form.bulk_action") }}</button>');           
                // },  
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
                    "url": '{!! route("datatables_leads") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },                   
                    "data": function ( d ) {
                        
                        d.status_id                 = $("select[name=status_id]").val();
                        d.source_id                 = $('select[name=source_id]').val();
                        d.assigned_to               = $('select[name=assigned_to]').val();
                        d.additional_filter         = $('select[name=additional_filter]').val();
                       
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


        $(document).on('click', '#bulk_action', function(e){
            e.preventDefault();
            
            
           
        });


        

    </script>
@endsection

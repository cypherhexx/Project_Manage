<div class="row">
      <div class="col-md-6">
         <h5>@lang('form.tickets')</h5>
      </div>
      <div class="col-md-6">
         @if(check_perm('tickets_create'))
         <a class="btn btn-primary btn-sm float-md-right" href="{{ route('add_ticket_page') }}?project_id={{ $rec->id }}" role="button">   @lang('form.new_ticket')
         </a>
         @endif
      </div>
   </div>
   <hr>
   
<div class="row">
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['open'] }}</h5>
         <div>@lang('form.open')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['in_progress'] }}</h5>
         <div class="text-success">@lang('form.in_progress')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['answered'] }}</h5>
         <div class="text-danger">@lang('form.answered')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['on_hold'] }}</h5>
         <div class="text-primary">@lang('form.on_hold')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['closed'] }}</h5>
         <div class="text-danger">@lang('form.closed')</div>
      </div>
   </div>
   <hr>
   <div class="form-row">
      <div class="form-group col-md-2">
         <label>@lang('form.department')</label>
         <?php
            echo form_dropdown('department_id', $data['department_id_list'] , [], "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.status')</label>
         <?php
            echo form_dropdown('ticket_status_id', $data['ticket_status_id_list'] , $data['default_ticket_status_ids'] , "class='form-control four-boot' multiple='multiple' ");
            ?>
      </div>
      <div class="form-group col-md-2">
         <label>@lang('form.priority')</label>
         <?php
            echo form_dropdown('ticket_priority_id', $data['ticket_priority_id_list'] , [], "class='form-control four-boot' multiple='multiple'");
            ?>
      </div>
      @if(check_perm('tickets_view'))
      <div class="form-group col-md-3">
         <label>@lang('form.assigned_to')</label>
         <?php
            echo form_dropdown('assigned_to', $data['customer_support_assistant_id_list'] , [], "class='form-control four-boot'");
            ?>
      </div>
      @endif
   </div>

<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.ticket_#")</th>
            <th>@lang("form.subject")</th>
            <th>@lang("form.department")</th>
            <th>@lang("form.service")</th>
            <th>@lang("form.contact")</th>
            <th>@lang("form.status")</th>
            <th>@lang("form.priority")</th>
            <th>@lang("form.assigned_to")</th>
            <th>@lang("form.last_reply")</th>
            <th>@lang("form.created")</th>
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
                "columnDefs": [
                    // { className: "text-right", "targets": [5] }
                    // { className: "text-center", "targets": [5] }


                ],
                "ajax": {
                    "url": '{!! route("datatables_tickets") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        
                        d.project_id                = "{{ $rec->id }}";
                        d.department_id             = $("select[name=department_id]").val();
                        d.ticket_status_id          = $('select[name=ticket_status_id]').val();
                        d.ticket_priority_id        = $('select[name=ticket_priority_id]').val();
                        d.assigned_to               = $('select[name=assigned_to]').val();                      
                       
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
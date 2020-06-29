@extends('layouts.customer.main')
@section('title', __('form.tickets'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.support_tickets')</h5>
      </div>
      <div class="col-md-6">
         <a class="btn btn-primary btn-sm float-md-right" href="{{ route('cp_add_ticket_page') }}" role="button" style="margin-bottom: 10px;">   @lang('form.new_ticket')
         </a>
      </div>
   </div>
   <hr>
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.ticket_#")</th>
            <th>@lang("form.subject")</th>
            <th>@lang("form.department")</th>
            <th>@lang("form.project")</th>
            <th>@lang("form.service")</th>
            <th>@lang("form.status")</th>
            <th>@lang("form.priority")</th>
            <th style="display: none;">@lang("form.last_reply")</th>
            <th>@lang("form.created")</th>
         </tr>
      </thead>
   </table>
</div>
@endsection
@section('onPageJs')
    <script>

        $(function() {

            $('#data').DataTable({
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
                    { "targets": [7], "visible": false  }

                ],
                "ajax": {
                    "url": '{!! route("cp_datatables_tickets") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            });

        });


    </script>
@endsection
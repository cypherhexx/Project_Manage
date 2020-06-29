

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
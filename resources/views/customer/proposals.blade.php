<div class="main-content">
   <h5>@lang('form.proposals')</h5>
   <hr>
   <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.proposal_#")</th>
            <th class="col-md-3">@lang("form.title")</th>
            <th style="display: none;">@lang("form.to")</th>
            <th>@lang("form.total")</th>
            <th>@lang("form.date")</th>
            <th>@lang("form.open_till")</th>
            <th>@lang("form.tags")</th>
            <th>@lang("form.date_created")</th>
            <th>@lang("form.status")</th>
         </tr>
      </thead>
   </table>
</div>

@section('innerPageJS')

    <script>
        $(function () {


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

                pageResize: true,
                responsive: true,
                processing: true,
                serverSide: true,
                // iDisplayLength: 5,
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                "columnDefs": [
                    { className: "text-right", "targets": [1,2] }
                    // { className: "text-center", "targets": [5] }




                ],
                "ajax": {
                    "url": '{!! route("datatables_proposal") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {

                        d.customer_id = "{{ $rec->id }}";

                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            })
           .column(2).visible(false);

        });
    </script>

@endsection
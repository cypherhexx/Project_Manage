<div class="main-content">
   <div class="bg-light text-dark" style="margin: -20px; padding: 10px;">
      <h5>@lang('form.expenses')</h5>
   </div>
   <br>
   <br>
   <table class="table table-expenses dataTable no-footer dtr-inline collapsed" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.category")</th>
            <th>@lang("form.amount")</th>
            <th>@lang("form.name")</th>
            <th>@lang("form.date")</th>
            <th>@lang("form.project")</th>
            <th>@lang("form.customer")</th>
            <th>@lang("form.invoice")</th>
            <th style="display: none;">@lang("form.vendor")</th>
            <th>@lang("form.reference")</th>
            <th>@lang("form.payment_mode")</th>
            <th>@lang("form.attachment")</th>
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
                    { className: "text-right", "targets": [1,2] },
                    // { className: "text-center", "targets": [5] }
                    { responsivePriority: 1},
                    { responsivePriority: 2},
                    { responsivePriority: 3}


                ],
                "ajax": {
                    "url": '{!! route("datatables_expense") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {

                        d.vendor_id = "{{ $rec->id }}";

                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            })
           .column(7).visible(false);

        });
    </script>

@endsection
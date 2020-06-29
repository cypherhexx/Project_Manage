@extends('layouts.main')
@section('title', __('form.recurring_invoices'))
@section('content')

     <div class="white-background">

        <div class="row">
              <div class="col-md-6">
                 <h5>@lang('form.recurring_invoices')</h5>
              </div>
              <div class="col-md-6">
                 <div class="float-md-right">
                 
                            <a class="btn btn-primary btn-sm" href="{{ route('invoice_list') }}" role="button">@lang('form.invoices')
                         </a>                        
                                            
                         <a class="btn btn-primary btn-sm" href="{{ route('recurring_invoices_list') }}" role="button">@lang('form.recurring_invoices')
                         </a>
                  </div>  
              </div>
           </div>

        <hr>
     
     <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
                    <thead>
                    <tr>
                        <th>@lang("form.invoice_#")</th>
                        <th>@lang("form.amount")</th>
                        <th>@lang("form.total_tax")</th>
                        <th>@lang("form.date")</th>
                        <th>@lang("form.customer")</th>
                        <th>@lang("form.project")</th>
                        <th>@lang("form.tags")</th>                        
                        <th>@lang("form.due_date")</th>
                        {{--<th>@lang("form.reference")</th>--}}
                        <th>@lang("form.status")</th>
                    </tr>
                    </thead>
                </table>

     </div>
@endsection


@section('onPageJs')


    <script>

        $(function () {

         

            var dataTable = $('#data').DataTable({

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
                    "url": '{!! route("datatable_recurring_invoices") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }                    
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

 });

</script>
@endsection
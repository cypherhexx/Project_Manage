@extends('layouts.main')
@section('title', __('form.items'))
@section('content')

    <div class="main-content">
        <div class="row">
          <div class="col-md-6">
             <h5>@lang('form.items')</h5>
          </div>
          <div class="col-md-6">
                @if(check_perm('items_create')) 
                <a class="btn btn-primary btn-sm float-md-right" href="{{ route('add_item') }}" role="button">
                    @lang('form.new_item')
                </a>
                @endif
          </div>
       </div>
       <hr>

        
        
        @if(check_perm('items_view'))
           
            <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
                <thead>
                <tr>
                    <th>@lang("form.name")</th>
                    <th>@lang("form.description")</th>
                    <th>@lang("form.rate")</th>
                    <th>@lang("form.tax_1")</th>
                    <th>@lang("form.tax_2")</th>
                    <th>@lang("form.unit")</th>
                    <th>@lang("form.group_name")</th>
                </tr>
                </thead>
            </table>
        @endif
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
                    { className: "text-right", "targets": [2] }
                    // { className: "text-center", "targets": [5] }


                ],
                "ajax": {
                    "url": '{!! route("datatables_items") !!}',
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
@extends('layouts.main')
@section('title', __('form.vendors'))
@section('content')

        <div class="main-content">
            <div class="row">
            <div class="col-md-6">
                <h5>@lang('form.vendors')</h5>
            </div>
            <div class="col-md-6">
                 @if(check_perm('vendors_create'))  
                <a class="btn btn-primary btn-sm float-md-right" href="{{ route('add_vendor_page') }}">@lang('form.new_vendor')</a> 
               
                @endif  
            </div>
            
            
       </div>
        <hr>

              
            

        <div class="row">
            <div class="col-md-2 bd-highlight">
                <h5>{{ $data['stat']['vendor_active'] + $data['stat']['vendor_inactive'] }}</h5>
                <div>@lang('form.total_vendors')</div>
            </div>
            <div class="col-md-2 bd-highlight">
                <h5>{{ $data['stat']['vendor_active'] }}</h5>
                <div class="text-success">@lang('form.active_vendors')</div>
            </div>
            <div class="col-md-2 bd-highlight">
                <h5>{{ $data['stat']['vendor_inactive'] }}</h5>
                <div class="text-danger">@lang('form.inactive_vendors')</div>
            </div>
           
        </div>

        <hr>

        @if(check_perm('vendors_view'))   
        <div class="form-check">
        
          <input class="form-check-input" type="checkbox" name="exclude_inactive_vendors" id="exclude_inactive_vendors"
            value="1" checked>   
          <label class="form-check-label" for="defaultCheck1">
            @lang('form.exclude_inactive_vendors')
          </label>
        </div>
        @endif
       
 
 @if(check_perm('vendors_view'))     
        <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
            <thead>
            <tr>
                <th>@lang("form.name")</th>
                <th>@lang("form.ID")</th>
                <th>@lang("form.primary_contact")</th>
                <th>@lang("form.primary_email")</th>
                <th>@lang("form.primary_phone")</th>
                <th>@lang("form.active")</th>            
                <th>@lang("form.date_created")</th>
            </tr>
            </thead>
        </table>
    </div>

 @endif   

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
                pageLength: "{{ data_table_page_length() }}",
                ordering: false,
                "columnDefs": [
                    // { className: "text-right", "targets": [2,4] },
                    { className: "text-center", "targets": [4] }


                ],
                "ajax": {
                    "url": '{!! route("datatables_vendors") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {                        

                        if($('#exclude_inactive_vendors').is(':checked'))
                        {
                            d.exclude_inactive_vendors = true;

                        }

                       
                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });


            $("#exclude_inactive_vendors").click(function(){

                dataTable.draw();
            });

            

        });


        $(document).on('change','.vendor_status',function(e){

            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("change_vendor_status") }}", {
                "_token": "{{ csrf_token() }}",
                id : id,
                inactive : (this.checked) ? '' : 1

            });


        });



        


    </script>
@endsection
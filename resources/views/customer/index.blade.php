@extends('layouts.main')
@section('title', __('form.customers'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.customers')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right">
            @if(check_perm('customers_create'))  
            <a class="btn btn-primary btn-sm" href="{{ route('add_customer_page') }}">@lang('form.new_customer')</a>      
            <a class="btn btn-primary btn-sm" href="{{ route('import_customer_page') }}">@lang('form.import_customers')</a>
            @endif
            @if(check_perm('customers_view'))  
            <a class="btn btn-primary btn-sm" href="{{ route('customer_contacts') }}">@lang('form.contacts')</a>
            @endif           
         </div>
      </div>
   </div>
    <hr>
   <div class="row">
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['customer_active'] + $data['stat']['customer_inactive'] }}</h5>
         <div>@lang('form.total_customers')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['customer_active'] }}</h5>
         <div class="text-success">@lang('form.active_customers')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['customer_inactive'] }}</h5>
         <div class="text-danger">@lang('form.inactive_customers')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['contact_active'] }}</h5>
         <div class="text-primary">@lang('form.active_contacts')</div>
      </div>
      <div class="col-md-2 bd-highlight">
         <h5>{{ $data['stat']['contact_inactive'] }}</h5>
         <div class="text-danger">@lang('form.inactive_contacts')</div>
      </div>      
   </div>
   <hr>
   @if(check_perm('customers_view'))   
   <div class="form-check">
      <input class="form-check-input" type="checkbox" name="exclude_inactive_customers" id="exclude_inactive_customers"
         value="1" checked>   
      <label class="form-check-label" for="defaultCheck1">
      @lang('form.exclude_inactive_customers')
      </label>
   </div>
   @endif
   @if(check_perm('customers_view'))     
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.ID")</th>
            <th>@lang("form.primary_contact")</th>
            <th>@lang("form.primary_email")</th>
            <th>@lang("form.phone")</th>
            <th>@lang("form.active")</th>
            <th>@lang("form.groups")</th>
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
                    "url": '{!! route("datatables_customers") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {                        

                        if($('#exclude_inactive_customers').is(':checked'))
                        {
                            d.exclude_inactive_customers = true;

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


            $("#exclude_inactive_customers").click(function(){

                dataTable.draw();
            });

            

        });


        $(document).on('change','.customer_status',function(e){

            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("change_customer_status") }}", {
                "_token": "{{ csrf_token() }}",
                id : id,
                inactive : (this.checked) ? '' : 1

            });


        });



        


    </script>
@endsection
@extends('layouts.main')
@section('title', __('form.dashboard'))
@section('content')

<style>
div.dataTables_filter input {
    margin-left: 0 !important;
    
}
/*td div{
    display: none !important;
}*/
.hide{
    display: none;
}
table.dataTable {

    margin-top: 0 !important;
    border-top: 0 !important;
}
.dataTables_info{
    font-size: 13px;
}
</style>

    <div>

       @if(auth()->user()->is_administrator)
       <div class="main-content" style="margin-bottom: 10px !important;">
            @include('dashboard_stat')
       </div>
       @endif

        <div class="row">
             

            <div class="col-md-8">
                <div  class="main-content">
                   <h5> @lang('form.my_tasks')</h5>
                   <hr>
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
                        <thead>
                       <tr>
                            <th>@lang("form.task_#")</th>
                            <th>@lang("form.name")</th>
                            <th>@lang("form.status")</th>
                            <th style="display: none;">@lang("form.start_date")</th>
                            <th style="display: none;">@lang("form.due_date")</th>
                            <th style="display: none;">@lang("form.assigned_to")</th>
                            <th style="display: none;">@lang("form.tags")</th>
                            <th>@lang("form.priority")</th>
                            <th style="display: none;">@lang("form.created_by")</th>
                         </tr>
                        </thead>
                    </table>
         
                </div>
            </div>

             <div class="col-md-4">
               <div  class="main-content">
                    @include('todo')
                </div>    
            </div>  

         </div>   

        
    </div>
@endsection
@section('onPageJs')
    <script>
        $(function() {

        dataTable = "";
            
        dataTable = $('#data').DataTable({
                // "bInfo" : false,
                "bLengthChange": false,
                "language": {
                                "infoFiltered": ""
                },
                // "oLanguage": { "sSearch": "" } ,
                // "dom": '<"float-md-left"f><"float-md-right"l>tip',
                searching: false,
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: 5,
                ordering: false,
                "columnDefs": [
                    
                    { "visible": false, "targets": [3,4,5,6,8] },
                    { className: "text-left", "targets": [2] }

                   
          
                
                ],
                "ajax": {
                    "url": '{!! route("datatables_tasks") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                       
                        d.assigned_to = "{{ auth()->user()->id }}";
                       
                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

            

            $('.dataTables_filter input').attr("placeholder", "Search Here");

            
            <?php if(auth()->user()->is_administrator) { ?>

            $.post( "{{ route('dashboard_stat') }}" , { "_token": "{{ csrf_token() }}" })
              .done(function( data ) {

                if(data)
                {
                    $.each(data, function( key, row ) {
                        
                        
                        $("#" + key).width(row.percent + '%').html(row.percent + '%');
                        $("." + key).html( row.figure);


                    });
                }
               
              }, "json");

            <?php } ?>

            
    });

</script>

@yield('innerPageJs')
@endsection
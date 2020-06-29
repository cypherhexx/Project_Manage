@extends('layouts.main')
@section('title', __('form.report'). " - ". __('form.timesheet') )
@section('content')
<div class="main-content" style="margin-bottom: 20px !important;">
    <div class="row">
      <div class="col-md-6">
         <h5>{{ __('form.timesheet') ." " .__('form.report') }}</h5>
      </div>

    </div>
    <hr>

    <form>

   <div class="form-row">

  

        <div class="form-group col-md-3">
            <label for="name">@lang('form.logged_time')</label>
            <input type="text" class="form-control form-control-sm" id="reportrange" name="date" >                  

        </div>

        <div class="form-group col-md-2">
        <label>@lang('form.team_member')</label>
        <?php
            echo form_dropdown('team_member_id', $data['team_member_id_list'] , [], "class='form-control four-boot' ");
        ?>
        </div>

        <div class="form-group col-md-2">
            <label>@lang('form.customer')</label>
             <?php echo form_dropdown('customer_id', [] ,[], "class='form-control customer_id'"); ?>
                
         </div>       
         <div class="form-group col-md-2">
            <label>@lang('form.project')</label>
             <?php echo form_dropdown('project_id', [] ,[], "class='form-control project_id'  "); ?>
                
         </div> 
          <div class="form-group col-md-2">
                <button style="margin-top: 25px;" type="button" class="btn btn-primary apply">@lang('form.apply')</button>
          </div>
         


   </div>
  

 
</form>


   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
    <thead>
    <tr>
        <th>@lang("form.member")</th>
        <th>@lang("form.task")</th>
        <th>@lang("form.start_time")</th>
        <th>@lang("form.end_time")</th>
        <th>@lang("form.note")</th>
        <th>@lang("form.time")(@lang("form.h"))</th>
        <th>@lang("form.time")(@lang("form.decimal"))</th>     
        <th>@lang("form.options")</th>

    </tr>
    </thead>
</table>
</div>
@endsection

@section('onPageJs')
<script>
    
    $(function () {

        ranges = {
            '{{ __("form.today")}}': [moment(), moment()],
            '{{ __("form.yesterday")}}' : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],           
            '{{ __("form.this_month")}}' : [moment().startOf('month'), moment().endOf('month')],
            '{{ __("form.last_month")}}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '{{ __("form.this_week")}}': [moment().startOf('isoWeek') ,moment().endOf('isoWeek')],
            '{{ __("form.last_week")}}': [ moment().subtract(1, 'weeks').startOf('isoWeek')  , moment().subtract(1, 'weeks').endOf('isoWeek') ]           
           
        };

        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#reportrange span').html(start.format('D , MMMM , YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges : ranges,
            locale: {

              format: 'DD/MM/YYYY'
            }
        }, cb);

        cb(start, end);  

            
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
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("datatables_timesheet_report") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        
                        d.date_range        = $('input[name=date]').val();
                        d.team_member_id    = $('select[name=team_member_id]').val();
                        d.customer_id       = $('select[name=customer_id]').val();
                        d.project_id        = $('select[name=project_id]').val();

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


            $('.apply').click(function(e){
                e.preventDefault();

                dataTable.draw();
            });

            var customer_id = $( ".customer_id" );

            customer_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,

                placeholder: "{{ __('form.search') }}",
                allowClear: true,
                "language": {
                   "noResults": function(){
                       return "<?php echo __('form.no_results_found') ?>";
                   }
               },


                ajax: {
                    url: '{{ route("search_customer") }}',
                    data: function (params) {
                        return {
                            search: params.term
                        }


                    },
                    dataType: 'json',
                    processResults: function (data) {
                        //params.page = params.page || 1;
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.results
                            // pagination: {
                            //     more: (params.page * 10) < data.count_filtered
                            // }
                        };
                    }




                },
                
                templateResult: function (obj) {

                    return obj.name || "<?php echo __('form.searching'); ?>" ;
                },
                templateSelection: function (obj) {                   

                    return obj.name ||  obj.text
                }

            } );

            $( ".customer_id" ).on('select2:select', function(selection){
            
                   $( ".project_id" ).val(null).trigger('change');
            });
            $( ".customer_id" ).on('select2:unselect', function(selection){
            
                $( ".project_id" ).val(null).trigger('change');
            });



             // Project
            var project_id = $( ".project_id" );
           

            project_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,
                placeholder: "{{ __('form.search') }}",
                allowClear: true,

                ajax: {
                    url: '{{ route("get_project_by_customer_id") }}',
                    data: function (params) {
                        return {
                            search: params.term,
                            customer_id : $( ".customer_id" ).val()
                        }


                    },
                    dataType: 'json',
                    processResults: function (data) {
                        //params.page = params.page || 1;
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.results
                            // pagination: {
                            //     more: (params.page * 10) < data.count_filtered
                            // }
                        };
                    }




                },

                templateResult: function (obj) {

                    return obj.name || "<?php echo __('form.searching'); ?>" ;
                },
                templateSelection: function (obj) {

                    return obj.name ||  obj.text
                }

            } );


    });
        
</script>
@yield('innerPageJs')
@endsection
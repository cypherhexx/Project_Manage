@extends('layouts.customer.main')
@section('title', __('form.projects'))
@section('content')
<div class="main-content">
    <h4>@lang('form.projects')</h4>
    <hr>
    <div class="row">

        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['not_started'] }}</h5>
            <div>@lang('form.not_started')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['in_progress'] }}</h5>
            <div class="text-success">@lang('form.in_progress')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['on_hold'] }}</h5>
            <div class="text-danger">@lang('form.on_hold')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['cancelled'] }}</h5>
            <div class="text-primary">@lang('form.cancelled')</div>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $data['stat']['finished'] }}</h5>
            <div class="text-danger">@lang('form.finished')</div>
        </div>
 
    </div>
  
    <hr>
    <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.name")</th>    
            <th>@lang("form.start_date")</th>
            <th>@lang("form.dead_line")</th>
            <th>@lang("form.billing_type")</th>
            <th>@lang("form.status")</th>
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
                pageLength: 10,
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("customer_panel_datatable_projects_list") !!}',
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
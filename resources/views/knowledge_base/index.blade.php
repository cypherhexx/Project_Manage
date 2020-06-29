@extends('layouts.main')
@section('title', __('form.knowledge_base'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.knowledge_base')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right">                
            <a class="btn btn-primary btn-sm" href="{{ route('add_knowledge_base_article_page') }}" role="button">
            @lang('form.new_article')
            </a>
            <a class="btn btn-primary btn-sm" href="{{ route('knowledge_base_article_group_list') }}" role="button">
            @lang('form.groups')
            </a>
         </div>
      </div>
   </div>
   <hr>
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>            
            <th>@lang("form.internal")</th>
            <th>@lang("form.disabled")</th>
            <th>@lang("form.group")</th>
            <th>@lang("form.date_created")</th>
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
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                "columnDefs": [
                    { className: "text-right", "targets": [2] }
                    // { className: "text-center", "targets": [5] }


                ],
                "ajax": {
                    "url": '{!! route("datatables_knowledge_base_article") !!}',
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
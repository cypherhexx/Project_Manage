@extends('layouts.customer.main')
@section('title', __('form.project') . " : ". $rec->name)
@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
    @endphp

    <div class="main-content" style="margin-bottom: 20px !important;">

        <div class="row">
            <div class="col-md-6"><h4>{{ $rec->name }}</h4></div>
            <div class="col-md-6">

                @if(check_customer_project_permission($rec->settings->permissions, 'create_tasks')) 
                <!-- Button trigger modal -->
                 <button type="button" class="btn btn-primary btn-sm float-md-right" data-toggle="modal" data-target="#taskModal">
                 @lang('form.new_task')
                 </button>
                 @endif
            </div>
         </div>  

       <ul class="nav project-navigation">
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}"><i class="fas fa-th-list"></i> @lang('form.overview')</a>
            </li>

            @if(check_customer_project_permission($rec->settings->tabs, 'milestones'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('milestones', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=milestones"><i class="fas fa-rocket"></i> @lang('form.milestones')</a>
            </li>
            @endif

            @if(check_customer_project_permission($rec->settings->tabs, 'tasks'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('tasks', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=tasks"><i class="fas fa-check-circle"></i> @lang('form.tasks')</a>
            </li>
            @endif

            @if(check_customer_project_permission($rec->settings->tabs, 'timesheets'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('timesheets', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=timesheets"><i class="far fa-clock"></i> @lang('form.timesheets')</a>
            </li>
            @endif

            @if(check_customer_project_permission($rec->settings->tabs, 'files'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('files', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=files"><i class="far fa-file"></i> @lang('form.files')</a>
            </li>
           @endif

            {{--<li class="nav-item">--}}
                {{--<a class="nav-link" href="#"><i class="fas fa-comment-alt"></i> @lang('form.discussions')</a>--}}
            {{--</li>--}}

            @if(check_customer_project_permission($rec->settings->tabs, 'gantt_view'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('gantt', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=gantt"><i class="fas fa-chart-line"></i> @lang('form.gantt_view')</a>
            </li>
            @endif

            <!-- <li class="nav-item">
                <a class="nav-link" href="#"><i class="far fa-life-ring"></i> @lang('form.tickets')</a>
            </li> -->

            @if(check_customer_project_permission($rec->settings->tabs, 'invoices'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('invoices', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=invoices"><i class="fas fa-file"></i> @lang('form.invoices')</a>
            </li>
            @endif

            @if(check_customer_project_permission($rec->settings->tabs, 'estimates'))
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('estimates', $group_name) }}" href="{{ route('cp_show_project_page', $rec->id) }}?group=estimates"><i class="fas fa-file"></i> @lang('form.estimates')</a>
            </li>
            @endif

            {{--<li class="nav-item">--}}
                {{--<a class="nav-link" href="#"><i class="fas fa-sticky-note"></i> @lang('form.notes')</a>--}}
            {{--</li>--}}

            @if(check_customer_project_permission($rec->settings->tabs, 'activity'))
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-exclamation"></i> @lang('form.activity')</a>
            </li>
            @endif
            
        </ul>


    </div>
   
    <div class="main-content">

        @if($group_name == '')
            @include('customer_panel.project.partials.overview')
        @elseif($group_name == 'tasks' && $sub_group_name == '')
            @include('customer_panel.project.partials.tasks')
        @elseif($group_name == 'tasks' && $sub_group_name == 'details')
            @include('customer_panel.project.task.show')
        @elseif($group_name == 'tasks' && $sub_group_name == 'kanban')
            @include('customer_panel.project.partials.tasks_kanban_view')
        @elseif($group_name == 'timesheets')
            @include('customer_panel.project.partials.timesheets')
        @elseif($group_name == 'milestones')
            @include('customer_panel.project.partials.milestones')
        @elseif($group_name == 'invoices')
            @include('customer_panel.project.partials.invoices')
        @elseif($group_name == 'files' && check_customer_project_permission($rec->settings->tabs, 'files') )
            @include('customer_panel.project.partials.files')
        @elseif($group_name == 'estimates')
            @include('customer_panel.project.partials.estimates')    
     
        @elseif($group_name == 'gantt')
            @include('customer_panel.project.partials.gantt_chart')
            
        @endif

    </div>

@include('customer_panel.project.partials.modal_task')

@endsection

@section('onPageJs')

<script type="text/javascript">
    dataTable = "";
</script>
    @yield('innerPageJS')
    @yield('innerChildPageJs')
    <script>

        $(function() {

       
                 // Modal Stuffs

            $('#taskModal').on('show.bs.modal', function (event) {
 
                    var parent_task_id = $( ".parent_task_id" );

                    parent_task_id.select2( {
                        theme: "bootstrap",
                        minimumInputLength: 2,
                        maximumSelectionSize: 6,
                        placeholder: "{{ __('form.select_and_begin_typing') }}",
                        allowClear: true,
                        dropdownParent: $("#taskModal"),
                        ajax: {
                            url: '{{ route("get_parent_tasks", [COMPONENT_TYPE_PROJECT, $rec->id])  }}',

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

                            return obj.name;
                        },
                        templateSelection: function (obj) {

                            return obj.name ||  obj.text
                        }

                    } );


                // TinyMce
                tinymce.init({
                        selector: '#descriptionTextArea',  
                       
                        branding: false,
                        theme: "modern",
                        

                        plugins: [
                            "advlist autolink lists link charmap hr anchor pagebreak",
                            "wordcount visualblocks visualchars code fullscreen",
                            "media nonbreaking save table contextmenu",
                            "emoticons template paste textcolor colorpicker textpattern autoresize"
                        ],
                        toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                        

                });
            
            
            });



     
            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                

                var postData = $('#taskModalForm').serializeArray();      
                
                
                $.post( "{{ route("cp_post_task") }}" , postData )
                    .done(function( response ) {
                        if(response.status == 2)
                        {

                            $.each(response.errors, function( index, value ) {
                                
                                parentDiv = $('label[for='+  index  +']').parent();
                                parentDiv.find('.form-control').addClass('is-invalid');
                                parentDiv.find('.invalid-feedback').html(value.join());
                            });


                        }
                        else
                        {
                            if(dataTable)
                            {
                                dataTable.draw();
                            }
                            

                            $("#taskModal").find("input[type=text], textarea, input[type=hidden]").val("");
                            $("select").val(null).trigger("change"); 
                            $("#list_of_attachments").html("");
                            $('.attachment').remove();

                            tinyMCE.activeEditor.setContent('');
                          
                              if($("#create_new_checkbox").is(":checked"))
                              {
                                $('input[name=id]').val("");
                                
                              }
                              else
                              {
                                  $('#taskModal').modal('hide');
                              }

                              $.jGrowl(response.msg, { position: 'bottom-right'});
                        }
                    });



            });

 
            
            $('.form-control').on('focus', function(){

                    parentDiv = $(this).parent();
                    $(this).removeClass('is-invalid');
                    parentDiv.find('.invalid-feedback').html("");
            });



        });



    </script>


@endsection
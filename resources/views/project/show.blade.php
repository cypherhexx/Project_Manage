@extends('layouts.main')
@section('title', __('form.project') . " : ". $rec->name)
@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
    @endphp

    <div class="main-content" style="margin-bottom: 20px !important;">

        

        <div class="row" style="margin-bottom: 10px;">
        <div class="col-md-9">
               
               @if(count($data['other_projects']) > 0)  

              <button class="btn btn-link dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               {{ $rec->name }}
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                @foreach($data['other_projects'] as $other_project)
                    <a class="dropdown-item" href="{{ route('show_project_page', $other_project->id) }}">{{ $other_project->name }}</a>
                @endforeach
              </div>
              @else
                <h5>{{ $rec->name }}</h5>
              @endif
               
        </div>
        <div class="col-md-3">
             @if(check_perm(['projects_edit', 'projects_delete']))             

            <div class="dropdown float-md-right">
                <a class="btn btn-light btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               @lang('form.actions')
              </a>

              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
               @if(check_perm('projects_edit'))
                    <a class="dropdown-item" href="{{ route('edit_project_page', $rec->id) }}">@lang('form.edit_project')</a>
                    <div class="dropdown-divider"></div>
                    @if($rec->status_id != PROJECT_STATUS_NOT_STARTED)
                        <a class="dropdown-item project_status" href="#" data-id="{{ PROJECT_STATUS_NOT_STARTED }}">    @lang('form.mark_as') @lang('form.not_started')</a>
                    @endif

                    @if($rec->status_id != PROJECT_STATUS_IN_PROGRESS)
                        <a class="dropdown-item project_status" href="#" data-id="{{ PROJECT_STATUS_IN_PROGRESS }}">@lang('form.mark_as') @lang('form.in_progress')</a>
                    @endif

                    @if($rec->status_id != PROJECT_STATUS_ON_HOLD)
                        <a class="dropdown-item project_status" href="#" data-id="{{ PROJECT_STATUS_ON_HOLD }}">@lang('form.mark_as') @lang('form.on_hold')</a>
                    @endif

                    @if($rec->status_id != PROJECT_STATUS_CANCELLED)
                        <a class="dropdown-item project_status" href="#" data-id="{{ PROJECT_STATUS_CANCELLED }}">@lang('form.mark_as') @lang('form.cancelled')</a>
                    @endif

                    @if($rec->status_id != PROJECT_STATUS_FINISHED)
                        <a class="dropdown-item project_status" href="#" data-id="{{ PROJECT_STATUS_FINISHED }}">@lang('form.mark_as') @lang('form.finished')</a>
                    @endif
                <div class="dropdown-divider"></div>
                @endif
                
                 @if(check_perm('projects_delete'))
                    <a class="dropdown-item delete_item" href="{{ route('delete_project', $rec->id ) }}" style="color: red;">@lang('form.delete') 
                    @lang('form.project')</a>
                @endif
                </div>

             
            </div>
            <a href="#" style="margin-right: 10px;" class="btn btn-sm btn-primary float-md-right" id="invoice_project">@lang('form.invoice_project')</a>
            @endif 
        </div>
    </div>


        <ul class="nav project-navigation">
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}"><i class="fas fa-th-list"></i> @lang('form.overview')</a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('milestones', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=milestones"><i class="fas fa-rocket"></i> @lang('form.milestones')</a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('tasks', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=tasks"><i class="fas fa-check-circle"></i> @lang('form.tasks')</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('timesheets', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=timesheets"><i class="far fa-clock"></i> @lang('form.timesheets')</a>
            </li>



            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('files', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=files"><i class="far fa-file"></i> @lang('form.files')</a>
            </li>
            
            {{--<li class="nav-item">--}}
                {{--<a class="nav-link" href="#"><i class="fas fa-comment-alt"></i> @lang('form.discussions')</a>--}}
            {{--</li>--}}

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('gantt', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=gantt"><i class="fas fa-chart-line"></i> @lang('form.gantt_view')</a>
            </li>


            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('tickets', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=tickets"><i class="far fa-life-ring"></i> @lang('form.tickets')</a>
            </li>

            @if(check_perm(['invoices_view', 'expenses_view']) )
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ is_active_nav('sales', $group_name) }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-balance-scale"></i> @lang('form.sales')</a>
                <div class="dropdown-menu">
                    @if(check_perm('invoices_view') )
                    <a class="dropdown-item" href="{{ route('show_project_page', $rec->id) }}?group=sales&subgroup=invoices">@lang('form.invoices')</a>
                    @endif
                    @if(check_perm('expenses_view') )
                    <a class="dropdown-item" href="{{ route('show_project_page', $rec->id) }}?group=sales&subgroup=expenses">@lang('form.expenses')</a>
                    @endif
                </div>
            </li>
            @endif

            <!-- <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-sticky-note"></i> @lang('form.notes')</a>
            </li> -->

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('activity', $group_name) }}" href="{{ route('show_project_page', $rec->id) }}?group=activity"><i class="fas fa-exclamation"></i> @lang('form.activity')</a>
            </li>

        </ul>


    </div>

    <div class="main-content">

        @if($group_name == '')
            @include('project.partials.overview')
        @elseif($group_name == 'tasks' && $sub_group_name == '')
            @include('project.partials.tasks')
        @elseif($group_name == 'tasks' && $sub_group_name == 'kanban')
            @include('project.partials.tasks_kanban_view')
        @elseif($group_name == 'timesheets')
            @include('project.partials.timesheets')
        @elseif($group_name == 'milestones')
            @include('project.partials.milestones')
        @elseif($group_name == 'files')
            @include('project.partials.files')
        @elseif($group_name == 'tickets')
            @include('project.partials.tickets')            
        @elseif($group_name == 'sales' && $sub_group_name == 'invoices' && check_perm('invoices_view') )
            @include('project.partials.invoices')
        @elseif($group_name == 'sales' && $sub_group_name == 'expenses' && check_perm('expenses_view') )
            @include('project.partials.expenses')
        @elseif($group_name == 'gantt')
            @include('project.partials.gantt_chart')
        @elseif($group_name == 'activity')
            @include('project.partials.activities')    
        @endif

    </div>


<!-- Project status change modal -->
<!-- Modal -->

<div class="modal fade" id="changeProjectStatusModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        
               <div class="modal-header">
        <h5 class="modal-title">@lang('form.additional_action_required')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form  id="changeProjectStatusForm" action="{{ route('change_project_status', $rec->id) }}" method="POST">
      <div class="modal-body">
         {{ csrf_field()  }}
        <input type="hidden" name="project_status_id" id="project_status_id">
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="notify_project_members_status_change" value="1">
           
            <label>@lang('form.notify_project_members_status_change')</label>
        </div>

        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="mark_all_task_as_completed" value="1"> 
            
            <label>@lang('form.mark_all_tasks_as_completed')</label>
        </div>

      </div>
      <div class="modal-footer">
        
        <input type="button" class="btn btn-primary" id="submitConfirmForm" name="" value="{{ __('form.confirm') }}">
      </div>
        </form>    
    </div>
  </div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">@lang('form.invoice_project')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <form method="POST" action="{{ route('create_invoice_for_a_project', $rec->id) }}">
            {{ csrf_field()  }}
              <div class="modal-body"></div>       
      
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">@lang('form.invoice_project')</button>
              </div>

        </form>    
    </div>
  </div>
</div>

<!-- -->
@endsection

@section('onPageJs')

    @yield('innerPageJS')
    @yield('innerChildPageJs')
    <script>

        $(function() {

            $('.project_status').click(function(e){

                e.preventDefault();
                // alert($(this).data('id'));
                $("#changeProjectStatusModal").find("#project_status_id").val($(this).data('id'));
                $("#changeProjectStatusModal").modal('show');

            });

            $('#submitConfirmForm').on('click', function(e){
                console.log("Test");
          
                e.preventDefault();

            // Find form and submit it
            $('#changeProjectStatusForm').submit();
          });


            $("#invoice_project").click(function(e){
                e.preventDefault();
                

                $.get("{{ route('get_invoice_project_modal_content', $rec->id) }}")
                .done(function( response ) {
                     
                     $('#invoiceModal').find(".modal-body").html(response.html);
                    $('#invoiceModal').modal('show');    
                });



                
            });

        });



    </script>


@endsection
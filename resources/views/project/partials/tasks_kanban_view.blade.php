
<div class="row">
       <div class="col-md-6">
         
         <a class="btn btn-secondary btn-sm" href="{{ route('show_project_page', $rec->id) }}?group=tasks" role="button">
            @lang('form.switch_to_list_view')</a>

        </div>        

   </div>
   <hr>      
@include('task.partials.kanban_layout', ['task_list' => $rec->data_for_kanban_view])


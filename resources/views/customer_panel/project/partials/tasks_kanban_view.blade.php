<div class="row">
       <div class="col-md-6">         
         <a class="btn btn-secondary btn-sm" href="{{ route('cp_show_project_page', $rec->id) }}?group=tasks" role="button">
            @lang('form.switch_to_list_view')</a>
        </div>        
</div>
<hr>      
<div class="container">
<?php $m = $rec->milestones; ?>  

@if(count($m) > 0)

@foreach($m->chunk(3) as $milestones)

 <div class="row" style="margin-bottom: 60px;">

@foreach($milestones as $milestone)

  <div class="col-md-4">
    <div style="padding: 10px; background-color: #{{ ($milestone->background_color) ? $milestone->background_color : '4A9FF9'}}">
        {{ $milestone->name }}
        
    </div>
    <div style="background-color: #eee; padding: 10px; height: 100%;" data-milestone="{{ $milestone->id }}">
    
        <?php $tasks = $milestone->tasks()->get() ; ?>

            @if(count($tasks) > 0)
                @foreach($tasks as $task)
                    <div style="background-color: #fff; padding: 5px; margin-bottom: 10px;">  
                        <div  style="{{ ($task->status_id == TASK_STATUS_COMPLETE ) ? 'text-decoration: line-through;' : '' }}">
                        	<a style="font-size: 13px;" href="{{ route('cp_show_task_page', [$rec->id, $task->id] ) }}">
                        		{{ $task->title }}
                        	</a>
                        </div>
                        <small style="font-size: 12px;" class="form-text text-muted">
                        	@lang('form.status') : {{ $task->status->name }}
                        </small>
                        <small style="font-size: 12px;" class="form-text text-muted">
                        	@lang('form.start_date') {{ sql2date($task->start_date) }}
                        	- @lang('form.due_date') {{ sql2date($task->due_date) }}
                        </small>
                    </div>
                @endforeach
            @else
            	@lang('form.no_tasks_found')    
            @endif
        
     
     </div>
  </div>

@endforeach

</div>
@endforeach

@endif


</div>

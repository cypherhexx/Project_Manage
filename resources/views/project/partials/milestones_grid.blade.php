<?php $milestones = $rec->milestones; ?>  

@if(count($milestones) > 0)
<div class="kanban">

 <div class="board">

@foreach($milestones as $milestone)

  <div class="board-column">
    <div class="board-column-header" style="background-color: {{ ($milestone->background_color) ? $milestone->background_color : '#4A9FF9'}}">
        <a class="edit_item" data-id="{{ (check_perm('projects_edit')) ? $milestone->id : ''  }}" href="#" style="color: {{ ($milestone->background_text_color) ? $milestone->background_text_color : '#fff'}} !important;">{{ $milestone->name }}</a>
        
    </div>
    <div class="board-column-content-wrapper" data-milestone="{{ $milestone->id }}">
      <div class="board-column-content">
        <?php $tasks = $milestone->tasks()->get() ; ?>

            @if(count($tasks) > 0)
                @foreach($tasks as $task)
                    <div class="board-item" data-task="{{ $task->id }}" >
                        <div class="board-item-content">
                            <div><a style="font-size: 13px;" href="{{ route('show_task_page', $task->id ) }}">{{ $task->title }}</a></div>
                        <small style="font-size: 12px;" class="form-text text-muted">{{ $task->status->name }}</small>
                        </div>
                    </div>
                @endforeach
            @endif
        
      </div>
     </div>
  </div>

@endforeach

</div>
</div>

@endif


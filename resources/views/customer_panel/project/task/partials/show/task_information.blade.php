<div>@lang('form.task_information')</div>
<p >
    <small>
        @lang('form.created_by') {{ $task->person_created->name }}
        <i data-toggle="tooltip" data-placement="top" title="@lang('form.created_at') {{ sql2date($task->created_at) }}" class="far fa-clock"></i>

    </small>
  
</p>

<table class="table" style="font-size: 13px;">
    <tr>
        <td><i class="fas fa-star"></i> @lang('form.status')</td>
        <td>{{ $task->status->name }}</a></td>
 
        


    </tr>

    <tr>
        <td><i class="fas fa-calendar-alt"></i> @lang('form.start_date')</td>
        <td>{{ sql2date($task->start_date) }}</td>
    </tr>


    <tr>
        <td><i class="far fa-calendar-check"></i> @lang('form.due_date')</td>
        <td>{{ ($task->due_date) ? sql2date($task->due_date) : '' }}</td>
    </tr>

    <tr>
        <td><i class="fas fa-bolt"></i> @lang('form.priority')</td>
        <td>{{ $task->priority->name }}</td>
    </tr>

    <tr>
        <td><i class="far fa-credit-card"></i> @lang('form.billable')</td>
        <td>{{ ($task->is_billable) ? __('form.yes') : __('form.no') }}</td>
    </tr>

    <tr>
        <td><i class="far fa-clock"></i> @lang('form.hourly_rate')</td>
        <td>{{ $task->hourly_rate }}</td>
    </tr>    

    
    <tr>
        <td>@lang('form.related_to')</td>
        <td>
            @if($task->component_id && $task->component_number)
                {{ (isset($task->related_to->name)) ? $task->related_to->name : '' }}
            @else
                
            @endif
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-user"></i> @lang('form.assigned_to')</td>
        <td>{{ (isset($task->assigned_user->first_name) && $task->assigned_user->first_name) ? $task->assigned_user->first_name . " ". $task->assigned_user->last_name : "" }}
           
        </td>
    </tr>
    
   
</table>
    <hr>
<div>
    @lang('form.tags')
    <p><?php echo $task->get_tags_as_badges(); ?></p>
</div>

@if(check_customer_project_permission($rec->settings->permissions, 'view_task_attachments'))
<div>
    @lang('form.attachments')
   <ul class="list-group">
        <?php $attachments = $task->attachments()->get(); ?>
        @if(count($attachments) > 0)
            @foreach ($attachments as $key =>$attachment) 
                <li class="list-group-item">

                  <a href="{{ route('attachment_download_link', Crypt::encryptString($attachment->name) ) }}">
                        <i class="fas fa-file"></i> {{ $attachment->display_name }}

                    </a>
                    <br>
                    <a href="{{ route('remove_attachment', $attachment->id) }}" class="delete_item">
                        <i class="far fa-trash-alt"></i> 
                    </a> 
                </li>
            
            
            @endforeach
        @endif
    </ul>
    
</div>
@endif
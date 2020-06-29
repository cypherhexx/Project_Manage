<div>@lang('form.task_information') 
    @if(check_perm('tasks_edit'))
    <a href="{{ route('edit_task_page', $rec->id) }}"><i class="far fa-edit"></i> @lang('form.edit')</a> 
    @endif
</div>
<p >
    <small>
        @lang('form.created_by') {{ $rec->person_created->name }}
        <i data-toggle="tooltip" data-placement="top" title="@lang('form.created_at') {{ sql2date($rec->created_at) }}" class="far fa-clock"></i>

    </small>
  
</p>

<table class="table" style="font-size: 13px;">
    <tr>
        <td><i class="fas fa-star"></i> @lang('form.status')</td>
        <td><a href="#" class="statusName"></a>
            <div class="dropdown" >
                <button style="padding-top: 0 !important; padding-left: 0 !important;" class="btn btn-sm btn-link dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ $rec->status->name }}
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    @if(isset($data['status_id_list']))
                        @foreach($data['status_id_list'] as $row)
                            @if($row->id != $rec->status_id)
                                <a class="dropdown-item" href="{{ route('task_change_status', [$rec->id, $row->id]) }}">{{ $row->name }}</a>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </td>


    </tr>

    <tr>
        <td><i class="fas fa-calendar-alt"></i> @lang('form.start_date')</td>
        <td>{{ sql2date($rec->start_date) }}</td>
    </tr>


    <tr>
        <td><i class="far fa-calendar-check"></i> @lang('form.due_date')</td>
        <td>{{ ($rec->due_date) ? sql2date($rec->due_date) : '' }}</td>
    </tr>

    <tr>
        <td><i class="fas fa-bolt"></i> @lang('form.priority')</td>
        <td>{{ $rec->priority->name }}</td>
    </tr>

    <tr>
        <td><i class="far fa-credit-card"></i> @lang('form.billable')</td>
        <td>{{ ($rec->is_billable) ? __('form.yes') : __('form.no') }}</td>
    </tr>

    <tr>
        <td><i class="far fa-clock"></i> @lang('form.hourly_rate')</td>
        <td>{{ $rec->hourly_rate }}</td>
    </tr>    

    
    <tr>
        <td>@lang('form.related_to')</td>
        <td>
            @if($rec->component_id && $rec->component_number)
            <a data-toggle="tooltip" data-placement="top" title="{{ $rec->component->name }}" href="{{ route(get_url_route_name_by_component_id($rec->component_id), $rec->component_number) }}">
                {{ (isset($rec->related_to->name)) ? $rec->related_to->name : '' }}
            </a>
            @else
                @lang('form.general')
            @endif
        </td>
    </tr>

    <tr>
        <td><i class="fas fa-user"></i> @lang('form.assigned_to')</td>
        <td><!-- {{ (isset($rec->assigned_user->first_name) && $rec->assigned_user->first_name) ? $rec->assigned_user->first_name . " ". $rec->assigned_user->last_name : "" }} -->
            <?php
      echo form_dropdown('assigned_to', $data['assigned_to_list'] , old_set('assigned_to', NULL,$rec), "class='form-control selectpicker assigned_to'");
      ?>

        </td>
    </tr>
    
   
</table>
    <hr>
<div>
    @lang('form.tags')
    <p><?php echo $rec->get_tags_as_badges(); ?></p>
</div>


<div>
    @lang('form.attachments')
   <ul class="list-group">
                            <?php $attachments = $rec->attachments()->get(); ?>
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
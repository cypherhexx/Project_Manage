<?php $sub_tasks = $rec->sub_tasks()->get(); ?>

@if(count($sub_tasks) > 0)
        <hr>
        <small><b>@lang('form.sub_tasks')</b></small>
        <br>
    <ul style="list-style-type: none; padding-left: 0!important; font-size: 13px;">
        @foreach($sub_tasks as $key=>$sub_task)
            <li>{{ $key + 1 }}. <a href="{{ route('show_task_page', $sub_task->id) }}">{{ $sub_task->title }}</a> {{ $sub_task->status->name }}</li>
        @endforeach
    </ul>
@endif
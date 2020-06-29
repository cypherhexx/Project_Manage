<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#taskModal">
@lang('form.new_task')
</button>

<a class="btn btn-secondary btn-sm" href="{{ route('show_project_page', $rec->id) }}?group=tasks&subgroup=kanban" role="button">@lang('form.switch_to_canban_view')</a>

<hr>
<div>
    <?php $task_summary =  $rec->get_task_summary(); ?>
    <div class="row">
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_BACKLOG] }}</h5>
            <span class="text-secondary">@lang('form.back_log')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_IN_PROGRESS] }}</h5>
            <span class="text-primary">@lang('form.in_progress')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_TESTING] }}</h5>
            @lang('form.testing')
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_AWAITING_FEEDBACK] }}</h5>
            <span class="text-warning">@lang('form.awaiting_feedback')</span>
        </div>
        <div class="col-md-2 bd-highlight">
            <h5>{{ $task_summary[TASK_STATUS_COMPLETE] }}</h5>
            <span class="text-success">@lang('form.complete')</span>
        </div>
    </div>


</div>

<hr>


<?php task_modal(COMPONENT_TYPE_PROJECT, $rec); ?>
<?php echo task_table_html(); ?>


@section('innerPageJS')
 <?php task_table_js(COMPONENT_TYPE_PROJECT, $rec->id, TRUE); ?>
@endsection
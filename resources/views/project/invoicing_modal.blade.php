@if($rec->billing_type_id == BILLING_TYPE_FIXED_RATE)
<div class="custom-control custom-radio">
  <input type="radio" class="custom-control-input" id="single_line" name="invoice_record_style" value="single_line" checked>
  <label class="custom-control-label" for="single_line">@lang('form.single_line')</label>
</div>
@else
<div class="custom-control custom-radio">
  <input type="radio" class="custom-control-input" id="task_per_item" name="invoice_record_style" value="task_per_item" checked>
  <label class="custom-control-label" for="task_per_item">@lang('form.task_per_item')</label>
</div>
<div class="custom-control custom-radio">
  <input type="radio" class="custom-control-input" id="all_timesheets_individually" name="invoice_record_style" value="all_timesheets_individually">
  <label class="custom-control-label" for="all_timesheets_individually">@lang('form.all_timesheets_individually')</label>
</div>
@endif

<?php $tasks = $rec->tasks; ?>

@if(count($tasks) > 0)

<hr>
<div style="font-weight: bold; margin-bottom: 20px;">@lang('form.tasks')</div>

<div class="custom-control custom-checkbox float-md-left">
<input type="checkbox" class="custom-control-input" id="select_all_tasks">
<label class="custom-control-label" for="select_all_tasks">@lang('form.select_all_tasks')</label>
</div>
<div class="text-danger float-md-right">@lang('form.all_billed_tasks_will_be_marked_as_finished')</div>
<div class="clearfix"></div>
<table class="table table-sm" style="font-size: 14px; margin-top: 20px;">
<tbody>
	@foreach($tasks as $task)
		@if($task->unbilled_timesheets->count() > 0)
	<tr>

		<td>
			<div class="custom-control custom-checkbox">
			  <input {{ ($task->status_id == TASK_STATUS_COMPLETE) ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="checkbox_{{ $task->id }}" name="task_ids[]" value="{{ $task->id }}">
			  <label class="custom-control-label" for="checkbox_{{ $task->id }}">{{ $task->title }}</label>
			</div>
			</td>
		<td class="text-right">{{ $task->status->name }}</td>
	</tr>
		@endif
	@endforeach
</tbody>
</table>
@endif


<?php $expenses = $rec->unbilled_expenses; ?>

@if(count($expenses) > 0)
<hr>
<div style="font-weight: bold; margin-bottom: 20px;">@lang('form.expenses')</div>

<div class="custom-control custom-checkbox float-md-left">
<input type="checkbox" class="custom-control-input" id="select_all_expenses">
<label class="custom-control-label" for="select_all_expenses">@lang('form.select_all_expenses')</label>
</div>

<div class="clearfix"></div>
<table class="table table-sm" style="font-size: 14px; margin-top: 20px;">
<tbody>
	@foreach($expenses as $expense)
	<tr>

		<td>
			<div class="custom-control custom-checkbox">
			  <input checked type="checkbox" class="custom-control-input" id="checkbox_{{ $expense->id }}" name="expense_ids[]" value="{{ $expense->id }}">
			  <label class="custom-control-label" for="checkbox_{{ $expense->id }}">{{ $expense->category->name }}</label>
			</div>
			</td>
		<td class="text-right">{{ $expense->name }}</td>
	</tr>
	@endforeach
</tbody>
</table>
@endif

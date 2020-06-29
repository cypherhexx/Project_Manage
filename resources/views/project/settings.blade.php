<?php 

	function check_box_fill($data_object, $key)
	{
		if((isset($data_object->{$key}) && $data_object->{$key} ) || 
			( is_array($data_object) && isset($data_object[$key]) && $data_object[$key]) 
		)
		{
			echo 'checked';
		}
		

	}


	$permissions 	= old_set('permissions', NULL, $rec);
	$tabs 			= old_set('tabs', NULL, $rec);

	
?>

<div id="project-permissions">
	
<div class="row">

	<div class="col-md-12"><div style="font-weight: bold; font-size: 13px;">@lang('form.visible_tabs')</div></div>

	<div class="col-md-6">
		<div class="checkbox">
	      <input  type="checkbox" name="tabs[tasks]" {{ check_box_fill($tabs, 'tasks') }} > 
	      <label for="view_tasks">@lang('form.tasks')</label>                                  
	   </div>
	   <div class="checkbox">
	      <input  type="checkbox" name="tabs[timesheets]" {{ check_box_fill($tabs, 'timesheets') }} > 
	      <label for="view_tasks">@lang('form.timesheets')</label>                                  
	   </div>
      <div class="checkbox">
         <input  type="checkbox" name="tabs[files]" {{ check_box_fill($tabs, 'files') }} > 
         <label for="view_tasks">@lang('form.files')</label>                                  
      </div>

	   <div class="checkbox">
	      <input  type="checkbox" name="tabs[milestones]" {{ check_box_fill($tabs, 'milestones') }} > 
	      <label for="view_tasks">@lang('form.milestones')</label>                                  
	   </div>
	</div>	


	<div class="col-md-6">
		<div class="checkbox">
      <input  type="checkbox" name="tabs[gantt_view]" {{ check_box_fill($tabs, 'gantt_view') }} > 
      <label for="view_tasks">@lang('form.gantt_view')</label>                                  
   </div>
   <div class="checkbox">
      <input  type="checkbox" name="tabs[invoices]" {{ check_box_fill($tabs, 'invoices') }} > 
      <label for="view_tasks">@lang('form.invoices')</label>                                  
   </div>
   <div class="checkbox">
      <input  type="checkbox" name="tabs[estimates]" {{ check_box_fill($tabs, 'estimates') }} > 
      <label for="view_tasks">@lang('form.estimates')</label>                                  
   </div>
  <!--  <div class="checkbox">
      <input  type="checkbox" name="tabs[activity_log]" {{ check_box_fill($tabs, 'activity_log') }} > 
      <label for="view_tasks">@lang('form.activity_log')</label>                                  
   </div> -->

	</div>	

   

</div>
<hr>
<div id="project-settings-area">



   <div class="checkbox">
      <input  type="checkbox" name="permissions[view_tasks]" {{ check_box_fill($permissions, 'view_tasks') }} > 
      <label for="view_tasks">@lang('form.allow_customer_to_view_tasks')</label>                                  
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[create_tasks]" {{ check_box_fill($permissions, 'create_tasks') }}  >
      <label for="create_tasks">@lang('form.allow_customer_to_create_tasks')</label>                                  
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[edit_tasks]" {{ check_box_fill($permissions, 'edit_tasks') }}   >
      <label for="edit_tasks">@lang('form.allow_customer_to_edit_tasks')
      </label>
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[comment_on_tasks]" {{ check_box_fill($permissions, 'comment_on_tasks') }} >
      <label for="comment_on_tasks">@lang('form.allow_customer_to_comment_on_tasks')</label>
                                  
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[view_task_comments]" {{ check_box_fill($permissions, 'view_task_comments') }} >
      <label for="view_task_comments">@lang('form.allow_customer_to_view_task_comments')</label>
                                  
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[view_task_attachments]" {{ check_box_fill($permissions, 'view_task_attachments') }} >
      <label for="view_task_attachments">@lang('form.allow_customer_to_view_task_attachments')</label>
                                  
   </div>
  <!--  <div class="checkbox">
      <input type="checkbox" name="permissions[view_task_checklist_items]" {{ check_box_fill($permissions, 'view_task_checklist_items') }} >
      <label for="view_task_checklist_items">@lang('form.allow_customer_to_view_task_checklist_items')</label>
                                 
   </div> -->
   <div class="checkbox">
      <input type="checkbox" name="permissions[upload_on_tasks]" {{ check_box_fill($permissions, 'upload_on_tasks') }} >
      <label for="upload_on_tasks">@lang('form.allow_customer_to_upload_attachment_on_tasks')</label>
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[view_task_total_logged_time]" {{ check_box_fill($permissions, 'view_task_total_logged_time') }} >
      <label for="view_task_total_logged_time">@lang('form.allow_customer_to_view_task_total_logged_time')</label>
                                  
   </div>
   <div class="checkbox">
      <input type="checkbox" name="permissions[view_finance_overview]" {{ check_box_fill($permissions, 'view_finance_overview') }} >
      <label for="view_finance_overview">@lang('form.allow_customer_to_view_finance_overview')</label>
                                  
   </div>
   <!-- <div class="checkbox">
      <input type="checkbox" name="permissions[upload_files]" {{ check_box_fill($permissions, 'upload_files') }} >
      <label for="upload_files">@lang('form.allow_customer_to_upload_files')</label>                                  
   </div>

   <div class="checkbox">
      <input type="checkbox" name="permissions[open_discussions]" {{ check_box_fill($permissions, 'open_discussions') }} >
      <label for="open_discussions">@lang('form.allow_customer_to_open_discussions')</label>                                 
   </div> -->

   <div class="checkbox">
      <input type="checkbox" name="permissions[view_milestones]" {{ check_box_fill($permissions, 'view_milestones') }} >
      <label for="view_milestones">@lang('form.allow_customer_to_view_milestones')</label>                                  
   </div>

   <div class="checkbox">
      <input type="checkbox" name="permissions[view_gantt]" {{ check_box_fill($permissions, 'view_gantt') }} >
      <label for="view_gantt">@lang('form.allow_customer_to_view_gantt')</label>                                  
   </div>

   <div class="checkbox">
      <input type="checkbox" name="permissions[view_timesheets]" {{ check_box_fill($permissions, 'view_timesheets') }} >
      <label for="view_timesheets">@lang('form.allow_customer_to_view_timesheets')</label>
                                  
   </div>
   <!-- <div class="checkbox">
      <input type="checkbox" name="permissions[view_activity_log]" {{ check_box_fill($permissions, 'view_activity_log') }} >
      <label for="view_activity_log">@lang('form.allow_customer_to_view_activity_log')</label>
                                  
   </div> -->
   <div class="checkbox">
      <input type="checkbox" name="permissions[view_team_members]" {{ check_box_fill($permissions, 'view_team_members') }} >
      <label for="view_team_members">@lang('form.allow_customer_to_view_team_members')</label>
                                  
   </div>
    <div class="checkbox">
      <input type="checkbox" name="permissions[upload_files]" {{ check_box_fill($permissions, 'upload_files') }} >
      <label for="view_team_members">@lang('form.allow_customer_to_upload_files')</label>
                                  
   </div>
   
</div>
</div>
@section('innerPageJs')
	
	<script type="text/javascript">
		$(function(){

			<?php if(!(isset($rec->id)) && empty($permissions)) { ?>
				$('#project-permissions input[type=checkbox]').prop('checked', true);
			<?php } ?>		

		});
	</script>
 @endsection
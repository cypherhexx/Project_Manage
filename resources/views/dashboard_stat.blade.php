<div class="row" style="font-size: 13px;">
    <div class="col-md-3">
        @lang('form.invoices_awaiting_payment')  : <span class="invoices_awaiting_payment"></span>
        <?php gen_progress_bar('bg-danger', 0, 'invoices_awaiting_payment') ;?>
    </div>

    <div class="col-md-3">
        @lang('form.converted_leads') : <span class="converted_leads"></span>
        <?php gen_progress_bar('bg-success', 0, 'converted_leads') ;?>
    </div>

    <div class="col-md-3">
        @lang('form.projects_in_progress') : <span class="projects_in_progress"></span>
        <?php gen_progress_bar('bg-info', 0, 'projects_in_progress') ;?>
    </div>

    <div class="col-md-3">
        @lang('form.tasks_not_finished') : <span class="tasks_not_finished"></span>
        <?php gen_progress_bar('bg-secondary', 0, 'tasks_not_finished') ;?>
    </div>

</div>




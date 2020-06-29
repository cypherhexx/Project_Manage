    <?php $time_sheet_stat = $rec->timesheet_stat(); ?>
        <div class="row">
                <div class="col-md-3">
                    <span class="text-secondary">@lang('form.logged_hours')
                    {{ $time_sheet_stat['logged_hours'] }}</span>

                </div>
                <div class="col-md-3">
                    <span class="text-primary">@lang('form.billable_hours')
                    {{ $time_sheet_stat['billable_hours'] }}</span>

                </div>
                <div class="col-md-3">
                    <span class="text-success">@lang('form.billed_hours')
                    {{ $time_sheet_stat['billed_hours'] }}</span>

                </div>
                <div class="col-md-3">
                    <span class="text-danger">@lang('form.unbilled_hours')
                    {{ $time_sheet_stat['unbilled_hours'] }}</span>

                </div
         </div>
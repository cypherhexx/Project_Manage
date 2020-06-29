    <?php $expenses_stat = $rec->expenses_stat(); ?>
        <div class="row">
                <div class="col-md-3">
                    <span class="text-secondary">@lang('form.total_expenses')</span>                   
                   <div>{{ format_currency($expenses_stat['total_expenses'], TRUE)  }}</div>

                </div>
                <div class="col-md-3">
                    <span class="text-primary">@lang('form.billable_expenses')</span>
                    <div>{{ format_currency($expenses_stat['billable_expenses'], TRUE) }}</div>

                </div>
                <div class="col-md-3">
                    <span class="text-success">@lang('form.billed_expenses')</span>
                    <div>{{ format_currency($expenses_stat['billed_expenses'], TRUE) }}</div>

                </div>
                <div class="col-md-3">
                    <span class="text-danger">@lang('form.unbilled_expenses')</span>
                    <div>{{ format_currency($expenses_stat['unbilled_expenses'], TRUE) }}</div>

                </div>
         </div>
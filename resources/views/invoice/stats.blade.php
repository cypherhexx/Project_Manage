<?php 

 $stat 	= $data['stat_unpaid_invoices'];
 $total = $data['stat_total_unpaid_invoices'];
?>
    <div class="row">
        <div class="col-md-6">
            <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                <div class="text-danger">{{ format_currency($data['total_outstanding'], TRUE) }}</div>
                @lang('form.outstanding_invoices')
            </div>
        </div>

        <div class="col-md-6">
            <div class="shadow-sm p-1 mb-5 bg-light rounded text-center">
                <div class="text-success">{{ format_currency($data['paid_invoices'], TRUE) }}</div>
                @lang('form.paid_invoices') (@lang('form.last_30_days'))
            </div>
        </div>

    </div>

    <div class="row" style="font-size: 13px;">
        <div class="col-md-3">
            @lang('form.unpaid') : {{ $stat[INVOICE_STATUS_UNPAID]['number'] }} / {{ $total }}
            <?php gen_progress_bar('bg-danger', $stat[INVOICE_STATUS_UNPAID]['percent']) ;?>
        </div>

        <div class="col-md-3">
            @lang('form.partially_paid') : {{ $stat[INVOICE_STATUS_PARTIALLY_PAID]['number'] }} / {{ $total }}
            <?php gen_progress_bar('bg-warning', $stat[INVOICE_STATUS_PARTIALLY_PAID]['percent'] ) ;?>
        </div>

        <div class="col-md-3">
            @lang('form.over_due') : {{ $stat[INVOICE_STATUS_OVER_DUE]['number'] }} / {{ $total }}
            <?php gen_progress_bar('bg-info', $stat[INVOICE_STATUS_OVER_DUE]['percent'] ) ;?>
        </div>

        <div class="col-md-3">
            @lang('form.draft') : {{ $stat[INVOICE_STATUS_DRAFT]['number'] }} / {{ $total }}
            <?php gen_progress_bar('bg-secondary', $stat[INVOICE_STATUS_DRAFT]['percent'] ) ;?>
        </div>

    </div>

    <br>

    
<?php $customer = $rec->invoice->customer; ?>

<htmlpageheader name="firstpageheader" style="display:none">
    <div class="row">
      <div class="col-md-6">
         <div style="margin-top: 23px;"><img src="{{ get_company_logo() }}"></div>
      </div>
      <div class="col-md-6 text-right">
         <h1 style="margin-bottom: 0; color: #007bff; font-size: 26px;">{{  strtoupper(__('form.receipt')) }}</h1>
         <div>{{ $rec->number }}</div>
      </div>
   </div>

    <div class="row"  style="margin-top: 20px;">
        <div class="col-md-6">

            <div><b>{{ Config::get('constants.company_name') }}</b></div>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
        </div>

        <div class="col-md-6">

            <div class="text-right">
                <div><b>{{ $customer->name }}</b></div>
                <div>{{ $customer->address}}</div>
                <div>{{ $customer->city}} {{ $customer->state}}</div>
                <?php $country = $customer->country; ?>
                <div>{{ (isset($country->name)) ? $country->name : ''}} {{ $customer->zip_code}}</div>
            </div>
        </div>
    </div>
    
    

  </htmlpageheader>

  <div class="row">
        <div class="col-md-6">
            @lang('form.payment_mode') : {{ $rec->payment_mode->name }} {{ $rec->payment_method }}
        </div>

        <div class="col-md-6 text-right">
            <div>@lang('form.payment_date') : {{ sql2date($rec->date) }}</div>
            
        </div>

    </div>


<h5>@lang('form.payment_for') : </h5>

<table class="table" style="font-size: 13px;">
    <thead>
        <tr>
            <th style="text-align: left;">@lang('form.invoice_number')</th>
            <th>@lang('form.invoice_date')</th>
            <th class="text-right">@lang('form.invoice_amount')</th>
            <th class="text-right">@lang('form.payment_amount')</th>
            <th class="text-right">@lang('form.amount_due')</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $rec->invoice->number }}</td>
            <td>{{ sql2date($rec->invoice->date) }}</td>
            <td class="text-right">{{ format_currency($rec->invoice->total, TRUE, $rec->invoice->get_currency_symbol() )}}</td>
            <td class="text-right">{{ format_currency($rec->amount, TRUE, $rec->invoice->get_currency_symbol() ) }}</td>
            <?php 
                $amount_due = $rec->invoice->total - ($rec->invoice->amount_paid + $rec->invoice->applied_credits) ;
            ?>

            <td class="text-right">{{ format_currency($amount_due, TRUE, $rec->invoice->get_currency_symbol() ) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr style="font-weight: bold">
            <td colspan="3">@lang('form.total_receipt_amount')</td>
            <td class="text-right">{{ format_currency($rec->amount, TRUE, $rec->invoice->get_currency_symbol() ) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
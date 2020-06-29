<?php 
    
    function get_details($row)
    {
        if($row->type == 'invoice')
        {
            $invoice_link       = anchor_link($row->number, route('invoice_customer_view', [$row->id, $row->info_2 ]));
            $due_information    = ($row->info_1) ? " - ".__('form.due_on') . " ". $row->info_1 : '';
            return __('form.invoice'). " ". $invoice_link . " ". $due_information ;
        }
        elseif($row->type == 'credit_note')
        {         
            return __('form.credit_note'). " ". $row->number ;
        }
        elseif($row->type == 'payment')
        {         
            return __('form.payment'). " ". $row->number . ' '. __('form.for_invoice'). ' '.  $row->info_1;
        }
        elseif($row->type == 'applied_credit')
        {         
            return __('form.applied_credit'). " ". $row->number . ' '. __('form.of_amount').'  '. $row->amount. ' '. __('form.to_invoice'). ' '.  $row->info_1;
        }
    }

    function get_address($rec)
    {
        $str  = "";
        $str .= nl2br($rec->address);
        $str .= "<br>" . $rec->city ;
        $str .= "<br>" . $rec->state ;
        $str .= "<br>" . $rec->zip_code ;

        if(isset($rec->country->name))
        {
            $str .= "<br>" . $rec->country->name ;
        }

        return $str;
    }

?>

<div class="statement-container" style="font-size: 13px;">
<htmlpageheader name="firstpageheader" style="display:none">
<div class="row">
    <div class="col-md-6"><img class="d-none" src="{{ get_company_logo() }}"></div>
    <div class="col-md-6">
         <div style="text-align: right;">
             <div><b>{{ Config::get('constants.company_name') }}</b></div>
            <address>
                <?php echo Config::get('constants.company_full_address') ?>
            </address>
         </div>
    </div>
</div>
</htmlpageheader>


<div class="row">
    <div class="col-md-7">
        <div>@lang('form.to')</div>
        <div><b>{{ auth()->user()->customer->name }}</b></div>
        <div><?php echo get_address(auth()->user()->customer);  ?></div>
    </div>
    <div class="col-md-5">
         <div style="text-align: right;">
             <h4>@lang('form.accounts_summary')</h4>

             <table class="table">
                 <tr>
                     <td style="text-align: left;">@lang('form.beginning_balance')</td>
                     <td class="text-right">{{ format_currency($data['beginning_balance'], TRUE, $data['currency_symbol'] ) }}</td>
                 </tr>
                 <tr>
                     <td style="text-align: left;">@lang('form.invoiced_amount')</td>
                     <td class="text-right">{{ format_currency($data['invoiced_amount'], TRUE, $data['currency_symbol'] ) }}</td>
                 </tr>
                 <tr>
                     <td style="text-align: left;">@lang('form.amount_paid')</td>
                     <td class="text-right">{{ format_currency($data['payment_amount'], TRUE, $data['currency_symbol'] ) }}</td>
                 </tr>
                 <tr>
                     <td style="text-align: left;">@lang('form.balance_due')</td>
                     <td class="text-right">{{ format_currency($data['balance_due'], TRUE, $data['currency_symbol'] ) }}</td>
                 </tr>
             </table>
         </div>

    </div>
</div>

<div style="text-align: center;">
@lang('form.showing_all_invoices_and_payments_between') {{ $data['date_from'] }} @lang('form.and') {{ $data['date_to'] }}</div>
<br>
<div class="table-responsive">
<table class="table table-striped table-bordered table-sm" width="100%" id="data">
    <thead>
        <tr>
            <th>@lang("form.date")</th>
            <th>@lang("form.details")</th>
            <th class="text-right">@lang("form.amount")</th>        
            <th class="text-right">@lang("form.payment")</th>
            <th class="text-right">@lang("form.balance")</th>                        
        </tr>
    </thead>

    <tbody>
     <?php $balance = $data['beginning_balance']; ?>
            <tr>
                <td>{{ date("d-m-Y", strtotime($data['date_from']))  }}</td>
                <td>@lang('form.beginning_balance')</td>
                <td class="text-right">{{ format_currency($balance) }}</td>
                <td class="text-right"></td>
                <td class="text-right">{{ format_currency($balance , TRUE, $data['currency_symbol'] ) }}</td>               
            </tr>
        @if(count($rec) > 0)       
            @foreach($rec as $row)
                <tr>
                    <td>{{ sql2date($row->date) }}</td>
                    <td><?php echo get_details($row) ; ?></td>
                    <td class="text-right">
                        {{ ($row->type != 'payment' && $row->type != 'applied_credit') ? format_currency($row->amount) : '' }}
                    </td>
                    <td class="text-right">{{ ($row->type == 'payment') ? format_currency($row->amount) : '' }}</td>

                    <?php 
                        if($row->type == 'invoice')
                        {
                            $balance += $row->amount;
                        }
                        elseif($row->type == 'credit_note')
                        {
                            $balance += -$row->amount;
                        }
                        elseif($row->type == 'payment')
                        {
                            // Payment
                            $balance += -$row->amount;
                        }

                    ?>
                    @if($row->type != 'applied_credit')
                    <td class="text-right">{{ format_currency($balance , TRUE, $data['currency_symbol'] ) }}</td>
                    @else
                        <td></td>
                    @endif                   
                </tr>
            @endforeach
        @endif
    </tbody>
    <tfoot>
        <tr>            
            <td colspan="3" class="text-right">@lang('form.balance_due')</td>
            <td colspan="2" class="text-right">{{ format_currency($balance , TRUE, $data['currency_symbol'] ) }}</td>               
        </tr>
    </tfoot>

</table>
</div>
</div>    
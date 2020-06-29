<htmlpageheader name="firstpageheader" style="display:none">
   <div class="row">
      <div class="col-md-6">
         <div><b>{{ Config::get('constants.company_name') }}</b></div>
         <address>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
         </address>
         <br>
         <div style="font-size: 12px;">
            <div style="font-weight: bold; ">@lang('form.bill_to'):</div>
            <address style="line-height: 18px;"> 
               {{ $rec->customer->name }}                     
               <br> <?php echo nl2br($rec->address); ?>                
               <br> <?php echo $rec->city; ?>
               <br> <?php echo $rec->state ; ?>
               <br> <?php echo $rec->zip_code ; ?> 
               @if(isset($rec->country->name))
               <br> <?php echo $rec->country->name ; ?>
               @endif
            </address>
         </div>
      </div>
      <div class="col-md-6 text-right">
         <h1 style="margin-bottom: 0; color: #007bff; font-size: 26px;">{{  strtoupper(__('form.invoice')) }}</h1>
         <div>{{ $rec->number }}</div>
         <div>{{ $rec->status->name }}</div>
         <br>
         <div style="font-size: 12px;">
            <div>@lang('form.invoice_date') : {{ sql2date($rec->date) }}</div>
            @if($rec->due_date)
            <div>@lang('form.due_date') : {{ sql2date( $rec->due_date) }}</div>
            @endif
            <div>@lang('form.created_by') : {{ $rec->person_created->first_name . " ".  $rec->person_created->last_name }}</div>
         </div>
      </div>
   </div>
</htmlpageheader>
<div class="row">
   <div class="col-md-12">
      <table class="table table-sm" style="border-bottom: 1px solid #eee; font-size: 12px;">
         <thead >
            <tr >
               <th class="text-left">#</th>
               <th class="description text-left" width="50%">@lang('form.item')</th>
               <th class="text-right">{{ $rec->show_quantity_as }}</th>
               <th class="text-right">@lang('form.rate')</th>
               <th class="text-right">@lang('form.tax')</th>
               <th class="text-right">@lang('form.amount')</th>
            </tr>
         </thead>
         <tbody class="ui-sortable">
            @if(isset($rec->item_line) && count($rec->item_line) > 0)
            @foreach($rec->item_line as $key=>$row)                            
            <tr>
               <td>{{ $key + 1 }}</td>
               <td class="description text-left">
                  <span><strong>{{ $row->description }}</strong></span>
                  <br>
                  <span style="color:#424242;">{{ $row->long_description }}</span>
               </td>
               <td class="text-right">{{ $row->quantity }}</td>
               <td class="text-right">{{ format_currency($row->rate) }}</td>
               <td class="text-right">{{ display_tax_rate_in_item_list($row->tax_id , $rec) }}</td>
               <td class="amount text-right" >{{ format_currency($row->sub_total) }}</td>
            </tr>
            @endforeach
            @endif
         </tbody>
      </table>
   </div>
   <div class="col-md-4 offset-md-8">
      <table class="table text-right">
         <tbody>
            <tr>
               <td><span class="bold">@lang('form.sub_total')</span>
               </td>
               <td class="subtotal">{{ format_currency($rec->sub_total) }}</td>
            </tr>
            @if(isset($rec->discount_total) && ($rec->discount_total > 0))
            <tr>
               <td><span class="bold">@lang('form.discount')</span>
               </td>
               <td class="subtotal">{{ format_currency($rec->discount_total) }}</td>
            </tr>
            @endif
            @if(isset($rec->array_of_taxes_used) && count($rec->array_of_taxes_used) > 0)
            @foreach($rec->array_of_taxes_used as $t)
            <tr class="tax-area">
               <td class="bold">{{ str_replace($t->rate.'%',"", $t->name) }} ({{ number_format($t->rate, 2) }}%)</td>
               <td>{{ format_currency($t->amount) }}</td>
            </tr>
            @endforeach
            @endif
            @if(isset($rec->adjustment) && $rec->adjustment != 0)
               <tr>
                  <td class="bold">@lang('form.adjustment')</td>
                  <td>{{ format_currency($rec->adjustment, true, $rec->get_currency_symbol()) }}</td>
               </tr>
            @endif
            <tr  style="background-color: #eee; border: none;">
               <td><span class="bold">@lang('form.total')</span></td>
               <td class="total">{{ format_currency($rec->total, true, $rec->get_currency_symbol()) }}</td>
            </tr>
            <tr>
               <td><span class="bold">@lang('form.total_paid')</span></td>
               <td><span class="">{{ format_currency($rec->amount_paid, true, $rec->get_currency_symbol()) }}</span></td>
            </tr>
            @if(isset($rec->applied_credits) && $rec->applied_credits > 0)
            <tr>
               <td class="bold">@lang('form.applied_credits')</td>
               <td>{{ format_currency($rec->applied_credits, true, $rec->get_currency_symbol()) }}</td>
            </tr>
            @endif
            <?php $amount_due = $rec->total - ($rec->amount_paid  + $rec->applied_credits) ; ?>
            <tr style="background-color: #eee; border: none;">
               <td><span class="{{ ($amount_due > 0) ? 'text-danger' : '' }} bold">@lang('form.amount_due')</span></td>
               <td><span class="{{ ($amount_due > 0) ? 'text-danger' : '' }}">{{ format_currency($amount_due, true , $rec->get_currency_symbol()) }}</span></td>
            </tr>
         </tbody>
      </table>
   </div>
   <div class="col-md-12 mtop15">
      @if(count($rec->payments) > 0)
      <p class="bold text-muted">@lang('form.transactions')</p>
      <table class="table" width="100%" id="data">
         <thead>
            <tr>
               <th>@lang("form.payment_#")</th>
               <th style="text-align: center;">@lang("form.payment_mode")</th>
               <th>@lang("form.transaction_id")</th>
               <th>@lang("form.date")</th>
               <th class="text-right">@lang("form.amount")</th>
            </tr>
         </thead>
         <tbody>
            @foreach($rec->payments as $payment)
            <tr>
               <td>{{ $payment->number }}</td>
               <td style="text-align: center;">{{ $payment->payment_mode->name }}</td>
               <td>{{ $payment->transaction_id }}</td>
               <td>{{ sql2date($payment->date) }}</td>
               <td class="text-right">{{ format_currency($payment->amount, TRUE, $rec->get_currency_symbol() ) }}</td>
            </tr>
            @endforeach
         </tbody>
      </table>
      <br>
      @endif
      <br><br>
      <div class="col-md-12" style="font-size: 12px;">
         @if($rec->terms_and_condition)
         <div class="bold text-muted">@lang('form.terms_and_condition')</div>
         <p>{{ $rec->terms_and_condition }}</p>
         @endif
         <br><br>
         _________________________<br>
         @lang('form.authorized_signature')
      </div>
   </div>
</div>
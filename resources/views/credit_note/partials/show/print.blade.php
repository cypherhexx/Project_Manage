<htmlpageheader name="firstpageheader" style="display:none">
   <div class="row">
      <div class="col-md-6">
         <div style="margin-top: 10px;"><b>{{ Config::get('constants.company_name') }}</b></div>
         <address>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
         </address>
      </div>
      <div class="col-md-6 text-right">
         <h1 style="margin-bottom: 0; color: #007bff; font-size: 26px;">{{  strtoupper(__('form.credit_note')) }}</h1>
         <div>{{ $rec->number }}</div>
         <div>{{ $rec->status->name }}</div>
      </div>
   </div>
   <div class="row" style="margin-top: 20px; font-size: 12px;">
   <div class="col-md-6">
      <div style="font-weight: bold; ">@lang('form.to'):</div>
      <address style="font-size: 12px;">
         <b>{{ $rec->customer->name }}</b>
         <br> <?php echo nl2br($rec->address); ?>                
         <br> <?php echo $rec->city; ?>
         <br> <?php echo $rec->state ; ?>
         <br> <?php echo $rec->zip_code ; ?> 
         @if(isset($rec->country->name))
         <br> <?php echo $rec->country->name ; ?>
         @endif
         @if(isset($rec->customer->vat_number) && $rec->customer->vat_number)
         <br> @lang('form.vat_number'): {{ $rec->customer->vat_number }}
         @endif
      </address>
   </div>
   <div class="col-sm-6 text-right">

      <div style="font-size: 12px;">
         <div>@lang('form.date') : {{ sql2date($rec->date) }}</div>
        
      </div>
   </div>
</htmlpageheader>
<div class="row">
   <div class="col-md-12">
      <div class="table-responsive">
         <table class="table table-sm" style="border-bottom: 1px solid #eee;">
            <thead>
               <tr>
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
                     <span style="font-size:px;"><strong>{{ $row->description }}</strong></span>
                     <br>
                     <span style="color:#424242;">{{ $row->long_description }}</span>
                  </td>
                  <td class="text-right">{{ $row->quantity }}</td>
                  <td class="text-right">{{ $row->rate }}</td>
                  <td class="text-right">{{ display_tax_rate_in_item_list($row->tax_id , $rec) }}</td>
                  <td class="amount text-right" >{{ $row->sub_total }}</td>
               </tr>
               @endforeach
               @endif
            </tbody>
         </table>
      </div>
   </div>
   <div class="col-md-4 offset-md-8">
      <table class="table text-right" cellspacing="0">
         <tbody>
            <tr>
               <td><span class="bold">@lang('form.sub_total')</span>
               </td>
               <td class="subtotal">{{ $rec->sub_total }}</td>
            </tr>
            @if(isset($rec->discount_total) && ($rec->discount_total > 0))
            <tr>
               <td><span class="bold">@lang('form.discount')</span>
               </td>
               <td class="subtotal">{{ $rec->discount_total }}</td>
            </tr>
            @endif
            @if(isset($rec->array_of_taxes_used) && count($rec->array_of_taxes_used) > 0)
            @foreach($rec->array_of_taxes_used as $t)
            <tr class="tax-area">
               <td class="bold">{{ str_replace($t->rate.'%',"", $t->name) }} ({{ number_format($t->rate, 2) }}%)</td>
               <td>{{ $t->amount }}</td>
            </tr>
            @endforeach
            @endif
            <tr style="background-color: #eee; border: none;">
               <td><span class="bold">@lang('form.total')</span></td>
               <td class="total">{{ format_currency($rec->total, true, $rec->get_currency_symbol() ) }}</td>
            </tr>
            <tr>
               <td><span class="bold">@lang('form.amount_credited')</span></td>
               <td class="total">{{ format_currency($rec->amount_credited, true, $rec->get_currency_symbol() ) }}</td>
            </tr>
            <tr  style="background-color: #eee; border: none;">
               <td><span class="bold">@lang('form.remaining_credit')</span></td>
               <td class="total">{{ format_currency($rec->total - $rec->amount_credited, true, $rec->get_currency_symbol() ) }}</td>
            </tr>
         </tbody>
      </table>
   </div>
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
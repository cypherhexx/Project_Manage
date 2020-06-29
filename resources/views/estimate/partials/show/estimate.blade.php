<div style="font-size: 13px;">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <h4 class="bold">
            <a href="{{ route('edit_estimate_page', $rec->id) }}">
            <span id="estimate-number">{{ $rec->number }}</span>
            </a>
         </h4>
         <address>
            <div><b>{{ Config::get('constants.company_name') }}</b></div>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
         </address>
         <p class="no-mbot">
            <span class="bold">
            @lang('form.estimate_date'):  {{ $rec->date }}
            </span>
         </p>
         <p class="no-mbot">
            <span class="bold">@lang('form.expiry_date'):</span>
            {{ $rec->expiry_date }}
         </p>
         <p class="no-mbot">
            <span class="bold">@lang('form.sales_agent'):</span>
            {{ (isset($rec->sales_agent->name)) ? $rec->sales_agent->name : ' ' }}
         </p>
      </div>
      <div class="col-sm-6 text-right">
         <span class="bold">@lang('form.to'):</span>
         <address>
            <a href="{{ route('view_customer_page', $rec->customer_id) }}" target="_blank">
            <b>{{ $rec->customer->name }}</b>
            </a>
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
         <span class="bold">@lang('form.ship_to'):</span>
         <address>
            @if(isset($rec->shipping_address) && $rec->shipping_address)
            <?php echo nl2br($rec->shipping_address); ?>
            @endif
            @if(isset($rec->shipping_city) && $rec->shipping_city)
            <br> <?php echo $rec->shipping_city; ?>
            @endif
            @if(isset($rec->shipping_state) && $rec->shipping_state)
            <br> <?php echo $rec->shipping_state ; ?>
            @endif
            @if(isset($rec->shipping_zip_code) && $rec->shipping_zip_code)
            <br> <?php echo $rec->shipping_zip_code ; ?>
            @endif
            @if(isset($rec->shipping_country->name))
            <br> <?php echo $rec->shipping_country->name ; ?>
            @endif
         </address>
      </div>
   </div>
   <br><br>
   <div class="row">
      <div class="col-md-12">
         <div class="table-responsive">
            <table class="table items estimate-items-preview">
               <thead>
                  <tr>
                     <th>#</th>
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
                     <td class="text-right">{{ format_currency($row->rate) }}</td>
                     <td class="text-right">
                        {{ display_tax_rate_in_item_list($row->tax_id , $rec) }}
                     </td>
                     <td class="amount text-right" >{{ format_currency($row->sub_total) }}</td>
                  </tr>
                  @endforeach
                  @endif
               </tbody>
            </table>
         </div>
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
               <tr>
                  <td><span class="bold">@lang('form.total')</span></td>
                  <td class="total">{{ format_currency($rec->total, true, $rec->get_currency_symbol()) }}</td>
               </tr>
            </tbody>
         </table>
      </div>
      @if($rec->terms_and_condition)
      <div class="col-md-12">
         <hr>
         <div class="bold text-muted">@lang('form.terms_and_condition')</div>
         <p>{{ $rec->terms_and_condition }}</p>
      </div>
      @endif
      @if($rec->admin_note &&  Route::currentRouteName() != 'estimate_customer_view')
      <div class="col-md-12">
         <div class="bold text-muted">@lang('form.admin_note')</div>
         <p>{{ $rec->admin_note }}</p>
      </div>
      @endif
      @if($rec->client_note)
      <div class="col-md-12">
         <div class="bold text-muted">@lang('form.client_note')</div>
         <p>{{ $rec->client_note }}</p>
      </div>
      @endif
   </div>
</div>
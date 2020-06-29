@if(in_array($rec->status_id, [INVOICE_STATUS_UNPAID, INVOICE_STATUS_PARTIALLY_PAID, INVOICE_STATUS_OVER_DUE]))
@if(!empty($data['online_payment_modes']) && is_array($data['online_payment_modes']))
<hr>
<div id="pay_now" class="row" style="margin-bottom: 100px;">
   <div class="col-md-6">
      <h6>@lang('form.online_payment')</h6>
      <form method="GET" action="{{ route( 'process_payment_request') }}" autocomplete="off">
         <input type="hidden" name="invoice_id" value="{{ encrypt($rec->id) }}">
         @foreach($data['online_payment_modes'] as $key=>$row)
         <?php
            if(old('gateway') == $row['unique_identifier'])
            {
            	$checke_status = 'checked';
            }
            elseif(empty(old('gateway')) && $key == 0)
            {
            	$checke_status = 'checked';
            }
            else
            {
            	$checke_status = NULL;
            }
            ?>
         <div class="custom-control custom-radio">
            <input {{ $checke_status }} type="radio" id="{{ $row['unique_identifier'] }}" name="gateway" class="custom-control-input" value="{{ $row['unique_identifier'] }}" >
            <label class="custom-control-label" for="{{ $row['unique_identifier'] }}">{{ $row['display_name_set_by_user'] }}</label>
         </div>
         @endforeach  
         <br>
         @if($rec->allow_partial_payment)
         <div class="input-group mb-3">
            <div class="input-group-prepend">
               <span class="input-group-text">{{ $data['currency_symbol'] }}</span>
            </div>
            <input type="text" class="form-control" name="amount" id="amount" value="{{ old('amount', $rec->amount_due) }}">
         </div>
         @else
         <input type="hidden" class="form-control" name="amount" id="amount" value="{{ old('amount', $rec->amount_due) }}">
         @endif
         <button type="submit" class="btn btn-primary">@lang('form.pay_now')</button>	
      </form>
   </div>
</div>
@endif
@endif
<div>

    <div class="modal fade" id="applyCreditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-lg" role="document">
            <form method="post" action="{{ route('apply_credit_to_invoice') }}" >
                 {{ csrf_field()  }}
                <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">@{{ records.invoice_number }} - @lang('form.apply_credits')</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click.prevent="toggleModal">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body"> 
                    <table class="table table-credit_notes dataTable dtr-inline collapsed" width="100%">
                       <thead>
                          <tr>
                             <th>@lang("form.credit_note_#")</th>
                             <th>@lang("form.date")</th>
                             <th class="text-right">@lang("form.amount")</th>
                             <th class="text-right">@lang("form.remaining_amount")</th>   
                             <th class="text-right">@lang("form.amount_to_credit")</th>                            
                          
                          </tr>
                       </thead>

                       <tbody>
                           <tr v-for="(row, index) in credit_notes">
                               <td>@{{ row.number }}</td>
                               <td>@{{ row.date }}</td>
                               <td class="text-right">@{{ row.total | format_money  }}</td>
                               <td class="text-right">@{{ (row.total - row.amount_credited) | format_money }}</td>
                               <td class="text-right">
                                <input style="text-align: right;" type="text" :name="'items[' + row.id + '][amount_to_credit]'" v-model="row.amount_to_credit" class="form-control form-control-sm" v-bind:class="{ 'is-invalid': errors[index] }" v-on:keypress="isNumber($event)">
                                <div v-if="errors[index]" class="invalid-feedback">@{{ errors[index] }}</div>
                               </td>
                           </tr>
                       </tbody>
                        <tfoot>
                           <tr >
                               <td colspan="4" class="text-right">@lang('form.amount_to_credit')</td>
                               <td class="text-right">@{{ amount_to_credit | format_money }}</td>  
                               <input type="hidden" name="total_amount_to_credit" v-model="amount_to_credit">                            
                               <input type="hidden" name="invoice_id" v-model="invoice_id"> 
                           </tr>
                           <tr >
                               <td colspan="4" class="text-right">@lang('form.balance_due')</td>
                               <td class="text-right">@{{ tmp_balance_due | format_money }}</td>                              
                           </tr>
                           <tr v-if="!show_apply_credit_button">
                            <td colspan="2">
                                <div class="invalid-feedback d-block" style="text-align: right">@lang('form.credit_amount_cannot_be_more_than_balance_due')</div>
                            </td>
                           </tr>
                       </tfoot>
                       @{{ calculate_total_amount_to_credit() }}
                    </table>
               </div>
               <div class="modal-footer">
                  <button v-on:click.prevent="toggleModal" type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
                  <button type="submit" class="btn btn-primary" v-if="show_apply_credit_button">@lang('form.apply')</button>
               </div>
            </div>

             </form>   
         </div>
      </div>

<div class="alert alert-primary" role="alert" v-if="records.customer_available_credits > 0">
<div>@lang('form.available_credit') @{{ records.customer_available_credits | format_money }} <a href="#" v-on:click.stop.prevent="applyCreditButton()">Apply Credit</a></div>
</div>

<form id="receivePaymentForm" method="post" action="{{ route( 'receive_payment') }}">
    {{ csrf_field()  }}

    <div class="row">
        <div class="col-md-12">
            <h5>@lang('form.record_payment') 
                <span class="text-danger" style="font-size: 13px;">@lang('form.amount_due') : @{{ this.records.amount_due_formatted }}</span>
            </h5>

            <hr>
            <input type="hidden" name="invoice_id" v-model="invoice_id">
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>@lang('form.amount_received') <span class="required">*</span> </label>
                <input type="text" v-on:keypress="isNumber($event)" class="form-control form-control-sm" name="amount"  v-model="amount">
                <div class="amount required" style="display: none;">@lang('form.this_field_is_required')</div>
                <div id="error_msg_exceding_amount" class="invalid-feedback" style="display: none;">@lang('form.over_received_amount')</div>
            </div>

            <div class="form-group">
                <label>@lang('form.payment_date') <span class="required">*</span> </label>
                <datepicker v-model="date" name="date"></datepicker>

                <div class="date required" style="display: none;">@lang('form.this_field_is_required')</div>
            </div>

            <div class="form-group">

                <label>@lang('form.payment_mode') <span class="required">*</span> </label>

                <select2 name="payment_mode_id" :options="options" v-model="payment_mode_id"></select2>
                <div class="payment_mode_id required" style="display: none;">@lang('form.this_field_is_required')</div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>@lang('form.transaction_id')</label>
                <input type="text" class="form-control form-control-sm  @php if($errors->has('transaction_id')) { echo 'is-invalid'; } @endphp" name="transaction_id" value="{{ old_set('transaction_id', NULL,$rec) }}">

            </div>

            <div class="form-group">
                <label>@lang('form.note')</label>
                <textarea id="note" name="note" rows="4" class="form-control">{{ old_set('note', NULL, $rec) }}</textarea>

            </div>
        </div>


        <div class="col-md-12">
            <div class="float-md-right">
                <button type="button" class="btn btn-danger" v-on:click="cancelButton">@lang('form.cancel')</button>
                <button type="button" class="btn btn-success" v-on:click="submitButton()">@lang('form.submit')</button>
            </div>
        </div>
    </div>
</form>
 </div> 
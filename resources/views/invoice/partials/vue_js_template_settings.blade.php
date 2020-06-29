<template id="recurring_invoice_template" >
   <div>
      <div class="form-group">
         <label>@lang('form.recurring_invoice') </label>
         <select2 :options="recurring_invoice_types_list" v-model="formInput.recurring_invoice_type"></select2>
      </div>
      <div class="form-row" v-if="formInput.recurring_invoice_type=='custom' ">
         <div class="form-group col-md-6">
             <label>@lang('form.frequency') </label>
            <select2 :options="recurring_invoice_custom_type_list" v-model="formInput.recurring_invoice_custom_type"></select2>
         </div>
         <div class="form-group col-md-6">
            <label>@lang('form.value') </label>
            <input type="text" v-on:keypress="isNumber($event)" class="form-control form-control-sm" v-model="formInput.recurring_invoice_custom_parameter">
         </div>
         
      </div>
      <div class="form-group">
         <label>@lang('form.total_cycle') </label>	
         <div class="input-group input-group-sm mb-3">
            <input type="text" v-on:keypress="isNumber($event)" class="form-control form-control-sm" v-model="formInput.recurring_invoice_total_cycle" :disabled="formInput.is_recurring_invoice_period_infinity">
            <div class="input-group-append" style="height: calc(1.8125rem + 2px);">
               <span class="input-group-text" id="basic-addon2">
                  <div class="custom-control custom-checkbox">
                     <input type="checkbox" class="custom-control-input" id="is_recurring_invoice_period_infinity" v-model="formInput.is_recurring_invoice_period_infinity" value="1">
                     <label class="custom-control-label" for="is_recurring_invoice_period_infinity">@lang('form.infinity')</label>
                  </div>
               </span>
            </div>
         </div>
      </div>
      <button type="button" class="btn btn-success" v-on:click="submitButton()">@lang('form.submit')</button>
   </div>
</template>
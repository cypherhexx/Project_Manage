<div>
<table class="table">
   <thead>
      <tr>  
         <th class="">@lang('form.invoice_#')</th>
         <th class="text-right">@lang('form.date')</th>
         <th class="text-right">@lang('form.amount')</th>
      </tr>
   </thead>
   <tbody>
                              
      <tr v-for="row in rec">       
         <td><a v-bind:href="row.invoice_url">@{{ row.invoice_number }}</a></td>
         <td class="text-right">@{{ row.date }}</td>
         <td class="amount text-right">@{{ row.amount }}</td>
      </tr>     
   </tbody>
</table>
</div>
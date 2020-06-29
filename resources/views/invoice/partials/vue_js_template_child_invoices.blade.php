<template id="child_invoices_template" >
<div>
	<table class="table dataTable no-footer collapsed" width="100%" id="child_invoices">
	    <thead>
	    <tr>
	        <th>@lang("form.invoice_#")</th>
	        <th>@lang("form.amount")</th>
	        <th>@lang("form.total_tax")</th>
	        <th>@lang("form.date")</th>
	                     
	        <th>@lang("form.due_date")</th>                      
	        <th>@lang("form.status")</th>
	    </tr>
	    </thead>
	    <tbody>
	    	<tr v-for="invoice in invoices">
	    		<td v-html="invoice.number"></td>
	    		<td>@{{ invoice.total }}</td>
	    		<td>@{{ invoice.tax_total }}</td>
	    		<td>@{{ invoice.date }}</td>                    		
	    		<td>@{{ invoice.due_date }}</td>
	    		<td>@{{ invoice.status }}</td>
	    	</tr>
	    </tbody>
	</table>
</div>
</template>
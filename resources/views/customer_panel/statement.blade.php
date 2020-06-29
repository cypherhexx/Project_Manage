@extends('layouts.customer.main')
@section('title', __('form.statement'))
@section('content')

<style type="text/css">
	.statement-container {
    background: #fff;
    border: 1px solid #e4e5e7;
    border-radius: 4px;
    padding: 20px;

}
</style>

<div class="main-content">

<h6>@lang('form.statement')</h6>

<form>
	<div class="form-row">
		<div class="form-group col-md-3">
            <label for="name">@lang('form.date_range')</label>
            <input type="text" class="form-control form-control-sm" id="reportrange" name="date">                             
        </div>

        <div class="form-group col-md-9">
        	<a target="_blank" href="{{ $data['url_for_pdf_download'] }}" class="btn btn-sm btn-light float-md-right"><i class="far fa-file-pdf"></i> @lang('form.download_pdf')</a>
        </div>	
           
	</div>
</form>
<hr>

@include('customer_panel.partials.statement_main')


@endsection

@section('onPageJs')

<script>

        $(function () {     	

           $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
			  
			 var url = "{{ route('cp_customer_statement_page') }}" + '?from='+ picker.startDate.format('YYYY-MM-DD') + '&to=' + picker.endDate.format('YYYY-MM-DD');	
			  window.location = url;

			});

         	$('#reportrange').data('daterangepicker').setStartDate('<?php echo $data['date_from']; ?>');
			$('#reportrange').data('daterangepicker').setEndDate('<?php echo $data['date_to']; ?>');

        });

       </script> 
@endsection
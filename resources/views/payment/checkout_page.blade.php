@extends('layouts.customer.public_view')
@section('title', $invoice->number)
@section('content')
  @if(Session::has('message')) 
  
    <div class="alert {{ Session::get('alert-class', 'alert-info') }}">
        {{ Session::get('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div>
 
  @endif
  
@lang('form.payment_for') @lang('form.invoice') 
	<a href="{{ route('invoice_customer_view', [$invoice->id, $invoice->url_slug]) }}">{{ $invoice->number }}</a>
<hr>	
<div style="margin-bottom: 20px;">@yield('payment_gateway_checkout_page')</div>
@endsection

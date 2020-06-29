@extends('payment.checkout_page')
@section('payment_gateway_checkout_page')

<?php $stripe = get_payment_gateway_info('stripe') ; ?>

@if(isset($stripe->stripe_api_publishable_key) && $stripe->stripe_api_publishable_key) 

@if($stripe)
<h6>@lang('form.total_amount') : {{ format_currency($data['amount'], TRUE, $data['currency_symbol'] ) }}</h6>

<form id="payment_form" method="POST" action="{{ route('process_stripe_payment') }}">
   {{ csrf_field()  }}
   <button class="btn btn-primary btn-sm" type="button" id="stripe-button">
   <i class="fab fa-cc-stripe"></i> @lang('form.pay_with_card')</button>
   <input type="hidden" name="stripeToken"> 
   <input type="hidden" name="stripeEmail">
   <input type="hidden" value="{{ $data['amount'] }}" name="stripeAmount" id="stripeAmount"> 
   <input type="hidden" name="invoice_id" value="{{ encrypt($invoice->id) }}">
</form>
@endif

@endif

@endsection



@section('onPageJs')
 <?php if(isset($stripe->stripe_api_publishable_key) && $stripe->stripe_api_publishable_key) {?>
 <script src="https://checkout.stripe.com/checkout.js"></script>
      <script>
        $(function() {  
            
           
            $('#stripe-button').click(function(){     

                  var amount = {{ $data['amount'] }};

                  amount = Math.round((amount * 100));

                  
                  StripeCheckout.open({
                      key           : '{{ $stripe->stripe_api_publishable_key }}',
                      amount        : amount,
                      name          : "{{ config('constants.company_name') }}",
                      image         : "{{ get_company_logo('regular') }}",
                      description   : "{{ str_replace('{invoice_number}', $invoice->number , $stripe->stripe_description_dashboard) }}",
                      panelLabel    : 'Checkout',
                      currency      : "{{ $invoice->currency_code }}",
                      token         : function(res) {

                            $('input[name=stripeToken]').val(res.id);
                            $('input[name=stripeEmail]').val(res.email);               
                            $('#payment_form').submit();
                      }
                  });

              return false;
            });

           
        });
      </script>
 <?php } ?>
@endsection
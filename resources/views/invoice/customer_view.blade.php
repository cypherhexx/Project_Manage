@extends('layouts.customer.public_view')
@section('title', $rec->number)
@section('content')

<div class="row">
  @if(Session::has('message')) 
  <div class="col-md-12">
    <div class="alert {{ Session::get('alert-class', 'alert-info') }}">
        {{ Session::get('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div>
  </div>   
  @endif

  <div class="col-md-6">
     <div class="{{ get_invoice_status_badge($rec->status_id) }}">{{  $rec->status->name }}</div>
  </div>
  <div class="col-md-6">
     <div class="tpbutton btn-toolbar text-center float-md-right">
      @if(is_array($data['online_payment_modes']) && count($data['online_payment_modes']) > 0 &&
      (in_array($rec->status_id, [INVOICE_STATUS_UNPAID, INVOICE_STATUS_PARTIALLY_PAID, INVOICE_STATUS_OVER_DUE]))
      )
      <button type="button" class="btn navbar-btn btn-primary" id="go_to_pay_now">@lang('form.pay_now')</button>
      @endif 
      <a class="btn btn-light" href="{{ route('download_invoice', $rec->id) }}">@lang('form.download')</a>
    </div>
     
  </div>
  <div class="col-md-12">
     <hr>
     <?php echo $data['html'];?>
     @include('invoice.customer_view_payment_options')
  </div>
  
</div>         

            
      
@endsection

@section('onPageJs')

<script>
        $(function() {
            
            $("#amount").keydown(function (e) {
              // Allow: backspace, delete, tab, escape, enter and .
              if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                   // Allow: Ctrl/cmd+A
                  (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                   // Allow: Ctrl/cmd+C
                  (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                   // Allow: Ctrl/cmd+X
                  (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                   // Allow: home, end, left, right
                  (e.keyCode >= 35 && e.keyCode <= 39)) {
                       // let it happen, don't do anything
                       return;
              }
              // Ensure that it is a number and stop the keypress
              if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                  e.preventDefault();
              }
          });


            $("#go_to_pay_now").click(function() {
                $('html,body').animate({
                    scrollTop: $("#pay_now").offset().top},
                    'slow');
            });
            
        });
      </script>
@endsection


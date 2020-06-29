@extends('layouts.app')
@section('content')
<div class="container">
   <div class="row justify-content-center">
      <div class="col-md-8">
         <h2 class="t-heading -size-l">@lang('form.customer_registration')</h2>
         <a href="{{ route('verification_resend') }}">Resend Email Validation Link</a>
         <div class="card" style="margin-bottom: 10%;">
            <div class="card-body">

               <form method="post" action="{{ route('register') }}">
               {{ csrf_field()  }}
               @include('customer.registration_form')
               
               <hr>
               <?php google_recaptcha($errors); ?>
               
               <div class="form-group" style="text-align: center;" >
                  <input type="submit" class="btn btn-lg btn-primary" name="submit" value="@lang('form.register')">
               </div>   
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

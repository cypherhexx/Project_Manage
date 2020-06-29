@extends('layouts.app')
@section('content')
<div class="container">
   <div class="row">
      <div class="card mx-auto" style="width: 28rem; margin-bottom: 10%; font-size: 13px;">
         <div class="card-body">
            <h4 class="card-title">Client Reset Password</h4>
            <hr>
            <form class="form-horizontal" method="POST" action="{{ route('customer_password_request') }}">
               {{ csrf_field() }}
               <input type="hidden" name="token" value="{{ $token }}">
               <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                  <label for="email">E-Mail Address</label>
                  <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>
                  <div class="invalid-feedback d-block">@php if($errors->has('email')) { echo $errors->first('email') ; } @endphp</div>
               </div>
               <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                  <label for="password">Password</label>
                  <input id="password" type="password" class="form-control" name="password" required>
                  <div class="invalid-feedback d-block">@php if($errors->has('password')) { echo $errors->first('password') ; } @endphp</div>
               </div>
               <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                  <label for="password-confirm">Confirm Password</label>
                  <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                  <div class="invalid-feedback d-block">@php if($errors->has('password_confirmation')) { echo $errors->first('password_confirmation') ; } @endphp</div>
               </div>
               <div class="form-group">
                  <button type="submit" class="btn btn-primary">
                  Reset Password
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
@endsection
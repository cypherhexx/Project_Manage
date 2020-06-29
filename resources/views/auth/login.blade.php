@extends('layouts.app')
@section('content')
<div class="container" style="padding-top: 5%;">
   <div class="row">
      <div class="col-md-4 offset-md-2" style="background-color: #408DE3; color: #fff">
         <div style="padding-top: 10%; padding-left: 10px;">
            <h3>{{ config('constants.company_name') }}</h3>
            <!-- <div>CRM & Project Management System</div> -->
         </div>
      </div>
      <div class="col-md-4" style="background-color: #fff;">
         <div style="padding: 20px;">
            <form method="POST" action="{{ route('login') }}">
               {{ csrf_field() }}
               <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                  <label for="email">E-Mail Address</label>
                  <input id="email" type="email" class="form-control form-control-sm" name="email" value="{{ old('email') }}" required autofocus>
                  @if ($errors->has('email'))
                  <span class="help-block">
                  <strong>{{ $errors->first('email') }}</strong>
                  </span>
                  @endif
               </div>
               <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                  <label for="password">Password</label>
                  <input id="password" type="password" class="form-control form-control-sm" name="password" required>
                  @if ($errors->has('password'))
                  <span class="help-block">
                  <strong>{{ $errors->first('password') }}</strong>
                  </span>
                  @endif
               </div>
               <div class="form-group">
                  <div class="checkbox">
                     <label>
                     <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                     </label>
                  </div>
               </div>

               <?php google_recaptcha($errors); ?>

               <div class="form-group">
                  <button disabled id="sign_in" style="cursor: pointer;" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
                  <a class="btn btn-link" href="{{ route('password.request') }}">
                  Forgot Your Password?
                  </a>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>


@endsection
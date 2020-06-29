@extends('layouts.app')
@section('content')
<div class="container">
   <div class="row">
      <div class="card mx-auto" style="width: 28rem; margin-bottom: 10%; font-size: 13px;">
         <div class="card-body">
            <h4 class="card-title">Client Reset Password</h4>
            <hr>
            @if (session('status'))
            <div class="alert alert-success">
               {{ session('status') }}
            </div>
            @endif
            <form class="form-horizontal" method="POST" action="{{ route('customer_password_email') }}">
               {{ csrf_field() }}
               <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                  <label for="email">E-Mail Address</label>
                  <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                  <div class="invalid-feedback d-block">@php if($errors->has('email')) { echo $errors->first('email') ; } @endphp</div>
               </div>
               <div class="form-group">
                  <button type="submit" class="btn btn-primary">
                  Send Password Reset Link
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
@endsection
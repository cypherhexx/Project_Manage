@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="t-heading -size-l">{{ __('Email Address Verified') }}</h2>
            <div class="card">                     
                <div class="card-body">                  

                    {{ __('Your email address has been verified.') }}
                    {{ __('Please') }} <a href="{{ route('customer_login_page') }}">{{ __('click here to login') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

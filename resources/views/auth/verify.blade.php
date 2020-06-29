@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="t-heading -size-l">{{ __('Verify Your Email Address') }}</h2>
            <div class="card">                     
                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email for a verification link.') }} Please note that the provided link is valid for <b>24 hours</b>.
                    {{ __('If you did not receive the email') }}, <a href="{{ route('verification_resend') }}">{{ __('click here to request another') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

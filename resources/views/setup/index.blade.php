@extends('layouts.main')

@section('title', __('form.settings'))

@section('content')

    @include('setup.menu')

    @yield('setting_page')

@endsection

@section('onPageJs')

    @yield('innerPageJS')


@endsection
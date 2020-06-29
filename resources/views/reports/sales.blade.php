@extends('layouts.main')

@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
        $main_url = route('report_sales_page');
    @endphp

    <div class="main-content" style="margin-bottom: 20px !important;">

        

        <div class="row">
        <div class="col-md-6">
           <h5>{{ __('form.sales') ." " .__('form.report') }}</h5>      
        </div>
        <div class="col-md-6">

            
        </div>
        </div>


        <ul class="nav project-navigation">
            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ $main_url }}">@lang('form.invoices')</a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('items', $group_name) }}" href="{{ $main_url }}?group=items">@lang('form.items')</a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('payments_received', $group_name) }}" href="{{ $main_url }}?group=payments_received">@lang('form.payments_received')</a>
            </li>


            <li class="nav-item">
                <a class="nav-link {{ is_active_nav('customers', $group_name) }}" href="{{ $main_url }}?group=customers">@lang('form.customers')</a>
            </li>

            
        </ul>


    </div>
    

    <div class="main-content">

        @if($group_name == '')
            @include('reports.partials.invoices')
        @elseif($group_name == 'items')
            @include('reports.partials.items')
        @elseif($group_name == 'payments_received')
            @include('reports.partials.payments_received')
        @elseif($group_name == 'customers')
            @include('reports.partials.customers')
        @endif

    </div>



@endsection

@section('onPageJs')

    @yield('innerPageJS')
    @yield('innerChildPageJs')
    <script>

        $(function() {



        });



    </script>


@endsection
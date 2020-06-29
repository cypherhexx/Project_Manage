@extends('layouts.main')

<?php
$page_title = (isset($rec->id)) ? __('form.vendor'). " : " .$rec->name : __('form.add_new_vendor');
?>
@section('title', $page_title)

@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = ($route_name == 'edit_vendor_page' || $route_name == 'add_vendor_page') ? 'show_form' : app('request')->input('group');

        if(isset($rec->id))
        {
            $main_url   = route('view_vendor_page', $rec->id);

        }

    @endphp


    <div class="main-content" style="margin-bottom: 10px;">
        <h5>@lang('form.vendor')  {{ (isset($rec->id)) ? ' : ' . $rec->name . " ( " . $rec->number . " )" : '' }} </h5>
    </div>
   



    <div class="row" style="margin-bottom: 20%;">
        @if(isset($rec->id) && ($route_name != 'edit_vendor_page'))
            <div class="col-md-3">

                <div class="list-group">
                    <a href="{{ $main_url }}" class="list-group-item list-group-item-action {{ is_active_nav('', $group_name) }}">
                        <i class="fa fa-user-circle menu-icon" aria-hidden="true"></i> @lang('form.profile')
                    </a>
                    


                    <a href="{{ $main_url }}?group=expenses" class="list-group-item list-group-item-action {{ is_active_nav('expenses', $group_name) }}">
                        <i class="far fa-money-bill-alt menu-icon" aria-hidden="true"></i> @lang('form.expenses')
                    </a>

                </div>




            </div>
        @endif


        <div class="{{ (isset($rec->id) && ($route_name != 'edit_vendor_page')) ? 'col-md-9' : 'col-md-12' }}">

            <div class="">
                @if($group_name == '')
                    @include('vendor.profile')
                @elseif($group_name == 'show_form')
                    @include('vendor.form')              
                @elseif($group_name == 'expenses')
                    @include('vendor.expenses')

                @endif
            </div>



        </div>
    </div>
@endsection

@section('onPageJs')

    @yield('innerPageJS')


@endsection
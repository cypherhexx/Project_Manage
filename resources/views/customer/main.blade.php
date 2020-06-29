@extends('layouts.main')

<?php
$page_title = (isset($rec->id)) ? __('form.customer') . " : " .$rec->name : __('form.add_new_customer');
?>
@section('title', $page_title)
@section('content')
<?php 
   $route_name = Route::currentRouteName();
   $group_name = app('request')->input('group');
   $sub_group_name = app('request')->input('subgroup');
   if(isset($rec->id))
   {
       $main_url   = route('view_customer_page', $rec->id);
   
   }
   ?>
<div class="main-content" style="margin-bottom: 20px;">
   <div class="row" style="margin-bottom: 10px;">
      <div class="col-md-9">
         <h5>@lang('form.customer'): {{ $rec->name }}</h5>
      </div>
      <div class="col-md-3">
         @if(check_perm('customers_edit'))
         <div class="dropdown float-md-right">
            <a class="btn btn-light btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.actions')
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
               <a class="dropdown-item" href="{{ route('edit_customer_page', $rec->id) }}">@lang('form.edit')</a>
            </div>
         </div>
         @endif 
      </div>
   </div>
   <ul class="nav project-navigation">
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ $main_url }}"><i class="fas fa-user"></i> @lang('form.profile')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('contacts', $group_name) }}" href="{{ $main_url }}?group=contacts"><i class="fas fa-users"></i> @lang('form.contacts')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('invoices', $group_name) }}" href="{{ $main_url }}?group=invoices"><i class="fas fa-file-alt"></i> @lang('form.invoices')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('payments', $group_name) }}" href="{{ $main_url }}?group=payments"><i class="fas fa-chart-line"></i> @lang('form.payments')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('proposals', $group_name) }}" href="{{ $main_url }}?group=proposals"><i class="fas fa-file-powerpoint"></i> @lang('form.proposals')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('estimates', $group_name) }}" href="{{ $main_url }}?group=estimates"><i class="fas fa-clipboard"></i> @lang('form.estimates')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('expenses', $group_name) }}" href="{{ $main_url }}?group=expenses"><i class="fas fa-money-bill-alt"></i> @lang('form.expenses')</a>
      </li>
      <li class="nav-item">
         <a class="nav-link {{ is_active_nav('tasks', $group_name) }}" href="{{ $main_url }}?group=tasks"><i class="fas fa-tasks"></i> @lang('form.tasks')</a>
      </li>
   </ul>
</div>
@if($group_name == '')
@include('customer.profile')
@elseif($group_name == 'show_form')
@include('customer.form')
@elseif($group_name == 'contacts')
@include('customer.contacts')
@elseif($group_name == 'invoices')
@include('customer.invoices')
@elseif($group_name == 'payments')
@include('customer.payments')
@elseif($group_name == 'proposals')
@include('customer.proposals')
@elseif($group_name == 'estimates')
@include('customer.estimates')
@elseif($group_name == 'expenses')
@include('customer.expenses')
@elseif($group_name == 'tasks')
@include('customer.tasks')
@endif
@endsection

@section('onPageJs')

    @yield('innerPageJS')


@endsection
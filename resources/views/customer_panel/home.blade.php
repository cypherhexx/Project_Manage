@extends('layouts.customer.main')
@section('title', __('form.dashboard'))
@section('content')
<div class="row" style="font-size: 13px;">
   <div class="col-md-6">
      <div class="main-content">
         <h5>@lang('form.projects')</h5>
         <hr>
         <div class="row">
            <div class="col-md-4 bd-highlight">
               <h5>{{ $data['project_stat']['not_started'] }}</h5>
               <div>@lang('form.not_started')</div>
            </div>
            <div class="col-md-4 bd-highlight">
               <h5>{{ $data['project_stat']['in_progress'] }}</h5>
               <div class="text-success">@lang('form.in_progress')</div>
            </div>
            <div class="col-md-4 bd-highlight">
               <h5>{{ $data['project_stat']['on_hold'] }}</h5>
               <div class="text-danger">@lang('form.on_hold')</div>
            </div>
         </div>
         <hr>
         <div class="row">
            <div class="col-md-4 bd-highlight">
               <h5>{{ $data['project_stat']['cancelled'] }}</h5>
               <div class="text-primary">@lang('form.cancelled')</div>
            </div>
            <div class="col-md-4 bd-highlight">
               <h5>{{ $data['project_stat']['finished'] }}</h5>
               <div class="text-danger">@lang('form.finished')</div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-6">
      <?php 
         $stat 	= $data['stat_unpaid_invoices'];
         $total = $data['stat_total_unpaid_invoices'];
         ?>
      <div class="main-content">
         <h5>@lang('form.invoices')</h5>
         <hr>
         <div class="row" style="margin-bottom: 30px;">
            <div class="col-md-6">
               @lang('form.unpaid') : {{ $stat[INVOICE_STATUS_UNPAID]['number'] }} / {{ $total }}
               <?php gen_progress_bar('bg-danger', $stat[INVOICE_STATUS_UNPAID]['percent']) ;?>
            </div>
            <div class="col-md-6">
               @lang('form.partially_paid') : {{ $stat[INVOICE_STATUS_PARTIALLY_PAID]['number'] }} / {{ $total }}
               <?php gen_progress_bar('bg-warning', $stat[INVOICE_STATUS_PARTIALLY_PAID]['percent'] ) ;?>
            </div>
         </div>
         <hr>
         <div class="row">
            <div class="col-md-6">
               @lang('form.over_due') : {{ $stat[INVOICE_STATUS_OVER_DUE]['number'] }} / {{ $total }}
               <?php gen_progress_bar('bg-info', $stat[INVOICE_STATUS_OVER_DUE]['percent'] ) ;?>
            </div>
            <div class="col-md-6">
               @lang('form.draft') : {{ $stat[INVOICE_STATUS_DRAFT]['number'] }} / {{ $total }}
               <?php gen_progress_bar('bg-secondary', $stat[INVOICE_STATUS_DRAFT]['percent'] ) ;?>
            </div>
         </div>
         <a href="{{ route('cp_customer_statement_page') }}">@lang('form.view_account_statement')</a>
      </div>
   </div>
</div>
@endsection
@extends('layouts.main')
@section('title', __('form.expenses') . " : ". __('form.report'))
@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
        $main_url = route('report_sales_page');
    @endphp

    <style type="text/css">
        tr:nth-last-child(-n+3) {
            /*declarations*/
            font-weight: bold;
        }
        tr:nth-last-child(-n+3) > td:first-child {
            /*declarations*/
           color: #03a9f4;
        }
    </style>

    <div class="main-content" style="margin-bottom: 20px !important;">
   <div class="row">
      <div class="col-md-6">
         <h5>{{ __('form.expense') ." " .__('form.report') }}</h5>
      </div>
      <div class="col-md-6">
         <form target="_blank" action="{{ route('report_expenses_download') }}" method="GET">
            
            <input type="hidden" name="year" value="{{ Request::get('year') }} ">
            <input type="hidden" name="exclude_billable" value="{{ Request::get('exclude_billable') }} ">
            <button type="submit" class="btn btn-light float-md-right">
            <i class="far fa-file-pdf"></i> @lang('form.download')
            </button>
         </form>
      </div>
   </div>
   <hr>
   <form id="filterForm" class="form-inline" action="{{ route('report_expenses_page') }}" method="GET">
      <label class="my-1 mr-2" for="inlineFormCustomSelectPref">@lang('form.year')</label>
      <?php
         echo form_dropdown('year', $data['year_list'] , Request::get('year'), "class='my-1 mr-sm-2'");
         ?>         
      <div class="custom-control custom-checkbox my-1 mr-sm-2">
         <input {{ ( Request::get('exclude_billable') == '1') ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="customControlInline" name="exclude_billable" value="1">
         <label class="custom-control-label"  for="customControlInline">@lang('form.exclude_billable_expenses')</label>
      </div>
   </form>
   <br>
   <div class="table-responsive" style="font-size: 13px;">
      <table class="table table-sm table-bordered table-hover expenses-report" id="expenses-report-table">
         <thead>
            <tr>
               <th scope="col">@lang('form.category')</th>
               <th scope="col">@lang('form.january')</th>
               <th scope="col">@lang('form.february')</th>
               <th scope="col">@lang('form.march')</th>
               <th scope="col">@lang('form.april')</th>
               <th scope="col">@lang('form.may')</th>
               <th scope="col">@lang('form.june')</th>
               <th scope="col">@lang('form.july')</th>
               <th scope="col">@lang('form.august')</th>
               <th scope="col">@lang('form.september')</th>
               <th scope="col">@lang('form.october')</th>
               <th scope="col">@lang('form.november')</th>
               <th scope="col">@lang('form.december')</th>
               <th scope="col">@lang('form.year')  ({{ (Request::get('year')) ? Request::get('year') : date("Y") }})</th>
            </tr>
         </thead>
         <tbody>
            @foreach($expenses as $expense)
            <tr>
               <td><b>{{ $expense['name'] }}</b></td>
               <?php $sub_total = 0; ?>
               @foreach(range(1,12) as $month)
               <td class="text-right">{{ format_currency($expense['month_'.$month], TRUE) }}</td>
               <?php $sub_total += $expense['month_'.$month] ; ?>
               @endforeach
               <td class="text-right" style="background-color: #eee;">{{ format_currency($sub_total, TRUE) }}</td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
</div>
    

  


@endsection

@section('onPageJs')

    
    
    <script>

        $(function() {

            $('input[type=checkbox], select').change(function(){

                $('#filterForm').submit();
            });


        });



    </script>


@endsection
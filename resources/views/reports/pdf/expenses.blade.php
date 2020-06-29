<htmlpageheader name="firstpageheader" style="display:none">
    <h3>{{ $data['page_title'] }}</h3>


</htmlpageheader>

<style type="text/css">
    th{
        width: 10%;
    }
    td{
        width: 10%;
         border: 1px solid #eee;
    }
    tr{
      border: 1px solid #eee;
    }
    
</style>

<table class="table table-sm">
         <thead>
            <tr>
               <th style="text-align: left;">@lang('form.category')</th>
               <th>@lang('form.january')</th>
               <th>@lang('form.february')</th>
               <th>@lang('form.march')</th>
               <th>@lang('form.april')</th>
               <th>@lang('form.may')</th>
               <th>@lang('form.june')</th>
               <th>@lang('form.july')</th>
               <th>@lang('form.august')</th>
               <th>@lang('form.september')</th>
               <th>@lang('form.october')</th>
               <th>@lang('form.november')</th>
               <th>@lang('form.december')</th>
               <th>@lang('form.year')  ({{ (Request::get('year')) ? Request::get('year') : date("Y") }})</th>
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
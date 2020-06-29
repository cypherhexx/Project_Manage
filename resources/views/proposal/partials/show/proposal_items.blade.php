<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table items estimate-items-preview">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="description text-left" width="50%">@lang('form.item')</th>
                    <th class="text-right">{{ $rec->show_quantity_as }}</th>
                    <th class="text-right">@lang('form.rate')</th>
                    <th class="text-right">@lang('form.tax')</th>
                    <th class="text-right">@lang('form.amount')</th>
                </tr>
                </thead>
                <tbody class="ui-sortable">
                @if(isset($rec->item_line) && !empty($rec->item_line))
                    @foreach($rec->item_line as $key=>$row)                       
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td class="description text-left">
                                <span style="font-size:px;"><strong>{{ $row->description }}</strong></span>
                                <br>
                                <span style="color:#424242;">{{ $row->long_description }}</span>
                            </td>
                            <td class="text-right">{{ $row->quantity }}</td>
                            <td class="text-right">{{ format_currency($row->rate) }}</td>
                            <td class="text-right">{{ display_tax_rate_in_item_list($row->tax_id , $rec) }}</td>
                            <td class="amount text-right" >{{ format_currency($row->sub_total , TRUE, $rec->get_currency_symbol() ) }}</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4 offset-md-8">
        <table class="table text-right">
            <tbody>
            <tr>
                <td style="text-align: right;"><span class="bold">@lang('form.sub_total')</span>
                </td>
                <td class="subtotal">{{ format_currency($rec->sub_total) }}</td>

            </tr>
            @if(isset($rec->discount_total) && ($rec->discount_total > 0))
                <tr>
                    <td><span class="bold">@lang('form.discount')</span>
                    </td>
                    <td class="subtotal">{{ format_currency($rec->discount_total) }}</td>

                </tr>
            @endif
           
            @if(isset($rec->array_of_taxes_used) &&  !empty($rec->array_of_taxes_used) )
                @foreach($rec->array_of_taxes_used as $t)
                    <tr class="tax-area">
                        <td class="bold">{{ str_replace($t->rate.'%',"", $t->name) }} ({{ number_format($t->rate, 2) }}%)</td>
                        <td>{{ $t->amount }}</td>
                    </tr>
                @endforeach
            @endif

            @if(isset($rec->adjustment) && $rec->adjustment != 0)
               <tr>
                  <td class="bold">@lang('form.adjustment')</td>
                  <td>{{ format_currency($rec->adjustment, true, $rec->get_currency_symbol()) }}</td>
               </tr>
            @endif

            <tr>
                <td><span class="bold">@lang('form.total')</span></td>
                <td class="total">{{ format_currency($rec->total, TRUE, $rec->get_currency_symbol() ) }}</td>
            </tr>


            </tbody>
        </table>
    </div>

</div>
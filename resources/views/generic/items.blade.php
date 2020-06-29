<div class="main-content" style="margin-bottom: 0 !important;">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">

                <?php

                echo form_dropdown('item_id', [], NULL, "class='form-control item_id selectpicker'");

                ?>
                <div class="invalid-feedback">@php if($errors->has('component_id')) { echo $errors->first('component_id') ; } @endphp</div>
            </div>
        </div>

        <div class="col-md-6">
            <?php
                $show_quantity_as = old_set('show_quantity_as', NULL,$rec);



            ?>
            <div class="float-md-right">
                <div class="form-check form-check-inline">
                    <label>
                        @lang('form.show_quantity_as') :
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_quantity_as" value="{{ __(('form.qty')) }}" {{ (($show_quantity_as == __(('form.qty'))) || ($show_quantity_as == '')) ? 'checked' : ''  }}>
                    <label class="form-check-label">
                        @lang('form.qty')
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_quantity_as" value="{{ __(('form.hours')) }}" {{ (($show_quantity_as == __(('form.hours'))) ) ? 'checked' : '' }}>
                    <label class="form-check-label">
                        @lang('form.hours')
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_quantity_as" value="{{ __(('form.qty_slash_hours')) }}" {{ (($show_quantity_as == __(('form.qty_slash_hours'))) ) ? 'checked' : '' }}>
                    <label class="form-check-label">
                        @lang('form.qty_slash_hours')
                    </label>
                </div>
            </div>
        </div>
    </div>

    <table class="table items">
        <thead>
            <tr>
                <td>@lang('form.item')</td>
                <td>@lang('form.description')</td>
                <td class="quantity">{{ old_set('show_quantity_as',  __('form.qty'),$rec) }}</td>
                <td>@lang('form.rate')</td>
                <td>@lang('form.tax')</td>
                <td class="text-right">@lang('form.amount')</td>
                <td></td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7"><a class="btn btn-success btn-sm" id="add_new_line" v-on:click="addRow($event);" href="#">
                        <i class="fas fa-plus"></i> @lang('form.add_new_line')</a>
                </td>
            </tr>
        </tfoot>

        <tr v-for="(row, index) in rows">

            <input type="hidden" :name="'items[' + index + '][id]'" v-model="row.id">
            <input v-if="row.component_number" type="hidden" :name="'items[' + index + '][component_number]'" v-model="row.component_number">
            <input v-if="row.component_id" type="hidden" :name="'items[' + index + '][component_id]'" v-model="row.component_id">
            
            <td>
                <textarea :name="'items[' + index + '][description]'" v-model="row.description"  rows="4" class="form-control form-control-sm" placeholder="Description" aria-invalid="false"></textarea>
                <div class="invalid-feedback d-block" v-if="itemHasValidationError('description', index)">@{{ row.validation_error.description  }}</div>
            </td>
            <td>
                <textarea :name="'items[' + index + '][long_description]'" v-model="row.long_description"  rows="4" class="form-control form-control-sm" placeholder="Long description" aria-invalid="false"></textarea>
            </td>
            <td style="width: 10%;">
                <input  type="text" :name="'items[' + index + '][quantity]'" v-model="row.quantity" v-on:keypress="isNumber($event)"  class="form-control form-control-sm text-center" placeholder="Quantity">
                <div class="invalid-feedback d-block" v-if="itemHasValidationError('quantity', index)">@{{ row.validation_error.quantity  }}</div>
                <input type="text" :name="'items[' + index + '][unit]'" v-model="row.unit" placeholder="Unit" class="form-control input-transparent text-right">

            </td>
            <td style="width: 15%;">
                <input type="text" :name="'items[' + index + '][rate]'" v-model="row.rate" v-on:keypress="isNumber($event)"  class="form-control form-control-sm text-right" placeholder="Rate" aria-invalid="false">
                 <div class="invalid-feedback d-block" v-if="itemHasValidationError('rate', index)">@{{ row.validation_error.rate  }}</div>
            </td>
            <td>
                <div class="select2-wrapper">
                        <select2-multiple :name="'items[' + index + '][tax_id][]'" v-model="row.tax_id"   :options="options" >

                        </select2-multiple>
                </div>
            </td>
            <td class="text-right">
                @{{ row.sub_total | formatNumber }}
                <input type="hidden" :name="'items[' + index + '][sub_total]'" v-model="row.sub_total">
            </td>
            <td>
                <button type="button" v-on:click="removeItem(index);"  class="btn btn-sm pull-right btn-info"><i class="far fa-trash-alt"></i></button>
            </td>
        </tr>
        </tbody>


    </table>

    <table class="table text-right">
        <tbody>
        <tr id="subtotal">
            <td><span class="bold">@lang('form.sub_total') :</span>
            </td>
            <td class="sub_total"> @{{ sub_total | formatNumber }} <input type="hidden" v-model="sub_total" name="sub_total"></td>
        </tr>
        <tr id="discount_area">
            <td>
                <div class="row">
                    <div class="col-md-7">
                        <span class="bold">@lang('form.discount')</span>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group" id="discount-total">

                            <input type="hidden" value="" v-model="discount_total" name="discount_total">
                            <input type="hidden" value="" v-model="discount_method_id" name="discount_method_id">

                            <div class="input-group">
                                <input type="text" name="discount_rate" v-model="discount_rate" v-on:keypress="isNumber($event)"  class="form-control form-control-sm text-right">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle discount_method_btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" v-on:click="discountMethodChanged('%', {{ DISCOUNT_METHOD_PERCENTAGE }}, $event);" href="#">%</a>
                                        <a class="dropdown-item" v-on:click="discountMethodChanged('{{ __('form.fixed_amount') }}',{{ DISCOUNT_METHOD_FIXED }}, $event);" href="#" >@lang('form.fixed_amount')</a>

                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </td>
            <td class="discount-total">-@{{ discount_total | formatNumber }} <input type="hidden" v-model="discount_total" name="discount_total"></td>
        </tr>

        <tr v-for="(row, index) in taxRows" v-if="row">
                <td>@{{ row.name }}</td>
                <td>@{{ row.amount | formatNumber }}</td>

            <input type="hidden" :name="'taxes[' + index + '][id]'"   v-bind:value="row.id">
            <input type="hidden" :name="'taxes[' + index + '][name]'"   v-bind:value="row.name">
            <input type="hidden" :name="'taxes[' + index + '][rate]'" v-bind:value="row.rate">
            <input type="hidden" :name="'taxes[' + index + '][amount]'" v-bind:value="row.amount">

        </tr>
        <tr>
            <td>
                <div class="row">
                    <div class="col-md-7">
                        <span class="bold">@lang('form.adjustment')</span>
                    </div>
                    <div class="col-md-5">
                        <input type="text" v-model="adjustment" v-on:keypress="isNumber($event)" class="form-control form-control-sm text-right" name="adjustment">
                    </div>
                </div>
            </td>
            <td class="adjustment">@{{ (adjustment && (!isNaN(adjustment))) | formatNumber }}</td>
        </tr>
        <tr>
            <td><span class="bold">@lang('form.total') :</span>
            </td>
            <td class="total">@{{ total | formatNumber }}<input type="hidden" name="total"  v-model="total" ></td>
        </tr>
        </tbody>
    </table>

</div>

@{{ runOperation }}
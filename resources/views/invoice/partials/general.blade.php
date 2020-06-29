<div class="main-content" style="margin-bottom: 20px !important;">
    
    <h5>@lang('form.invoice')</h5>
    <hr>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="customer_id">@lang('form.customer') <span class="required">*</span></label>
                <?php
                $errorClass = ($errors->has('customer_id')) ? 'is-invalid' : '';
                echo form_dropdown('customer_id', $data['customer_id_list'] , old_set('customer_id', NULL,$rec), "class='form-control  customer_id $errorClass '");

                ?>
                <div class="invalid-feedback">@php if($errors->has('customer_id')) { echo $errors->first('customer_id') ; } @endphp</div>

            </div>


            <div class="form-group">
                <label for="project_id">@lang('form.project')</label>
                <?php
                $errorClass = ($errors->has('project_id')) ? 'is-invalid' : '';
                echo form_dropdown('project_id', $data['project_id_list'] , old_set('project_id', NULL,$rec), "class='form-control form-control-sm  project_id $errorClass '");

                ?>
                <div class="invalid-feedback d-block">@php if($errors->has('project_id')) { echo $errors->first('project_id') ; } @endphp</div>

            </div>


        <?php

        if(isset($rec->id))
        {
           $date_class = 'initially_empty_datepicker';
           $date_value =  sql2date(old_set('date', NULL, $rec));      
        }
        else
        {            
           $date_class = 'datepicker';
           $date_value =  old_set('date', NULL, $rec);

        }
        $due_date_value = (old_set('due_date', NULL, $rec)) ? sql2date(old_set('due_date', NULL, $rec)) : "" ;   
        ?>


            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.invoice_date') <span class="required">*</span> </label>
                    <input type="text" class="form-control form-control-sm {{ $date_class }}  @php if($errors->has('date')) { echo 'is-invalid'; } @endphp" name="date" value="{{ $date_value }}">
                    <div class="required">@php if($errors->has('date')) { echo $errors->first('date') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.due_date') </label>
                    <input type="text" class="form-control form-control-sm initially_empty_datepicker  @php if($errors->has('due_date')) { echo 'is-invalid'; } @endphp" name="due_date" value="{{ $due_date_value }}">
                    <div class="invalid-feedback">@php if($errors->has('due_date')) { echo $errors->first('due_date') ; } @endphp</div>
                </div>






            </div>

            <div class="form-row">
                <div class="form-group col-md-6">

                    <label>@lang('form.currency') <span class="required">*</span> </label>
                    <?php
                    echo form_dropdown('currency_id', $data['currency_id_list'], old_set('currency_id', NULL,$rec), "class='form-control selectpicker'");

                    ?>
                    <div class="required">@php if($errors->has('currency_id')) { echo $errors->first('currency_id') ; } @endphp</div>

                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.discount_type')</label>
                    <?php
                    echo form_dropdown('discount_type_id', $data['discount_type_id_list'], old_set('discount_type_id', NULL,$rec), "class='form-control selectpicker'");

                    ?>
                </div>
            </div>

            <div class="form-row">

                <div class="form-group col-md-6">
                    <label>@lang('form.reference')  </label>
                    <input type="text" class="form-control form-control-sm" name="reference" value="{{ old_set('reference', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('reference')) { echo $errors->first('reference') ; } @endphp</div>
                </div>

                <div class="form-group col-md-6">
                    <label>@lang('form.sales_agent')</label>
                    <?php
                    echo form_dropdown('sales_agent_id', $data['sales_agent_id_list'] , old_set('sales_agent_id', NULL,$rec), "class='form-control  selectpicker'");
                    ?>
                </div>
            </div>


            <div class="form-group">
                <label for="group_id">@lang('form.tag')</label>
                <div class="select2-wrapper">
                    <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control select2-multiple' multiple='multiple'") ?>
                </div>
                <div class="invalid-feedback">@php if($errors->has('tag_id')) { echo $errors->first('tag_id') ; } @endphp</div>
            </div>

            <?php echo bottom_toolbar(__('form.submit')); ?>
        </div>
        <div class="col-md-6">


            <div id="billing_address_form">
                <div class="form-group">
                    <label>@lang('form.billing_address')  </label>
                    <textarea id="address" name="address" placeholder="{{ __('form.street') }}" class="form-control ">{{ old_set('address', NULL, $rec) }}</textarea>
                    <div class="invalid-feedback">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">

                        <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.city') }}" name="city" value="{{ old_set('city', NULL, $rec) }}">
                        <div class="form-control-feedback">@php if($errors->has('city')) { echo $errors->first('city') ; } @endphp</div>
                    </div>
                    <div class="form-group col-md-6">

                        <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.state') }}" name="state" value="{{ old_set('state', NULL, $rec) }}">
                        <div class="form-control-feedback">@php if($errors->has('state')) { echo $errors->first('state') ; } @endphp</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">

                        <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.zip_code') }}" name="zip_code" value="{{ old_set('zip_code', NULL, $rec) }}">
                        <div class="form-control-feedback">@php if($errors->has('zip_code')) { echo $errors->first('zip_code') ; } @endphp</div>
                    </div>
                    <div class="form-group col-md-6">

                        <div class="select2-wrapper">
                            <?php echo form_dropdown("country_id", $data['country_id_list'], old_set("country_id", NULL, $rec), "class='form-control  selectpicker' data-placeholder='".__('form.country')."' ") ?>
                        </div>
                        <div class="form-control-feedback">@php if($errors->has('country_id')) { echo $errors->first('country_id') ; } @endphp</div>
                    </div>
                </div>
            </div>




            <div class="form-group">
                <label>@lang('form.shipping_address')  </label>
                <textarea id="shipping_address " name="shipping_address" placeholder="{{ __('form.street') }}" class="form-control form-control-sm">{{ old_set('shipping_address', NULL, $rec) }}</textarea>
                <div class="form-control-feedback">@php if($errors->has('shipping_address')) { echo $errors->first('shipping_address') ; } @endphp</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.city') }}" name="shipping_city" value="{{ old_set('shipping_city', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('shipping_city')) { echo $errors->first('shipping_city') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.state') }}" name="shipping_state" value="{{ old_set('shipping_state', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('shipping_state')) { echo $errors->first('shipping_state') ; } @endphp</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="text" class="form-control form-control-sm" placeholder="{{ __('form.zip_code') }}" name="shipping_zip_code" value="{{ old_set('shipping_zip_code', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('shipping_zip_code')) { echo $errors->first('shipping_zip_code') ; } @endphp</div>
                </div>

                <div class="form-group col-md-6">
                    <div class="select2-wrapper">
                        <?php echo form_dropdown("shipping_country_id", $data['country_id_list'], old_set("shipping_country_id", NULL, $rec), "class='form-control form-control-sm  selectpicker '") ?>
                    </div>
                    <div class="form-control-feedback">@php if($errors->has('shipping_country_id')) { echo $errors->first('shipping_country_id') ; } @endphp</div>

                </div>

            </div>

            <div class="form-row">
                <div class="custom-control custom-checkbox">
                  <input {{ (old_set("allow_partial_payment", NULL, $rec)) ? 'checked' : '' }} type="checkbox" name="allow_partial_payment" value="1" class="custom-control-input" id="allow_partial_payment">
                  <label class="custom-control-label" for="allow_partial_payment">@lang('form.allow_partial_payment')</label>
                </div>
            </div>    

        </div>
    </div>
</div>
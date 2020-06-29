<div class="main-content" style="margin-bottom: 0 !important;">
    <h5>@lang('form.proposal')</h5>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>@lang('form.title') <span class="required">*</span> </label>
                <input type="text" class="form-control form-control-sm  @php if($errors->has('title')) { echo 'is-invalid'; } @endphp" name="title" value="{{ old_set('title', NULL,$rec) }}">
                <div class="invalid-feedback">@php if($errors->has('title')) { echo $errors->first('title') ; } @endphp</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.related_to') <span class="required">*</span></label>
                    <?php
                    $errorClass = ($errors->has('component_id')) ? 'is-invalid' : '';
                    echo form_dropdown('component_id', $data['component_id_list'], old_set('component_id', NULL,$rec), "class='form-control related_to  selectPickerWithoutSearch $errorClass'");

                    ?>
                    <div class="invalid-feedback">@php if($errors->has('component_id')) { echo $errors->first('component_id') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label for="component_number">
                    <?php
                        $component_id = old_set('component_id', NULL,$rec);
                        if($component_id && ($component_id == COMPONENT_TYPE_CUSTOMER))
                            {
                                echo __('form.customer');
                            }
                            elseif ($component_id && ($component_id == COMPONENT_TYPE_LEAD))
                            {
                                echo __('form.lead');
                            }
                            else
                            {
                                echo '&nbsp';
                            }
                    ?>
                    </label>
                    <?php
                    $errorClass = ($errors->has('component_number')) ? 'is-invalid' : '';
                    echo form_dropdown('component_number', $data['component_number_options'], old_set('component_number', NULL,$rec), "class='form-control  component_number $errorClass '");

                    ?>
                    <div class="invalid-feedback">@php if($errors->has('component_number')) { echo $errors->first('component_number') ; } @endphp</div>
                </div>
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
            $open_till_value = (old_set('open_till', NULL, $rec)) ? sql2date(old_set('open_till', NULL, $rec)) : "" ;   
        ?>


            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.date') <span class="required">*</span> </label>
                    <input type="text" class="form-control form-control-sm {{ $date_class }}  @php if($errors->has('date')) { echo 'is-invalid'; } @endphp" name="date" value="{{ $date_value }}">
                    <div class="invalid-feedback">@php if($errors->has('date')) { echo $errors->first('date') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.open_till') </label>
                    <input type="text" class="form-control form-control-sm initially_empty_datepicker  @php if($errors->has('open_till')) { echo 'is-invalid'; } @endphp" name="open_till" value="{{ $open_till_value }}">
                    <div class="invalid-feedback">@php if($errors->has('open_till')) { echo $errors->first('open_till') ; } @endphp</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.currency') <span class="required">*</span> </label>
                    <?php
                    echo form_dropdown('currency_id', $data['currency_id_list'], old_set('currency_id', NULL,$rec), "class='form-control selectpicker'");

                    ?>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.discount_type')</label>
                    <?php
                    echo form_dropdown('discount_type_id', $data['discount_type_id_list'], old_set('discount_type_id', NULL,$rec), "class='form-control selectpicker'");

                    ?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_id">@lang('form.tag')</label>
                <div class="select2-wrapper">
                    <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control select2-multiple' multiple='multiple'") ?>
                </div>
                <div class="invalid-feedback">@php if($errors->has('tag_id')) { echo $errors->first('group_id') ; } @endphp</div>
            </div>
            <?php echo bottom_toolbar(__('form.submit'));?>
        </div>
        <div class="col-md-6">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.status')</label>
                    <?php
                    echo form_dropdown('status_id', $data['status_id_list'] , old_set('status_id', NULL,$rec), "class='form-control  selectPickerWithoutSearch'");
                    ?>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.assigned_to')</label>
                    <?php
                    echo form_dropdown('assigned_to', $data['assigned_to_list'] , old_set('assigned_to', NULL,$rec), "class='form-control  selectpicker'");
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label>@lang('form.to') <span class="required">*</span> </label>
                <input type="text" class="form-control form-control-sm  @php if($errors->has('send_to')) { echo 'is-invalid'; } @endphp" name="send_to" value="{{ old_set('send_to', NULL,$rec) }}">
                <div class="invalid-feedback">@php if($errors->has('send_to')) { echo $errors->first('send_to') ; } @endphp</div>
            </div>
            <div class="form-group">
                <label>@lang('form.address')  </label>
                <textarea id="address" name="address" placeholder="" class="form-control ">{{ old_set('address', NULL, $rec) }}</textarea>
                <div class="invalid-feedback">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.city')  </label>
                    <input type="text" class="form-control form-control-sm" name="city" value="{{ old_set('city', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('city')) { echo $errors->first('city') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.state')  </label>
                    <input type="text" class="form-control form-control-sm" name="state" value="{{ old_set('state', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('state')) { echo $errors->first('state') ; } @endphp</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.zip_code')  </label>
                    <input type="text" class="form-control form-control-sm" name="zip_code" value="{{ old_set('zip_code', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('zip_code')) { echo $errors->first('zip_code') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.country')  </label>
                    <div class="select2-wrapper">
                        <?php echo form_dropdown("country_id", $data['country_id_list'], old_set("country_id", NULL, $rec), "class='form-control  selectpicker '") ?>
                    </div>
                    <div class="form-control-feedback">@php if($errors->has('country_id')) { echo $errors->first('country_id') ; } @endphp</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>@lang('form.email') <span class="required">*</span> </label>
                    <input type="text" class="form-control form-control-sm" name="email" value="{{ old_set('email', NULL, $rec) }}">

                    <div class="invalid-feedback">@php if($errors->has('email')) { echo $errors->first('email') ; } @endphp</div>
                </div>
                <div class="form-group col-md-6">
                    <label>@lang('form.phone')  </label>
                    <input type="text" class="form-control form-control-sm" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
                    <div class="form-control-feedback">@php if($errors->has('phone')) { echo $errors->first('phone') ; } @endphp</div>
                </div>
            </div>
        </div>
    </div>
</div>
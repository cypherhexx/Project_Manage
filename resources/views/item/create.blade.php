@extends('layouts.main')
@section('title', (isset($item->id)) ? __('form.edit') . " ". __('form.item') :   __('form.add_new_item')   )
@section('content')
 
 <div class="row">
       <div class="col-md-6">

        <div class="main-content">
            <h5>@lang('form.item')</h5>
            <hr>
            <form method="post" action="{{ (isset($item->id)) ? route( 'patch_item', $item->id) : route('post_item') }}">       
                   
                        {{ csrf_field()  }}
                        @if(isset($item->id))
                            {{ method_field('PATCH') }}
                        @endif
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label>@lang('form.name') <span class="required">*</span> </label>
                                <input type="text" class="form-control form-control-sm  @php if($errors->has('name')) { echo 'is-invalid'; } @endphp" name="name" value="{{ old_set('name', NULL,$item) }}">
                                <div class="invalid-feedback">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
                            </div>
                            <div class="form-group col-md-4">
                                <label>@lang('form.rate') <span class="required">*</span></label>
                                <input type="number" class="form-control form-control-sm @php if($errors->has('rate')) { echo 'is-invalid'; } @endphp " name="rate" value="{{ old_set('rate', NULL,$item) }}">
                                <div class="invalid-feedback">@php if($errors->has('rate')) { echo $errors->first('rate') ; } @endphp</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('form.description') </label>
                            <textarea class="form-control form-control-sm" name="description" rows="2">{{ old_set('description', NULL,$item) }}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>@lang('form.unit') </label>
                                <input type="text" class="form-control form-control-sm @php if($errors->has('unit')) { echo 'is-invalid'; } @endphp " name="unit" value="{{ old_set('unit', NULL,$item) }}">
                                <div class="invalid-feedback">@php if($errors->has('unit')) { echo $errors->first('unit') ; } @endphp</div>
                            </div>
                            <div class="form-group  col-md-6">
                                <label>@lang('form.item_group')</label>
                                <?php
                                echo form_dropdown('item_category_id', $item_category_list, old_set('item_category_id', NULL,$item), "class='form-control form-control-sm  selectPickerWithoutSearch'");
                                ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>@lang('form.tax_1')</label>
                                <?php
                                echo form_dropdown('tax_id_1', $taxes_list , old_set('tax_id_1', NULL,$item), "class='form-control form-control-sm  selectPickerWithoutSearch'");
                                ?>
                            </div>
                            <div class="form-group  col-md-6">
                                <label>@lang('form.tax_2')</label>
                                <?php
                                echo form_dropdown('tax_id_2', $taxes_list, old_set('tax_id_2', NULL,$item), "class='form-control form-control-sm  selectPickerWithoutSearch'");
                                ?>
                            </div>
                        </div>
                      <?php echo bottom_toolbar(); ?>
                   
               
            </form>
        </div>
    </div>
 </div>   

@endsection
@section('onPageJs')
@endsection
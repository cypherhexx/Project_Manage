@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.edit_lead').' : '.$rec->first_name. " ". $rec->last_name : __('form.add_new_lead') )
@section('content')
<div class="main-content">
     <h5>{{ (isset($rec->id)) ?  __('form.lead').' : ' . $rec->first_name. " ". $rec->last_name : __('form.add_new_lead') }}</h5>
    <hr>

<div id="formArea" class="{{ (isset($rec->id) && (Route::currentRouteName() == 'show_lead_page') ) ? 'hide' : '' }}">
    <form method="post" action='{{ (isset($rec->id)) ? route( 'patch_lead', $rec->id) : route('post_lead') }}'>

        {{ csrf_field()  }}
        @if(isset($rec->id))
            {{ method_field('PATCH') }}
        @endif


        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="lead_status_id">@lang('form.status') <span class="required">*</span></label>
                <?php echo form_dropdown("lead_status_id", $data['lead_status_id_list'], old_set("lead_status_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                <div class="invalid-feedback">@php if($errors->has('lead_status_id')) { echo $errors->first('lead_status_id') ; } @endphp</div>
            </div>

            <div class="form-group col-md-4">
                <label for="lead_source_id">@lang('form.source') <span class="required">*</span></label>
                <?php echo form_dropdown("lead_source_id", $data['lead_source_id_list'], old_set("lead_source_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                <div class="invalid-feedback">@php if($errors->has('lead_source_id')) { echo $errors->first('lead_source_id') ; } @endphp</div>
            </div>

            <div class="form-group col-md-4">
                <label for="assigned_to">@lang('form.assigned_to')</label>
                <?php echo form_dropdown("assigned_to", $data['assigned_to_list'], old_set("assigned_to", NULL, $rec), "class='form-control form-control-sm selectpicker'") ?>
                <div class="invalid-feedback">@php if($errors->has('assigned_to')) { echo $errors->first('assigned_to') ; } @endphp</div>
            </div>
        </div>


    

        <div class="row">



            <div class="col-md-6">
                <div class="form-group">
                    <label for="name">@lang('form.first_name') <span class="required">*</span> </label>
                    <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'first_name') }}"
                           id="first_name" name="first_name" value="{{ old_set('first_name', NULL, $rec) }}">
                    <div class="invalid-feedback">{{ showError($errors, 'first_name') }}</div>
                </div>

                <div class="form-group">
                    <label for="name">@lang('form.last_name') <span class="required">*</span> </label>
                    <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'last_name') }}"
                           id="last_name" name="last_name" value="{{ old_set('last_name', NULL, $rec) }}">
                    <div class="invalid-feedback">{{ showError($errors, 'last_name') }}</div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">@lang('form.email')</label>
                        <input type="text" class="form-control form-control-sm @php if($errors->has('email')) { echo 'is-invalid'; } @endphp"
                               id="email" name="email" value="{{ old_set('email', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('email')) { echo $errors->first('email') ; } @endphp</div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="phone">@lang('form.phone')</label>
                        <input type="text" class="form-control form-control-sm @php if($errors->has('phone')) { echo 'is-invalid'; } @endphp"
                               id="phone" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('phone')) { echo $errors->first('phone') ; } @endphp</div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="form-group col-md-6">
                    <label for="company">@lang('form.company')</label>
                    <input type="text" class="form-control form-control-sm @php if($errors->has('company')) { echo 'is-invalid'; } @endphp"
                           id="company" name="company" value="{{ old_set('company', NULL, $rec) }}">
                    <div class="invalid-feedback">@php if($errors->has('company')) { echo $errors->first('company') ; } @endphp</div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="position">@lang('form.position')</label>
                        <input type="text" class="form-control form-control-sm @php if($errors->has('position')) { echo 'is-invalid'; } @endphp"
                               id="position" name="position" value="{{ old_set('position', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('position')) { echo $errors->first('position') ; } @endphp</div>
                    </div>

                </div>                

                

                <div class="form-group">
                    <label for="website">@lang('form.website')</label>
                    <input type="text" class="form-control form-control-sm @php if($errors->has('website')) { echo 'is-invalid'; } @endphp"
                           id="website" name="website" value="{{ old_set('website', NULL, $rec) }}">
                    <div class="invalid-feedback">@php if($errors->has('website')) { echo $errors->first('website') ; } @endphp</div>
                </div>



                
            </div>


            <div class="col-md-6">

                <div class="form-group">
                    <label for="address">@lang('form.address')</label>
                    <textarea rows="4" class="form-control form-control-sm @php if($errors->has('address')) { echo 'is-invalid'; } @endphp"
                              id="address" name="address" rows=1">{{ old_set('address', NULL, $rec) }}</textarea>
                    <div class="invalid-feedback">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
                </div>


                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="phone">@lang('form.city')</label>
                        <input type="text" class="form-control form-control-sm" name="city" value="{{ old_set('city', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('city')) { echo $errors->first('city') ; } @endphp</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">@lang('form.state')</label>
                        <input type="text" class="form-control form-control-sm" name="state" value="{{ old_set('state', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('state')) { echo $errors->first('state') ; } @endphp</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="phone">@lang('form.zip_code')</label>
                        <input type="text" class="form-control form-control-sm" name="zip_code" value="{{ old_set('zip_code', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('zip_code')) { echo $errors->first('zip_code') ; } @endphp</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">@lang('form.country')</label>
                        <div class="select2-wrapper">
                            <?php echo form_dropdown("country_id", $data['country_id_list'], old_set("country_id", NULL, $rec), "class='form-control form-control-sm selectpicker '") ?>
                        </div>
                        <div class="invalid-feedback">@php if($errors->has('country_id')) { echo $errors->first('country_id') ; } @endphp</div>

                    </div>
                </div>


            </div>





        </div>

        <div class="form-group">
            <label for="address">@lang('form.description')</label>
            <textarea rows="4" class="form-control form-control-sm@php if($errors->has('description')) { echo 'is-invalid'; } @endphp"
                      id="description" name="description" rows=1">{{ old_set('description', NULL, $rec) }}</textarea>
            <div class="invalid-feedback">@php if($errors->has('description')) { echo $errors->first('description') ; } @endphp</div>
        </div>

         <div class="form-group">
            <label for="group_id">@lang('form.tag')</label>
            <div class="select2-wrapper">
                <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control select2-multiple' multiple='multiple'") ?>
            </div>
            <div class="invalid-feedback">@php if($errors->has('tag_id')) { echo $errors->first('group_id') ; } @endphp</div>
        </div>
   

        <?php echo bottom_toolbar(); ?>

    </form>
</div>

</div>
@endsection
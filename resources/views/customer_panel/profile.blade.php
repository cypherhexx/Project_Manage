@extends('layouts.customer.main')
@section('title', __('form.profile'))
@section('content')

  <div class="row">

    <div class="col-md-8">
        <div class="main-content">
     <h5>@lang('form.profile')</h5>
        <hr>
        <form action="{{ route('cp_patch_user_profile') }}" method="POST" autocomplete="off">
            {{ csrf_field()  }}
            {{ method_field('PATCH') }} 

            <div class="form-row">
                
                <div class="form-group col-md-6">
                    <label>@lang('form.first_name') <span class="required">*</span></label>
                    <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'first_name') }}" name="first_name" value="{{ old_set('first_name', NULL, $rec) }}">
                     <div class="invalid-feedback">{{ showError($errors, 'first_name') }}</div>
                </div>

                <div class="form-group col-md-6">
                    <label>@lang('form.last_name') <span class="required">*</span></label>
                    <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'last_name') }}" name="last_name" value="{{ old_set('last_name', NULL, $rec) }}">
                    <div class="invalid-feedback">{{ showError($errors, 'last_name') }}</div>
                </div>    

            </div>

            <div class="form-group">
                <label>@lang('form.email') <span class="required">*</span></label>
                <input type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'email') }}" name="email" value="{{ old_set('email', NULL, $rec) }}">
                 <div class="invalid-feedback">{{ showError($errors, 'email') }}</div>
            </div>

            <div class="form-group">
                <label>@lang('form.phone')</label>
                <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'phone') }}" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
                <div class="invalid-feedback">{{ showError($errors, 'phone') }}</div>
            </div> 


            <div class="form-group">
                <label>@lang('form.position')</label>
                <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'position') }}" name="position" value="{{ old_set('position', NULL, $rec) }}">
                <div class="invalid-feedback">{{ showError($errors, 'phone') }}</div>
            </div>

            <input type="submit" class="btn btn-primary" name="submit" value="@lang('form.submit')" />

        </form>

    </div>

    </div>

    <div class="col-md-4">
            

    </div>

    </div>
@endsection

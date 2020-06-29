@extends('layouts.customer.main')
@section('title', __('form.change_password'))
@section('content')

  <div class="row">

 

    <div class="col-md-8">
            
       
        <div class="main-content">
            <h5>@lang('form.change_password')</h5>
            <hr>
            <form action="{{ route('cp_patch_change_password') }}" method="POST" autocomplete="off">
                {{ csrf_field()  }}
                {{ method_field('PATCH') }} 

                <div class="form-group">
                <label>@lang('form.current_password') <span class="required">*</span></label>
                <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'current_password') }}" name="current_password">
                <div class="invalid-feedback">{{ showError($errors, 'current_password') }}</div>
            </div> 


            <div class="form-group">
                <label>@lang('form.new_password') <span class="required">*</span></label>
                <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'new_password') }}" name="new_password">
                <div class="invalid-feedback">{{ showError($errors, 'new_password') }}</div>
            </div>

            <div class="form-group">
                <label>@lang('form.confirm_password') <span class="required">*</span></label>
                <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'confirm_password') }}" name="confirm_password">
                <div class="invalid-feedback">{{ showError($errors, 'confirm_password') }}</div>
            </div>

            <input type="submit" class="btn btn-primary" name="submit" value="@lang('form.change')" />


             </form>   
        </div>
    </div>

    </div>
@endsection

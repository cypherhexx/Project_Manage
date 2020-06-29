@extends('installer.template')
@section('title', "General Information")
@section('content')




<div class="card mx-auto" style="width: 40rem; margin-bottom: 10%; font-size: 13px;">
   <div class="card-body">
      <h4 class="card-title">General Information</h4>
      <hr>
      @if (Session::has('error_msg'))
            <div class="alert alert-danger" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
            {!! session('error_msg') !!}</div>
       @endif
      <form action="{{ route('run_installation_step_2') }}" method="POST">
        {{ csrf_field()  }}
         
         <div class="form-row">
              
              <div class="form-group col-md-6">
            <label>First Name <span class="required">*</span></label>
            <input  type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'first_name') }}" name="first_name" required value="{{ old_set('first_name', NULL, $rec) }}">
            <div class="invalid-feedback">{{ showError($errors, 'first_name') }}</div>
         </div>
         <div class="form-group col-md-6">
            <label>Last Name <span class="required">*</span></label>
            <input  type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'last_name') }}" name="last_name" required value="{{ old_set('last_name', NULL, $rec) }}">
            <div class="invalid-feedback">{{ showError($errors, 'last_name') }}</div>
         </div>


         </div>

         <div class="form-row">
            
            <div class="form-group col-md-6">
              <label>Email Address <span class="required">*</span></label>            
              <input  type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'email') }}" name="email" 
              value="{{ old_set('email', NULL , $rec) }}" required>
              <div class="invalid-feedback">{{ showError($errors, 'email') }}</div>
            </div>

            <div class="form-group col-md-6">
              <label>Password <span class="required">*</span></label>            
              <input  type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'password') }}" name="password" 
              value="{{ old_set('password', NULL , $rec) }}" required>
              <div class="invalid-feedback">{{ showError($errors, 'password') }}</div>
            </div>

         </div>

         

         <div class="form-group">
            <label>Company Name <span class="required">*</span></label>            
            <input  type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'company_name') }}" name="company_name" 
            value="{{ old_set('company_name', NULL , $rec) }}" required>
            <div class="invalid-feedback">{{ showError($errors, 'company_name') }}</div>
         </div>

         <div class="form-group">
            <label>Your Job Title <span class="required">*</span></label>            
            <input  type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'job_title') }}" name="job_title" 
            value="{{ old_set('job_title', NULL , $rec) }}" required>
            <div class="invalid-feedback">{{ showError($errors, 'job_title') }}</div>
         </div>

         <div class="form-row">
             <div class="form-group col-md-6">
              <label>Currency ISO Code (3 Characters) <span class="required">*</span></label>
              <input type="text" name="currency_iso_code" class="form-control form-control-sm {{ showErrorClass($errors, 'currency_iso_code') }}"  value="{{ old_set('currency_iso_code', NULL, $rec) }}" required>
              <div class="invalid-feedback">{{ showError($errors, 'currency_iso_code') }}</div>
           </div>

           <div class="form-group col-md-6">
              <label>Currency Symbol <span class="required">*</span></label>
              <input type="text" name="currency_symbol" class="form-control form-control-sm {{ showErrorClass($errors, 'currency_symbol') }}" value="{{ old_set('currency_symbol', NULL, $rec) }}" required>
              <div class="invalid-feedback">{{ showError($errors, 'currency_symbol') }}</div>
           </div>

         </div>
         


         <a class="btn btn-secondary  float-md-left" href="{{ route('installer_page') }}">Back</a>
     
         <input type="submit" name="submit" class="btn btn-primary float-md-right" value="Next" >
      </form>
   </div>
</div>

@endsection
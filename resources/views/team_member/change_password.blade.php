<?php
   $s =  (is_current_user($rec->id)) ? __('form.my_account') : __('form.team_member');
   ?>
@section('title',  $s . " : ". $rec->first_name)
<div class="main-content" style="margin-bottom: 10px !important">
   @include('team_member.partials.profile_menu')
</div>
<div class="row">
   <div class="col-md-3 col-sm-4">
      @include('team_member.partials.profile_photo')
   </div>
   <div class="col-sm-8 col-md-9">
      <div class="main-content">
         <div class="row">
            <div class="col-md-10">
               <form autocomplete="off" class="form-horizontal" method="post" action="{{ route('update_user_account') }}">
                  {{ csrf_field()  }}
                  {{ method_field('PATCH') }}
                  <!-- <div class="form-group row">
                     <label class="col-md-4 control-label">@lang('form.email')</label>
                     <div class="col-md-8">
                        <input type="email" class="form-control form-control-sm" name="email" value="{{ old_set('email', NULL,$rec) }}">
                     </div>
                  </div> -->
                
                  <div class="form-group row">
                     <label class="col-md-4 control-label">@lang('form.current_password')</label>
                     <div class="col-md-8">
                        <input type="password" class="form-control form-control-sm" name="current_password" value="">
                        
                        <div class="invalid-feedback d-block">@php if($errors->has('current_password')) { echo $errors->first('current_password') ; } @endphp</div>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-md-4 control-label">@lang('form.new_password')</label>
                     <div class="col-md-8">
                        <input type="password" class="form-control form-control-sm" name="password" value="">
                
                        <div class="invalid-feedback d-block">@php if($errors->has('password')) { echo $errors->first('password') ; } @endphp</div>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-md-4 control-label">@lang('form.retype_password')</label>
                     <div class="col-md-8">
                        <input type="password" class="form-control form-control-sm" name="password_confirmation" value="">
                        <div class="invalid-feedback d-block">@php if($errors->has('password_confirmation ')) { echo $errors->first('password_confirmation ') ; } @endphp</div>
                     </div>
                     
                  </div>
                  <div class="form-group row">
                     <div class="offset-md-4 col-md-8">
                        <button type="submit" class="btn btn-primary">@lang('form.submit')</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
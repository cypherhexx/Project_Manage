
<?php
$page_title = (isset($rec->id)) ? __('form.team_member') . " : ". $rec->first_name : __('form.add_new_team_member');
?>
@section('title', $page_title)

<div class="main-content">
    <div>
        <h5>{{ (isset($rec->id)) ? __('form.edit') ." " . __('form.team_members_profile') : __('form.add_new_team_member') }}
        </h5>
        <hr>
    </div>
    
<form autocomplete="off" method="post" action="{{ (isset($rec->id)) ? route( 'patch_team_member', $rec->id) : route('post_team_member') }}">
    {{ csrf_field()  }}
    @if(isset($rec->id))
    {{ method_field('PATCH') }}
    @endif
<div class="row">
   <div class="col-md-7">
     
      <div class="form-row">
         <div class="form-group col-md-5">
            <label>@lang('form.first_name') <span class="required">*</span></label>
            <input type="text" class="form-control form-control-sm @php if($errors->has('first_name')) { echo 'is-invalid'; } @endphp " name="first_name" value="{{ old_set('first_name', NULL, $rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('first_name')) { echo $errors->first('first_name') ; } @endphp</div>
         </div>
         <div class="form-group col-md-5">
            <label>@lang('form.last_name') <span class="required">*</span></label>
            <input type="text" class="form-control form-control-sm @php if($errors->has('last_name')) { echo 'is-invalid'; } @endphp " name="last_name" value="{{ old_set('last_name', NULL, $rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('last_name')) { echo $errors->first('last_name') ; } @endphp</div>
         </div>
         <div class="form-group col-md-2">
            <label for="default_language_id">@lang('form.gender')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("gender_id", $data['gender_id_list'], old_set("gender_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch '") ?>
            </div>
            <div class="invalid-feedback d-block">@php if($errors->has('gender_id')) { echo $errors->first('gender_id') ; } @endphp</div>
         </div>
      </div>
      <div class="form-row">
         <div class="form-group col-md-5">
            <label>@lang('form.job_title') <span class="required">*</span></label>
            <input type="text" class="form-control form-control-sm @php if($errors->has('job_title')) { echo 'is-invalid'; } @endphp " name="job_title" value="{{ old_set('job_title', NULL, $rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('job_title')) { echo $errors->first('job_title') ; } @endphp</div>
         </div>
         <div class="form-group col-md-4">
            <label>@lang('form.reporting_boss')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("reporting_boss", $data['reporting_boss_id_list'], old_set("reporting_boss", NULL, $rec), "class='form-control form-control-sm selectpicker' ") ?>
            </div>
            <div class="invalid-feedback d-block">@php if($errors->has('reporting_boss')) { echo $errors->first('reporting_boss') ; } @endphp</div>
         </div>
         <div class="form-group col-md-3">
            <label>@lang('form.joining_date')</label>
            <input type="text" class="form-control form-control-sm datepicker  @php if($errors->has('joining_date')) { echo 'is-invalid'; } @endphp" name="joining_date" value="{{ old_set('joining_date', NULL,$rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('joining_date')) { echo $errors->first('joining_date') ; } @endphp</div>
         </div>
      </div>
      <div class="form-row">
         <div class="form-group col-md-6">
            <label>@lang('form.teams')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("team_id[]", $data['team_id_list'], old_set("team_id", NULL, $rec), "class='form-control form-control-sm four-boot' multiple='multiple'") ?>
            </div>
            <div class="invalid-feedback d-block">@php if($errors->has('team_id')) { echo $errors->first('team_id') ; } @endphp</div>
         </div>
         <div class="form-group col-md-6">
            <label>@lang('form.date_of_birth')</label>
            <input type="text" class="form-control form-control-sm initially_empty_datepicker  @php if($errors->has('birth_date')) { echo 'is-invalid'; } @endphp" name="birth_date" value="{{ old_set('birth_date', NULL,$rec) }}">
            <div class="required">@php if($errors->has('birth_date')) { echo $errors->first('birth_date') ; } @endphp</div>
         </div>
      </div>
      <div class="form-row">
         <div class="form-group col-md-6">
            <label for="phone">@lang('form.salary')</label>
            <input type="text" class="form-control form-control-sm" id="salary" name="salary" value="{{ old_set('salary', NULL, $rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('salary')) { echo $errors->first('salary') ; } @endphp</div>
         </div>
         <div class="form-group col-md-6">
            <label for="phone">@lang('form.salary_term')</label>
            <input type="text" class="form-control form-control-sm" placeholder="@lang('form.salary_term_example')" name="salary_term" value="{{ old_set('salary_term', NULL, $rec) }}">
            <div class="invalid-feedback d-block">@php if($errors->has('salary_term')) { echo $errors->first('salary_term') ; } @endphp</div>
         </div>
      </div>
      <div class="form-group">
         <label>@lang('form.skills')</label>
         <div class="select2-wrapper">
            <?php echo form_dropdown("skill_id[]", $data['skill_id_list'], old_set("skill_id", NULL, $rec), "class='form-control form-control-sm four-boot' multiple='multiple'") ?>
         </div>
         <div class="invalid-feedback d-block">@php if($errors->has('skill_id')) { echo $errors->first('skill_id') ; } @endphp</div>
      </div>
       <div class="form-group">
         <label>@lang('form.unique_code') </label>
         <input type="text" class="form-control form-control-sm @php if($errors->has('code')) { echo 'is-invalid'; } @endphp " 
            name="code" placeholder="@lang('form.member_code_example')" value="{{ old_set('code', NULL, $rec) }}">
         <div class="invalid-feedback d-block">@php if($errors->has('code')) { echo $errors->first('code') ; } @endphp</div>
      </div>
   </div>
   <div class="col-md-5">
    <div class="form-group">
         <label>@lang('form.departments')</label>
         <div class="select2-wrapper">
            <?php echo form_dropdown("department_id[]", $data['department_id_list'], old_set("department_id", NULL, $rec), "class='form-control form-control-sm four-boot' multiple='multiple'") ?>
         </div>
         <div class="invalid-feedback d-block">@php if($errors->has('department')) { echo $errors->first('department') ; } @endphp</div>
      </div>

      <div class="form-row">
         <div class="form-group col-md-6">
            <label>@lang('form.user_role')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("role_id", $data['user_roles_id_list'], old_set("role_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch' ") ?>
            </div>
            <div class="invalid-feedback d-block">@php if($errors->has('role_id')) { echo $errors->first('role_id') ; } @endphp</div>
         </div>
         <div class="form-check form-check-inline">
            <div class="custom-control custom-checkbox">
               <input type="checkbox" class="custom-control-input" id="customCheck1" name="is_administrator" value="1" {{ (old_set('is_administrator', NULL, $rec)) ? 'checked' : '' }}>
               <label class="custom-control-label" for="customCheck1">@lang('form.is_administrator')</label>
            </div>
         </div>
      </div>
      <div class="form-group">
         <label>@lang('form.email') <span class="required">*</span></label>
         <input type="text" class="form-control form-control-sm @php if($errors->has('email')) { echo 'is-invalid'; } @endphp" id="email" name="email" value="{{ old_set('email', NULL, $rec) }}">
         <div class="invalid-feedback d-block">@php if($errors->has('email')) { echo $errors->first('email') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label>@lang('form.password')</label>
         <div class="input-group input-group-sm">
            <input type="password" class="form-control form-control-sm @php if($errors->has('password')) { echo 'is-invalid'; } @endphp" name="password">
            <div class="input-group-append">
               <span class="input-group-text"><a  href="#" class="password_display"><i class="fas fa-eye"></i></a></span>
               <span class="input-group-text"><a href="#" class="password_generate"><i class="fas fa-sync"></i></a></span>
            </div>
         </div>
         <small class="form-text text-muted">{{ (isset($rec->id)) ? __('form.team_member_password_note_edit') : __('form.team_member_password_note_add') }}</small>
         <div class="invalid-feedback d-block">@php if($errors->has('password')) { echo $errors->first('password') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="phone">@lang('form.phone')</label>
         <input type="text" class="form-control form-control-sm" id="phone" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
         <div class="invalid-feedback d-block">@php if($errors->has('phone')) { echo $errors->first('phone') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="address">@lang('form.address')</label>
         <textarea id="address" name="address" class="form-control form-control-sm " >{{ old_set('address', NULL, $rec) }}</textarea>
         <div class="invalid-feedback d-block">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
      </div>
   </div>
</div>
<?php bottom_toolbar(__('form.submit'))?>
</form>
</div>

@section('innerPageJS')
<script>
    $(function () {


        function generatePassword() {
            var length = 8,
                charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                retVal = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * n));
            }
            return retVal;
        }

        passwordField = $('input[name=password]');

        $('.password_display').click(function (e) {
            e.preventDefault();

            (passwordField.attr('type') == 'password') ? passwordField.attr('type', 'text') : passwordField.attr('type', 'password');

        });

        $('.password_generate').click(function (e) {
            e.preventDefault();

            passwordField.val(generatePassword);

        });
    });
</script>    
@endsection
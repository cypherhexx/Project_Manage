<div class="row">
   <div class="col-md-6">
      
      <div class="form-group ">
         <label for="name">@lang('form.name') <span class="required">*</span></label>
         <input type="text" class="form-control form-control-sm @php if($errors->has('name')) { echo 'is-invalid'; } @endphp " name="name" value="{{ old_set('name', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
      </div>

      <div class="form-row">
         <div class="form-group col-md-6">
            <label for="phone">@lang('form.phone')</label>
            <input type="text" class="form-control form-control-sm" id="phone" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
            <div class="invalid-feedback">@php if($errors->has('phone')) { echo $errors->first('phone') ; } @endphp</div>
         </div>
         <div class="form-group col-md-6">
            <label for="website">@lang('form.website')</label>
            <input type="text" class="form-control form-control-sm " id="website" name="website" value="{{ old_set('website', NULL, $rec) }}">
            <div class="invalid-feedback">@php if($errors->has('website')) { echo $errors->first('website') ; } @endphp</div>
         </div>
      </div>



   </div>
   <div class="col-md-6">
      @include('vendor.partials.contact_person_form') 
   </div>
</div>
<br>
<div class="row">
   <div class="col-md-12">
      <nav>
         <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">@lang('form.address')</a>
            <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">@lang('form.notes')</a>
         </div>
      </nav>
      <div class="tab-content" id="nav-tabContent">
         <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            @include('vendor.partials.address')
         </div>
         <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
            <div class="form-group">
               <textarea id="notes" name="notes" placeholder="{{ __('form.notes') }}" class="form-control form-control-sm " >{{ old_set('notes', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">@php if($errors->has('notes')) { echo $errors->first('notes') ; } @endphp</div>
            </div>
         </div>
      </div>
   </div>
</div>
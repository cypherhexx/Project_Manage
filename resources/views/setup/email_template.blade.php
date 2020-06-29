@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.email'))
@section('setting_page')

<style type="text/css">
  fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
}

    legend.scheduler-border {
        font-size: 12px !important;
        font-weight: bold !important;
        text-align: left !important;
        width:auto;
        padding:0 10px;
        border-bottom:none;
    }
</style>

<div class="main-content">
   <h5>@lang('form.email_template')</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal"enctype="multipart/form-data" action="{{ route('patch_settings_email_template') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}
            <fieldset class="scheduler-border">
               <legend class="scheduler-border">{{ $data['title'] }}</legend>
               <div class="row">
                  <div class="col-md-9"><input type="text" name="subject" class="form-control form-control-sm {{ showErrorClass($errors , 'subject') }}" value="{{ old_set('subject', NULL, $rec) }}" placeholder="@lang('form.subject')"></div>
                  <div class="col-md-3">
                     <div class="dropdown short_code float-md-right">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @lang('form.available_short_codes')
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                           @foreach($data['short_codes'] as $short_code)
                           <a class="dropdown-item" href="#" data-code="{{ $short_code }}">{{ $short_code }}</a>
                           @endforeach                
                        </div>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="invalid-feedback d-block">{{ showError($errors, 'subject') }}</div>
                  </div>
               </div>
               <div style="clear: both;"></div>
               <div class="row">
                 <div class="col-md-6">
                   <input type="text" name="from_name" class="form-control form-control-sm {{ showErrorClass($errors , 'from_name') }}" value="{{ old_set('from_name', NULL, $rec) }}" placeholder="@lang('form.from_name')">
                 </div>
                 <div class="col-md-6">
                   <input type="email" name="from_email_address" class="form-control form-control-sm {{ showErrorClass($errors , 'from_email_address') }}" value="{{ old_set('from_email_address', NULL, $rec) }}" placeholder="@lang('form.from_email_address')">
                 </div>
               </div>
               <input type="hidden" name="name" value="{{ $data['name'] }}">
               <div class="form-group" style="margin-top: 10px;">
                  <textarea  rows="10" class="form-control form-control-sm {{ showErrorClass($errors , 'template') }}" name="template">{{ old_set('template', NULL, $rec) }}</textarea>
                  <div class="invalid-feedback">{{ showError($errors, 'template') }}</div>
               </div>
            </fieldset>
            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
         @include('setup.email_template_list')
      </div>
   </div>
</div>
@endsection
@section('onPageJs')
<script>
   $(function() {
   
      $('.short_code .dropdown-item').click(function(e){

         e.preventDefault();

          var textarea     = $('textarea[name=template]');

          var $short_code  = $(this).data('code');

          var cursorPos    = textarea.prop('selectionStart');
          var v            = textarea.val();
          var textBefore   = v.substring(0,  cursorPos);
          var textAfter    = v.substring(cursorPos, v.length);

          textarea.val(textBefore + $short_code + textAfter);



      });
   
   });
   
   
</script>
@endsection
@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.proposal'))
@section('setting_page')
<form role="form" class="form-horizontal" action="" enctype="multipart/form-data" action="{{ route('patch_settings_proposal') }}" method="post" autocomplete="off" >
   {{ csrf_field()  }}
   {{ method_field('PATCH') }}
   <div class="main-content">
      <h5>@lang('form.proposal')</h5>
      <hr>
      <div class="row">
         <div class="col-md-6">
            <h6>@lang('form.template')</h6>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <div class="select2-wrapper">
                  <?php echo form_dropdown("short_codes_proposal_template", $data['short_codes_proposal_template'], NULL, "class='form-control form-control-sm selectpicker '") ?>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group">
         <textarea id="template_proposal" rows="8" class="form-control form-control-sm {{ showErrorClass($errors ,'template_proposal') }}" name="template_proposal">{{ old_set('template_proposal', NULL, $rec) }}</textarea>
         <div class="invalid-feedback">{{ showError($errors, 'template_proposal') }}</div>
      </div>
   </div>
    <?php echo bottom_toolbar(); ?>
</form>
@endsection
@section('onPageJs')
<script>
   $(function() {
    
    <?php  echo tinyMceJsSriptWithFileUploader('#template_proposal'); ?>



      $('select[name=short_codes_proposal_template]').change(function(e){

         e.preventDefault();

          var $short_code     = $(this).val();  

          if($short_code)
          {
            var textarea      = $('#template_proposal');      

            tinyMCE.triggerSave();           
            var content = tinymce.activeEditor.getContent();           

            tinymce.activeEditor.setContent(content + " " + $short_code);

            
            $('select[name=short_codes_proposal_template]').val(null).trigger("change");
          }
      });



      

      

   
   });
   
   
</script>
@endsection
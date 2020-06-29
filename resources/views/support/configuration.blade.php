@extends('setup.index')
@section('title', __('form.settings') . " : " . __('form.support') . " " .__('form.configuration') )
@section('setting_page')
<div class="main-content">
   
   <h5>{{ __('form.support') . " " .__('form.configuration')  }}</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal" enctype="multipart/form-data" action="{{ route('patch_support_configuration') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}

            <div class="custom-control custom-checkbox">
              <input {{ (old_set('disable_support', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" 
              name="disable_support" value="1" id="disable_support">
              <label class="custom-control-label" for="disable_support">@lang('form.disable_support')</label>
            </div>

            <div class="custom-control custom-checkbox">
              <input {{ (old_set('disable_knowledge_base', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" 
              name="disable_knowledge_base" value="1" id="disable_knowledge_base">
              <label class="custom-control-label" for="disable_knowledge_base">@lang('form.disable_knowledge_base')</label>
            </div>

            <div class="custom-control custom-checkbox">
              <input {{ (old_set('knowledge_base_is_private', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" 
              name="knowledge_base_is_private" value="1" id="knowledge_base_is_private">
              <label class="custom-control-label" for="knowledge_base_is_private">@lang('form.make_knowledge_base_available_for_logged_in_users_only')</label>
            </div>


      
            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
         
      </div>
   </div>
</div>
@endsection

@section('onPageJs')
<script>
   $(function() {
   
    
   
   });
   
   
</script>
@endsection
@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.edit') . " ". __('form.article') :   __('form.add_new_article')   )
@section('content')
<form method="post" action="{{ (isset($rec->id)) ? route( 'patch_knowledge_base_article', $rec->id) : route('post_knowledge_base_article') }}">
   {{ csrf_field()  }}
   @if(isset($rec->id))
   {{ method_field('PATCH') }}
   @endif
   <div class="row">
      <div class="col-md-8">
         <div class="main-content">
            <div class="row">
               <div class="col-md-6">
                  <h5>{{ (isset($rec->id)) ? __('form.edit') . " ". __('form.article') :   __('form.add_new_article') }}</h5>
               </div>
               <div class="col-md-6">
                  <div class="custom-control custom-checkbox float-md-right">
                     <input {{ (!isset($rec->id) || old_set('create_slug_from_subject', NULL,$rec) )  ? 'checked' : '' }}  type="checkbox" class="custom-control-input" id="create_slug_from_subject" name="create_slug_from_subject" value="1">
                     <label class="custom-control-label" for="create_slug_from_subject">@lang('form.create_slug_from_subject')</label>
                  </div>
               </div>
            </div>
            <hr>
            <div class="form-group ">
               <label>@lang('form.subject') <span class="required">*</span> </label>
               <input type="text" class="form-control form-control-sm  {{ showErrorClass($errors, 'subject') }}" name="subject" value="{{ old_set('subject', NULL,$rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'subject') }}</div>
            </div>
            <div class="form-group">
               <label>@lang('form.url_slug') <span class="required">*</span></label>
               <div class="row">
                  <div class="col-md-6">
                     <small class="form-text text-muted">{{ str_replace('url_slug', '', route('knowledge_base_article_customer_view', 'url_slug') ) }}</small>
                  </div>
                  <div class="col-md-6">
                     <input style="width: 100%;" type="text" class="{{ showErrorClass($errors, 'slug') }}" name="slug" value="{{ old_set('slug', NULL, $rec) }}">
                  </div>
               </div>
               <div class="invalid-feedback d-block">{{ showError($errors, 'slug') }}</div>
            </div>
            <div class="form-group">
               <label>@lang('form.details') <span class="required">*</span></label>
               <textarea class="form-control form-control-sm" id="details" name="details" rows="2">{{ old_set('details', NULL,$rec) }}</textarea>
               <div class="invalid-feedback">{{ showError($errors, 'details') }}</div>
            </div>
            <?php echo bottom_toolbar(); ?>
         </div>
      </div>
      <div class="col-md-4">
         <div class="main-content">
            <div class="form-group">
               <label>@lang('form.group') <span class="required">*</span></label>
               <?php
                  echo form_dropdown('article_group_id', $data['article_group_id_list'] , old_set('article_group_id', NULL,$rec), "class='form-control form-control-sm  selectpicker'");
                  ?>
            </div>
            <div class="custom-control custom-checkbox">
               <input {{ (old_set('is_internal', NULL,$rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="is_internal" name="is_internal" value="1">
               <label class="custom-control-label" for="is_internal">@lang('form.internal')</label>
            </div>
            <div class="custom-control custom-checkbox">
               <input {{ (old_set('is_disabled', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="is_disabled" name="is_disabled" value="1">
               <label class="custom-control-label" for="is_disabled">@lang('form.disabled')</label>
            </div>
         </div>
      </div>
   </div>
</form>
@endsection
@section('onPageJs')

<script type="text/javascript">
    <?php tinyMceJsSript('#details'); ?>


    $('input[name=subject]').keyup(function(e){

        e.preventDefault();
        var text = $(this).val();
        var slug_input = $('input[name=slug]');
    
        if($('#create_slug_from_subject').is(':checked') )
        {
            var slug = text.toLowerCase()
                .replace(/ /g,'-')
                .replace(/[^\w-]+/g,'')
                ;

                slug_input.val(slug);


        }
    });

</script>
@endsection
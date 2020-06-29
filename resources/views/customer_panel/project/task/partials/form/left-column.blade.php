


<div class="form-group">
   <label>@lang('form.title') <span class="required">*</span> </label>
   <input type="text" class="form-control form-control-sm  @php if($errors->has('title')) { echo 'is-invalid'; } @endphp" name="title" value="{{ old_set('title', NULL,$rec) }}">
   <div class="invalid-feedback">@php if($errors->has('title')) { echo $errors->first('title') ; } @endphp</div>
</div>
<div class="form-group">
   <label>@lang('form.description') </label>
   <textarea class="form-control" id="description" name="description" rows="6">{{ old_set('description', NULL,$rec) }}</textarea>
</div>
<div>
   <ul class="list-group" id="list_of_attachments">
      @if(isset($rec->id))
      <?php $attachments = $rec->attachments()->get(); ?>
      @if(count($attachments) > 0)
      @foreach ($attachments as $key =>$attachment) 
      <li class="list-group-item"> {{ $attachment->display_name }}
         <a href="{{ route('attachment_download_link', Crypt::encryptString($attachment->name) ) }}" data-key="{{ $key }}" class="btn btn-danger btn-sm remove_attachment"> <i class="far fa-trash-alt"></i> </a> 
      </li>
      @endforeach
      @endif
      @endif
   </ul>
   <br>
   <small class="form-text text-muted text-center" id="uploading_file_loading_text"></small>
</div>
<input type="file" name="attachment" id="attachment" style="display:none;"/>    

<a href="#" class="upload_link">
   <div class="upload-area">
      <div>@lang('form.attach_a_file')</div>
      <div style="font-size: 11px; color: black;">@lang('form.max_size_one_mb')</div>
   </div>
</a>
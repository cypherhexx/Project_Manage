<div class="card">
 <?php $photo_url = ($rec->photo) ? asset(Storage::url($rec->photo)) : asset('images/user-placeholder.jpg') ; ?>
  <img class="card-img-top img-fluid member-avatar" style="padding: 20px;" src="{{ $photo_url }}" alt="{{ $rec->first_name . " " . $rec->last_name }}">
  @if(auth()->user()->id == $rec->id)
  	<a href="#" class="upload_photo" style="font-size: 13px; margin-top: -20px; text-align: center;">@lang('form.change_photo')</a>
    <span class="uploading_spinner text-center" style="display: none;">@lang('form.uploading') ..</span>
  @endif
  <div class="card-body" >
    <div style="font-size: 14px;" class="card-title text-center">{{ $rec->first_name . " " . $rec->last_name }}</div>
    
    <p class="card-text text-center">{{ $rec->job_title }}</p>

      @if(check_perm('team_members_edit'))
      <div class="card-text text-center">
        <a class="text-center" href="{{ route('edit_team_member_page', $rec->id) }}">
          <i class="far fa-edit"></i> {{ __('form.edit') . " ". __('form.profile') }}
        </a>
      </div>
     @endif

  </div>
</div>

<div style="display: none;">
<form>
	<input type="file" id='file' name="file" >
</form>
</div>
<form action="{{ route('lead_add_note', $rec->id) }}" method="POST" autocomplete="off">
     {{ csrf_field()  }}
   
   
<div class="form-group">
  <textarea name="details" class="form-control form-control-sm">{{ old_set('details', NULL,$rec) }}</textarea>
  <div class="invalid-feedback d-block">{{ showError($errors, 'details') }}</div>
</div>
<div class="form-group">
  <input type="submit" class="btn btn-primary float-md-right" name="submit" value="@lang('form.post')">
 <div class="clearfix"></div>
</div>	
</form>

@if(count($rec->notes) > 0)
@foreach($rec->notes as $note)
	<div class="alert alert-secondary note_thread_{{ $note->id }}" role="alert" style="font-size: 12px;">

		<div class="row">
			<div class="col-md-6">
					<div style="font-size: 13px;">
						<?php echo anchor_link(__('form.note_by') . " ". $note->person_created->first_name . " ". $note->person_created->last_name, route('member_profile', $note->user_id )) ?>
					</div>

			</div>
			<div class="col-md-6">
				
				<div class="float-md-right">
					@if($note->user_id == auth()->user()->id)
					<a class="editNote btn btn-light btn-sm" data-id="{{ $note->id }}" href="#"><i class="far fa-edit"></i></a> 
        			<a class="delete_note btn btn-danger btn-sm" data-id="{{ $note->id }}" href="#">
        				<i class="far fa-trash-alt"></i></a>
        			@endif		
				</div>	
			</div>

		</div>	


		
		<div class="note_details_{{ $note->id }}" style="font-size: 13px;">
			{{ $note->body }}
		</div>

		<div class="inlineEdit_{{ $note->id }}" style="display: none;">		

			<div class="form-group">
			  <textarea name="details" class="form-control form-control-sm ">{{ $note->body }}</textarea>
			
			</div>
			<div class="form-group">
			  <input type="submit" class="btn btn-light float-md-right saveNote" data-id="{{ $note->id }}" name="submit" value="@lang('form.submit')">
			 <div class="clearfix"></div>
			</div>	

		</div>
		<br>
		<div>@lang('form.note_added') {{ $note->created_at->diffForHumans() }}</div>
	</div>
@endforeach
@endif

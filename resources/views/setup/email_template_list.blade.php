@if(isset($template_list) && count($template_list) > 0)
<div class="accordion" id="accordionExample">
  @foreach($template_list as $key=>$row)	
  <div class="card">
    <div class="card-header" id="heading_{{ $key }}">
      <h5 class="mb-0">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse_{{ $key }}" aria-expanded="true" 
        aria-controls="collapse_{{ $key }}">
          {{ $row['component_name'] }}
        </button>
      </h5>
    </div>
    <div id="collapse_{{ $key }}" class="collapse show" aria-labelledby="heading_{{ $key }}" data-parent="#accordionExample">
	     <ul class="list-group">
		  	@if(count($row['templates']) > 0)
		  		@foreach($row['templates'] as $template)
		  			<li class="list-group-item"><a href="{{ $template['route'] }}">{{ $template['title'] }}</a></li>
		  		@endforeach
		  	@endif
		</ul>
    </div>
  </div>  
@endforeach
</div>
@endif

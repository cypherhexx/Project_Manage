@extends('customer_panel.knowledge_base.template')
@section('title', $category->name . " : ". __('form.knowledge_base'))
@section('knowledge_base_content')
<div class="main-content">
	
	<h2>{{ $category->name }}</h2>

	<hr>

	<div class="row">
		<div class="col-md-8">
			@include('customer_panel.knowledge_base.loop.child_categories', ['children' => $category->children ] )
			@include('customer_panel.knowledge_base.loop.articles_list_with_excerpt', ['articles' => $category->articles  ] )
		</div>
	</div>
</div>
@endsection


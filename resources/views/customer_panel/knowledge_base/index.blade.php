@extends('customer_panel.knowledge_base.template')
@section('title', __('form.knowledge_base'))
@section('knowledge_base_content')
<div style="margin-bottom: 15%;">
   @if(count($article_groups) > 0)
@foreach($article_groups->chunk(3) as $chunk_of_article_group)
<div class="card-deck" style="margin-bottom: 20px;">
   @foreach($chunk_of_article_group as $article_group)
   <div class="card">
      <div class="card-body">
       
        <div style="background-color: #eee;"><h5 class="card-title text-center"><a href="{{ route('knowledge_base_category_customer_view', $article_group->slug) }}">
           {{ $article_group->name }}
            </a> ({{ $article_group->articles_count + $article_group->children_count}}) </h5></div>
           
            <p>{{ $article_group->description }}</p>
         <ul> 
            <li>
            </li>
            @include('customer_panel.knowledge_base.loop.child_categories', ['children' => $article_group->children()->get() ] )
         </ul>     
      </div>
   </div>
   @endforeach
</div>
@endforeach
@endif

</div>

@endsection
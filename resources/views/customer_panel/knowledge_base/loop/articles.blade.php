@if(count($articles) > 0)
	
@foreach($articles as $article)
	<div style="line-height: 32px;"> <a href="{{ route('knowledge_base_article_customer_view', $article->slug) }}">{{ $article->subject }}</a></div>
@endforeach
	
@endif
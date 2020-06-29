@if(count($articles) > 0)
<ul>
   @foreach($articles as $article)
   <li>
      <a style="font-weight: bold;" href="{{ route('knowledge_base_article_customer_view', $article->slug) }}">{{ $article->subject }}</a>
      <p class="form-text text-muted" style="margin: 10px 0;"><?php echo str_limit(strip_tags($article->details), 150); ?></p>
   </li>
   <hr>
   @endforeach    
</ul>
@endif
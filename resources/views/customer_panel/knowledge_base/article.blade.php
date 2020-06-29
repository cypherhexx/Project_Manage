@extends('customer_panel.knowledge_base.template')
@section('title', $article->subject)
@section('knowledge_base_content')
<div class="main-content">
   <?php knowledge_base_breadcrumb(['article' => $article ]); ?>
   <div class="row">
      <div class="col-md-8">
         <h1 style="line-height: 40px; font-size: 28px;">{{ $article->subject }}</h1>
         <hr>
         <div><?php echo $article->details; ?></div>
         <small class="form-text text-muted">@lang('form.posted') {{ $article->created_at->diffForHumans() }}</small>
      </div>
      <div class="col-md-4">
         <h4>@lang('form.articles_in_this_section')</h4>
         <hr>
         @include('customer_panel.knowledge_base.loop.articles_list_with_excerpt', ['articles' => $data['related_articles'] ])
         
      </div>
   </div>
</div>
@endsection




@section('innerPageJs')

   
@endsection
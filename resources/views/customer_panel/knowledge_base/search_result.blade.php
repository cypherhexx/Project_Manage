@extends('customer_panel.knowledge_base.template')
@section('title', __('form.search_result'))
@section('knowledge_base_content')
<div class="main-content">
   <div class="row">
      <div class="col-md-8">
         <h1 style="line-height: 40px; font-size: 28px;">{{ __('form.search_result') }} ( {{ $result->total() }} )</h1>
         <hr>
         @include('customer_panel.knowledge_base.loop.articles_list_with_excerpt', ['articles' => $result ])

         {{ $result->links() }}
         @if($result->total() == 0)
            @lang('form.no_record_found')
         @endif
      </div>
      <div class="col-md-4">
  
      </div>
   </div>
</div>
@endsection




@section('innerPageJs')

   
@endsection
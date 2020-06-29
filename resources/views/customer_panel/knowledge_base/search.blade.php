<div class="main-content" style="margin-bottom: 20px;">

    
<form action="{{ route('knowledge_base_search_customer_view') }}" method="GET" autocomplete="off">
   
   <div class="row">
      <div class="offset-md-2 col-md-8">
         <h1 class="text-center page-title">@lang('form.knowledge_base_search_title')</h1>
         <br>
         <div class="input-group mb-3">
         
            <input type="text" name="q" spellcheck="false" class="form-control form-control-lg" placeholder="@lang('form.have_a_question')" aria-label="Recipient's username" aria-describedby="button-addon2">
            <div class="input-group-append">
               <button class="btn btn-secondary btn-lg" type="submit" id="button-addon2">@lang('form.search')</button>
            </div>
         </div>

        <!--  <input type="text" id="query" placeholder="@lang('form.have_a_question')" autocomplete="off" class="form-control search-hero__query search-query st-default-search-input ds-input" name="q" spellcheck="false" style="position: relative; vertical-align: top;"> -->

      </div>
   </div>
</form>
</div>
<br>


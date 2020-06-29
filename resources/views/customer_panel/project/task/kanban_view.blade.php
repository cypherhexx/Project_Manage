@extends('layouts.main')
@section('title', __('form.tasks'))
@section('content')
  
   <div class="main-content">

   <div class="row">
       <div class="col-md-6">
         
         <a class="btn btn-secondary btn-sm" href="{{ route('task_list') }}" role="button">
            @lang('form.switch_to_list_view')</a>

        </div>        

   </div>
   <hr>        
    @include('task.partials.kanban_layout', ['task_list' => $rec])
</div>
@endsection

@section('onPageJs')

  @yield('innerChildPageJs')
      
       
   
@endsection
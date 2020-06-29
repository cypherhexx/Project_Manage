@extends('installer.template')
@section('title', ($data['status'] == 1) ? 'Installation Complete' : 'Installation Failed')
@section('content')

<div class="card mx-auto" style="width: 28rem; margin-bottom: 10%;">
   <div class="card-body">
      <h4 class="card-title"><i class="fas {{ $data['icon'] }}"></i> {{ $data['title'] }}</h4>
      <hr>
         <?php echo $data['msg']; ?>   
        <br>
        @if($data['status'] == 1)
            <a href="{{ route('dashboard') }}"class="btn btn-primary">Go to Login page</a>
        @endif
   </div>
</div>
@endsection      
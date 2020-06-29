@extends('layouts.main')
@section('title', __('form.filemanager'))
@section('content')


<form class="form-inline">
  <?php 
$default_value = (app('request')->input('type')) ?? 'Files' ;
$data =  ['Images' => __('form.images'), 'Files' => __('form.files') ];
echo form_dropdown("type", $data, $default_value , "class='custom-select my-1 mr-sm-3'") 
?>
</form>



<iframe src="{{ route('get_laravel_file_manager') }}?field_name=mceu_49-inp&type={{ $default_value }}" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
@endsection
@section('onPageJs')
    <script>
        $(function() {

        $('.custom-select').change(function(){
        	var type = $(this).val();

        	window.location.href = "{{ route('file_manager') }}?type=" +type;
        });
             
    });

</script>


@endsection
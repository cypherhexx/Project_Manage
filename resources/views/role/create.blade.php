@extends('setup.index')
@section('title', (isset($rec->id)) ?  __('form.edit_role')  : __('form.add_new_role'))
@section('setting_page')

<div class="main-content">
<h5>{{ (isset($rec->id)) ?  __('form.edit_role')  : __('form.add_new_role') }}</h5>
<hr>
<form method="post" action="{{ (isset($rec->id)) ? route( 'patch_role', $rec->id) : route('post_role') }}">

    {{ csrf_field()  }}
    @if(isset($rec->id))
        {{ method_field('PATCH') }}
    @endif
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>@lang('form.role_name') <span class="required">*</span> </label>
            <input type="text" class="form-control form-control-sm  @php if($errors->has('name')) { echo 'is-invalid'; } @endphp" name="name" value="{{ old_set('name', NULL,$rec) }}">
            <div class="invalid-feedback">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
        </div>

    </div>

     <table class="table table-sm table-bordered">
<thead>
    <tr>
        <th class="bold">@lang('form.permission')</th>
        <th class="text-center bold">@lang('form.view')</th>
        <th class="text-center bold">@lang('form.view_own')</th>
        <th class="text-center bold">@lang('form.create')</th>
        <th class="text-center bold">@lang('form.edit')</th>
        <th class="text-center text-danger bold">@lang('form.delete')</th>
    </tr>
</thead>
<tbody>
    @foreach($data['permissions_checkboxes'] as $list)
        <tr>
        <td>{{ $list['name'] }}</td>
        <td class="text-center"><?php echo $list['view']; ?></td>
        <td class="text-center"><?php echo $list['view_own']; ?></td>
        <td class="text-center"><?php echo $list['create']; ?></td>
        <td class="text-center"><?php echo $list['edit']; ?></td>
        <td class="text-center"><?php echo $list['delete']; ?></td>
    </tr>
    @endforeach
</tbody>

     

    <?php echo bottom_toolbar(__('form.submit'))?>

</form>
</div>
@endsection
@section('onPageJs')
@endsection
@extends('layouts.main')
@section('content')
    <h4>@lang('form.lead_status')</h4>
    <hr>
<div>
    <form method="post" action='{{ (isset($rec->id)) ? route( 'patch_lead_status', $rec->id) : route('post_lead_status') }}'>

               {{ csrf_field()  }}
               @if(isset($rec->id))
                  {{ method_field('PATCH') }}
               @endif

        <div class="form-group">
            <label for="name">@lang('form.name')</label>
            <input type="text" class="form-control @php if($errors->has('name')) { echo 'is-invalid'; } @endphp" id="name" name="name" value="{{ old_set('name', NULL, $rec) }}">
            <div class="form-control-feedback">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
        </div>
            <button type="submit" class="btn btn-primary">@lang('form.submit')</button>
    </form>
</div>
@endsection
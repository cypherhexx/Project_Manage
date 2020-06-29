
<div class="main-content">
    
    <form method="post" action='{{ (isset($rec->id)) ? route( 'patch_vendor', $rec->id) : route('post_vendor') }}'>
        {{ csrf_field()  }}
        @if(isset($rec->id))
            {{ method_field('PATCH') }}
            <input type="hidden" name="id" value="{{ $rec->id }}">
        @endif

        @include('vendor.partials.details')
      

        <?php bottom_toolbar(__('form.submit'))?>
    </form>
</div>




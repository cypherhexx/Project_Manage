<div class="flash-message">
    @foreach (['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light'] as $msg)
        @if(Session::has($msg))
            <div class="alert alert-{{ $msg }}" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div>{{ Session::get($msg) }}</div>
            </div>
        @endif
    @endforeach
</div>

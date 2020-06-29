<style type="text/css">
	.ticketstaffnotes {
    margin: 0 0 5px 0;
    padding: 0;
    background-color: #ffffe6;
    border: 1px dashed #dacd83;
}
</style>
<div class="main-content" style="margin-bottom: 10px !important">

	@include('support.partials.notes')

    <form action="{{ route('ticket_add_reply', $rec->id) }}" method="POST" autocomplete="off">
         {{ csrf_field()  }}
        <input type="hidden" name="id" value="{{ $rec->id }}">
        @include('support.partials.post_comment')
        <?php bottom_toolbar(__('form.add_response')); ?>
    </form>
</div> 

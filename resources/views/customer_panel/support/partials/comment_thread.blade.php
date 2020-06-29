<style type="text/css">
   .thread-member{
   background-color: #fff !important;
   }
   .thread-customer{
   background-color: #ffffe6!important;
   }
</style>
<?php $c = $rec->comments()->paginate(TICKET_THREAD_PAGE_LENGTH) ?>
@if(count($c) > 0)
@foreach($c as $comment)
<div id="thread_{{ $comment->id }}" class="{{ ($comment->user_type == 1) ? 'thread-member' : 'thread-customer' }}" style="padding: 20px; border: 1px solid #dce1ef; border-radius: 4px; font-size: 14px; margin-bottom: 40px;">
   <div class="row">
      <div class="col-md-3">
         <p><?php echo $comment->user->name; ?></p>
         <div>{{ ($comment->user_type == 1) ? __('form.staff') : ''   }}</div>
         <br>
      </div>
      <div class="col-md-9" style="border-left: 1px solid #eee;">
         <?php echo nl2br($comment->body); ?>                       
         @if(count($comment->attachments) > 0)
         <hr>
         <div>@lang('form.attachments')</div>
         @foreach($comment->attachments as $attachment)
         <div style="font-size: 13px;">
            <a target="_blank" href="{{ gen_url_for_attachment_download($attachment->name) }}"> {{ $attachment->display_name }} </a>
         </div>
         @endforeach
         @endif
      </div>
   </div>
   <br><br>
   <div style="padding: 10px; background-color: #eee; margin: -20px; font-size: 13px;">
      @lang('form.posted') : {{ $comment->created_at->format('d-M-Y h:i:s a') }}
   </div>
</div>
@endforeach
{{ $c->links('layouts.pagination_bootstrap-4') }}
@endif
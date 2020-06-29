@if(check_customer_project_permission($rec->settings->permissions, 'comment_on_tasks'))
<hr> 
<form id="commentForm" action="{{ route('cp_post_task_comment', $task->id) }}" method="POST">

        {{ csrf_field()  }}
        <div class="form-group">
            <div style="position:relative;">
                <label>@lang('form.comment') </label>
                <textarea class="form-control comment" id="comment" name="comment" rows="2"></textarea>
                
            </div>      
        
        </div>
        
        
        <div>
            <ul class="list-group" id="list_of_attachments"></ul>
        </div>
        <br>
        <div class="form-group">
            @if(check_customer_project_permission($rec->settings->permissions, 'upload_on_tasks'))
                <input type="file" name="attachment" id="attachment" data-form-id="#commentForm" data-short-code-input-id="#comment"style="display:none;"/> 
                <a href="#" class="btn btn-light upload_link">                    
                    <i class="fas fa-paperclip "></i> @lang('form.upload_attachment')
                </a>
             @endif
            <input type="submit" class="btn btn-primary float-md-right" value="@lang('form.post')" />
        </div>
       <div class="clearfix"></div>
    </form>
@endif
        
@if(check_customer_project_permission($rec->settings->permissions, 'view_task_comments'))
    <div>
         <hr>
         <?php $number_of_comments = count($task->comments); ?>
        <h5>{{ $number_of_comments }} {{ ($number_of_comments > 1) ? __('form.comments') : __('form.comment')}}
            <?php 
                $comment = app('request')->input('comment') ; 
                if($comment)
                {
                    echo anchor_link(__('form.show_all_comments'), route('cp_show_task_page', [$rec->id, $task->id]));
                }
            ?>     
        </h5>
        <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead style="display: none;">
        <tr>
            <th></th>
            
           

        </tr>
        </thead>
    </table>
       
    </div>
@endif

    
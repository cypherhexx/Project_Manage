@if(count($ticket->comments) > 0)
<div>
	
<div style="color: #b5b5b5; line-height: 18px; font-size: 12px;">##- Please type your reply above this line -##</div>
		
<?php $comments = $ticket->comments()->orderBy('id', 'DESC')->get();?>	
@foreach($comments as $comment)

<?php $photo_url = ($comment->user_type == USER_TYPE_TEAM_MEMBER) ? get_company_logo() : asset('images/user-placeholder.jpg') ; ?>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
   <tbody>
      <tr>
         <td width="100%" style="padding:15px 0;border-top:1px dotted #c5c5c5">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed">
               <tbody>
                  <tr>
                     <td valign="top" style="padding:0 15px 0 15px;width:40px">              
                     	<img width="40" height="40" alt="" style="height:auto;line-height:100%;outline:none;text-decoration:none;border-radius:5px" src="<?php echo $photo_url; ?>" class="CToWUd">            
                     </td>
                     <td width="100%" style="padding:0;margin:0" valign="top">
                        <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:15px;line-height:18px;margin-bottom:0;margin-top:0;padding:0;color:#1b1d1e"> 
                           @if(($comment->user_type == USER_TYPE_TEAM_MEMBER))                                   
                        	<strong>{{ config()->get('constants.company_name') }} @lang('form.support')</strong> 
                           @endif
                           ({{ $comment->user->name }})                                                            
                        </p>
                        <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:13px;line-height:25px;margin-bottom:15px;margin-top:0;padding:0;color:#bbbbbb">             

                        {{ $comment->created_at->format('M d Y, h:i A') }} {{ $comment->created_at->format('P') }}


                                    </p>
                        <div class="m_4227488671114434441zd-comment" style="color:#2b2e2f;font-family:'Lucida Sans Unicode','Lucida Grande','Tahoma',Verdana,sans-serif;font-size:14px;line-height:22px;margin:15px 0">
                           <p style="color:#2b2e2f;font-family:'Lucida Sans Unicode','Lucida Grande','Tahoma',Verdana,sans-serif;font-size:14px;line-height:22px;margin:15px 0" dir="auto"><?php echo $comment->body; ?></p>
                           <p></p>
                        </div>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>	

@endforeach

</div>
@endif
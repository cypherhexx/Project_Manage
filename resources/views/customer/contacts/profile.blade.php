<div class="row">
   <div class="col-md-3 pr-1">
     <img class="card-img-top img-fluid img__img member-avatar" src="<?php echo asset('images/user-placeholder.jpg') ; ?>"  v-bind:src="records.contact_photo_url">
   </div>
   <div class="col-md-9 pl-1" >
      <h4><a v-bind:href="records.contact_edit_page_url">@{{ records.first_name   }} @{{ records.last_name }}</a> <i v-if="records.is_important" class="fas fa-star star-important" data-toggle="tooltip" data-placement="top" title="@lang('form.important')"></i></h4>
      <div>@{{ records.position   }}, <a v-bind:href="records.company_page_url">@{{ records.company_name }}</a></div>     
      <div class="quick-preview">
         <table>
            <tr>
               <td>@lang('form.email')</td>
               <td>: @{{ records.email }}</td>
            </tr>
            <tr>
               <td>@lang('form.phone')</td>
               <td>: @{{ records.phone }}</td>
            </tr>
         </table>
      </div>
   </div>
</div>


<li class="nav-item dropdown notification" id="notificationList" v-cloak>
   <a v-on:click.prevent="showNotifications" class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-bell mr-3"></i><div id="notification_badge" style="display:none; font-size: 55% !important; margin-left: -25px !important; position: relative;" class="badge badge-pill badge-danger"></div>
   </a>
   <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown01" style="width: 300px; font-size: 13px; max-height: 300px; overflow-y: scroll">
      <div class="text-center" v-if="loadingEnabled">@lang('form.loading')</div>
      <a v-if="notifications.length > 0 && !loadingEnabled" class="btn btn-link float-md-right"  style="border-bottom:1px solid #eee; font-size: 12px;" href="{{ route('notification_all_mark_as_read')}}">@lang('form.mark_all_as_read')</a>           
      <a v-if="!loadingEnabled" v-for="notification in notifications" class="dropdown-item"  style="white-space: normal !important; padding-top: 10px; padding-bottom: 10px; border-bottom:1px solid #eee;" :href="notification.url">@{{ notification.message }}
      <small class="form-text text-muted">@{{ notification.moment }}</small>
      </a>
      <div v-if="notifications.length == 0 && !loadingEnabled" class="text-center">@lang('form.no_unread_notifications')</div>
      <a v-if="!loadingEnabled" class="dropdown-item btn btn-light text-center"  style="white-space: normal !important; padding-top: 10px; padding-bottom: 10px; border-bottom:1px solid #eee;" href="{{ route('member_view_all_notifications', auth()->user()->id ) }}">@lang('form.view_all_notifications')</a>
   </div>
</li>
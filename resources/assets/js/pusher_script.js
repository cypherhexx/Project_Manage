/* this expects global_config variable (in the main.blade.php file) to have the following values:
  pusher_log_status ,
  pusher_app_key , 
  pusher_cluster,
  pusher_channel,
  is_pusher_enable,
*/

if(global_config.is_pusher_enable)
{
    Pusher.logToConsole = global_config.pusher_log_status ;  

    var pusher = new Pusher(global_config.pusher_app_key , {
      cluster: global_config.pusher_cluster ,
      forceTLS: true
    });

    var channel = pusher.subscribe(global_config.pusher_channel);

    channel.bind('new.notification', function(data) {          
      var $number = $("#notification_badge").text();
      var num_of_notification = ($number) ? parseInt($number) : 0 ;
      $("#notification_badge").html(num_of_notification+1).show();

    });

}
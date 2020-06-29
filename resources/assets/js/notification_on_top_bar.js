var notificationListVueInstance = new Vue({

    el: '#notificationList',
    data: {
        notifications :[],
        loadingEnabled : false
    },

  computed: {},
  methods: {        

        showNotifications: function(){
           $scope = this;
           $("#notification_badge").html("").hide();
           $scope.loadingEnabled = true;
           $.post( global_config.url_get_unread_notifications , { "_token":  global_config.csrf_token })
            .done(function( data ) {
                $scope.notifications = data;
                
            })
            .always(function() {
                $scope.loadingEnabled = false;
            });
        }
    }
    

 });
const mix = require('laravel-mix');



mix.sass('resources/assets/sass/app.scss', 'public/css').version();


// mix.sass('resources/assets/sass/vendor.scss', 'public/css')
//   .combine([   

mix.styles([  
        
        'node_modules/bootstrap-daterangepicker/daterangepicker.css',
        'node_modules/jgrowl/jquery.jgrowl.min.css',
        'node_modules/four-boot/dist/JQuery.four-boot.min.css',
        'node_modules/select2/dist/css/select2.min.css',
        'resources/assets/vendor/select2-bootstrap/select2-bootstrap.css',
        'node_modules/rangeslider.js/dist/rangeslider.css',
        'resources/assets/vendor/At.js/css/jquery.atwho.min.css',
        'resources/assets/vendor/gantt-chart/css/style.css',
        'node_modules/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css',
        // Datatable 
        'resources/assets/vendor/datatable-bootstrap/css/datatables.min.css',       
      
        
        

    ], 
    'public/css/vendor.css'
  ).version()
   .sass('node_modules/bootstrap/scss/bootstrap.scss', 'public/css')
   .styles([
        'public/css/bootstrap.css',
        'node_modules/select2/dist/css/select2.min.css',
        'resources/assets/vendor/select2-bootstrap/select2-bootstrap.css',
    ], 
    'public/css/guest.css'
  ).version()
   .js('resources/assets/js/app.js', 'public/js').version()
  //  // .extract(['lodash', 'jquery', 'bootstrap', 'axios', 'vue','chart.js', 
  //  //            'sweetalert', 'jgrowl', 'four-boot', 'rangeslider.js', 'web-animations-js', 'hammerjs' ,
  //  //            'muuri'
              
  //  //            ])
   .autoload({
    jquery: ['$', 'window.jQuery', 'jQuery', 'jquery'],
   
  })
  ;


 mix.js('resources/assets/js/tinymce.js', 'public/js').version();

 // Vendor Js

 mix.scripts([
    // DatatTable
    // 'resources/assets/vendor/datatable-bootstrap/js/pdfmake.min.js',
    // 'resources/assets/vendor/datatable-bootstrap/js/vfs_fonts.js',
    'resources/assets/vendor/datatable-bootstrap/js/datatables.min.js',
    
    'resources/assets/vendor/moment/moment.min.js',
    'resources/assets/vendor/tinymce-vue/tinymce-vue.min.js',
    'resources/assets/vendor/Caret.js/jquery.caret.min.js',
    'resources/assets/vendor/At.js/js/jquery.atwho.min.js',
    'resources/assets/vendor/pusher/pusher.min.js',
    

    

    // colorPicker
    'node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js',

   
    
], 'public/js/vendor.js').version();


mix.scripts([
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/bootstrap/dist/bootstrap.min.js',
    'node_modules/sweetalert/dist/sweetalert.min.js',
    'node_modules/jgrowl/jquery.jquery.jgrowl.min.js',
    'node_modules/four-boot/dist/JQuery.four-boot.min.js',
    'node_modules/select2/dist/js/select2.full.min.js',

], 'public/js/guest.js').version();



//  // DatatTable
// mix.scripts([    
//     'resources/assets/vendor/datatable-bootstrap/js/datatables.min.js',
//      // 'resources/assets/vendor/datatable-bootstrap/js/pdfmake.min.js',
//      // 'resources/assets/vendor/datatable-bootstrap/js/vfs_fonts.js',

// ], 'public/js/datatable.js').version();





   mix.scripts([    

    // MicroSearch
    'resources/assets/js/microsearch.js',

    // Display Notifications on top bar menu
    'resources/assets/js/notification_on_top_bar.js',

     // Upload Attachments
    'resources/assets/js/upload_attachment.js',

     // Generic block of codes (being used on the entire app)
    'resources/assets/js/generic.js',

     // Pusher Notification
    'resources/assets/js/pusher_script.js',

], 'public/js/main.js').version();


mix.copy('node_modules/tinymce/skins', 'public/js/skins');

// Make sure gantt-chart and laravel-filemanager folders are in public/vendor folder
mix.copy('resources/assets/vendor/gantt-chart', 'public/vendor/gantt-chart', false);
mix.copy('resources/assets/vendor/laravel-filemanager', 'public/vendor/laravel-filemanager', false);

mix.copy('resources/assets/vendor/gantt-chart/img', 'public/img', false);


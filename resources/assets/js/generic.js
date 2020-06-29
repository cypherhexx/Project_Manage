var updateQueryStringParam = function (key, value) {

        var baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
            urlQueryString = document.location.search,
            newParam = key + '=' + value,
            params = '?' + newParam;

        // If the "search" string exists, then build params from it
        if (urlQueryString) {

            updateRegex = new RegExp('([\?&])' + key + '[^&]*');
            removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');

            if( typeof value == 'undefined' || value == null || value == '' ) { // Remove param if value is empty

                params = urlQueryString.replace(removeRegex, "$1");
                params = params.replace( /[&;]$/, "" );

            } else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it

                params = urlQueryString.replace(updateRegex, "$1" + newParam);

            } else { // Otherwise, add it to end of query string

                params = urlQueryString + '&' + newParam;

            }

        }
        window.history.replaceState({}, "", baseUrl + params);
    };

    function get_url_parameters()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }


$(function(){


$('[data-toggle="tooltip"]').tooltip();


        $('#sidebarCollapse').on('click', function (e) {
            e.preventDefault();
            $('#sidebar').toggleClass('active');


        });

        $( ".selectPickerWithoutSearch" ).select2( {
            theme: "bootstrap",

            minimumResultsForSearch: -1,
            placeholder: function(){
                $(this).data('placeholder');
            },
            maximumSelectionSize: 6
        } );

        $( ".selectpicker" ).select2( {
            theme: "bootstrap",
            placeholder: function(){
                $(this).data('placeholder');
            },
            maximumSelectionSize: 6
        } );

        $( ".select2-multiple" ).select2( {
            theme: "bootstrap",
            placeholder: "Nothing Selected",
            maximumSelectionSize: 6
        } );

        $('.four-boot').fourBoot();





        ranges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 12 Months': [moment().subtract(11, 'month').startOf('month'), moment()]
        };

        var start = moment().startOf('month');
        var end = moment().endOf('month');

        function cb(start, end) {
            $('#reportrange span').html(start.format('D , MMMM , YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges : ranges,
            locale: {

              // format: 'DD/MM/YYYY'
              format : 'MMM D, YYYY'
            }
        }, cb);

        cb(start, end);  


        $('.initially_empty_datepicker').daterangepicker({
                autoUpdateInput: false,
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD-MM-YYYY'
                }

            }).on('apply.daterangepicker', function(ev, picker) {
                
                $(this).val(picker.endDate.format('DD-MM-YYYY'));
            });


        $('.datepickerAndTime').daterangepicker({
            timePicker: true,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY hh:mm A'
            },
            startDate: moment()

        });    



        $('.datepicker').daterangepicker({

            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            },
            startDate: moment()

        });



        // Bootstrap NavBar Multi Level
        $('.dropdown-submenu > a').on("click", function(e) {
            e.preventDefault();
            var submenu = $(this);
            $('.dropdown-submenu .dropdown-menu').removeClass('show');
            submenu.next('.dropdown-menu').addClass('show');
            e.stopPropagation();
        });

        $('.dropdown').on("hidden.bs.dropdown", function() {
            // hide any open menus when parent closes
            $('.dropdown-menu.show').removeClass('show');
        });

        // End of Bootstrap NavBar Multi Level



});



    $(document).on('click','.delete_item',function(e){
        //  $(this) = your current element that clicked.
        // additional code
        e.preventDefault();
        var url = $(this).attr('href');   

        swal({
              title: global_config.txt_delete_confirm_title ,
              text:  global_config.txt_delete_confirm_text ,
              icon: "warning",
              buttons: {
                        cancel: {
                          text: global_config.txt_btn_cancel ,
                          value: null,
                          visible: true,
                          className: "",
                          closeModal: true,
                        },
                        confirm: {
                          text: global_config.txt_yes ,
                          value: true,
                          visible: true,
                          className: "",
                          closeModal: true
                        }
                      },
              dangerMode: true,
              
            }).then(function (willDelete) {
              if (willDelete) {
                window.location.href = url;
              } 
            });

    });


    $(document).on('click', '.insert_short_code', function(e){
            e.preventDefault();
            var element = $(this);
            
            var short_code = element.data('short-code');
            var insert_in = element.data('insert-in');

            var box = $(insert_in);
            box.val(box.val() + " " +short_code );
           
    });



/* Notes */

$(function () {
        

    function toggleNote(id)
    {
        console.log("toggleNote : " + id);
        var inlineEdit      = $('.inlineEdit_' + id);
        var note_details    = $('.note_details_'+ id);

        if($(inlineEdit).is(":hidden"))
        {            
            inlineEdit.show();
            note_details.hide();
        }
        else
        {            
            inlineEdit.hide();
            note_details.show();
        }
    }

$('.editNote').click(function(e){
    e.preventDefault();

    var id = $(this).data('id');
   
    toggleNote(id);


});

$('.saveNote').click(function(e){
    e.preventDefault();
    var id = $(this).data('id');
    
    var newNote = $('.inlineEdit_' + id).find('textarea[name=details]').val();

    $('.note_details_' + id).html(newNote);

    toggleNote(id);
    saveNote(id, newNote);


});

        

});




    function saveNote(id, $newNote)
    {
        var postData = {
            _token : global_config.csrf_token,
            id : id,
            details : $newNote
        };
        $.post(global_config.url_patch_note , postData ).done(function( response ) {

            if(response.status == 1)
            {
                $.jGrowl(response.msg, { position: 'bottom-right'});
            }
            else
            {

            }
        });

    }

    function deleteNote(id)
    {
        var postData = {
            _token : global_config.csrf_token,
            id : id
            
        };
        $.post(global_config.url_delete_note , postData ).done(function( response ) {

            if(response.status == 1)
            {
                $('.note_thread_' + id).remove();
                $.jGrowl(response.msg, { position: 'bottom-right'});
            }
            else
            {

            }
        });

    }

$(document).on('click','.delete_note',function(e){

    e.preventDefault();

    id = $(this).data('id');


    swal({
      title: global_config.txt_delete_confirm_title ,
      text:  global_config.txt_delete_confirm_text ,
      icon: "warning",
      buttons: {
                cancel: {
                  text: global_config.txt_btn_cancel ,
                  value: null,
                  visible: true,
                  className: "",
                  closeModal: true,
                },
                confirm: {
                  text: global_config.txt_yes ,
                  value: true,
                  visible: true,
                  className: "",
                  closeModal: true
                }
              },
      dangerMode: true,
      
    }).then(function (willDelete) {
      if (willDelete) {
        
        deleteNote(id);
      } 
    });

});

/* End of Notes */
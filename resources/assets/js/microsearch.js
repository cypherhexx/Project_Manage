(function($) {


    var highlighter = function highlighter(item) {

        return (settings.displayText) ? item[settings.displayText] : item;
    };

    settings = {
        minLength: 3,
        source: [],
        highlighter: highlighter,
        displayText: null,
        anchorLink: null,
        fitToElement: false,
        noRecordFoundMessage: "No record found"
    };
    dataList: [];

    style = {
        listHolderClass: 'micoSearch'
    };

    $.fn.micoSearch = function(options) {



        settings = $.extend({}, settings, options);

        $(this).keyup(function(event) {

            if (!this.value) {
                return;
            }
            this.value = this.value.trim();
            if (this.value.length < settings.minLength) {

                return;
            }
            $(this).parent().find('ul.' + style.listHolderClass).remove();
            $(document).on('click', function(event) {

                remove_element(this);
            });


            run(this);




        });


        return this;
    };

    function run($scope) {
        if ($scope.value.length > 0) {

            $.when(set_source($scope.value)).then(function() {

                    $html = genHtml(get_source());

                    $($html).insertAfter($scope);
                }

            );
        }


    }

    function remove_element($scope) {
        $($scope).find('ul.' + style.listHolderClass).remove();
    }

    function get_source() {

        return dataList;
    }

    function set_source($query) {
        var dfd = jQuery.Deferred();

        if ($.isFunction(settings.source)) {
            settings.source($query).done(function(data) {
                if (data) {
                    dataList = data;
                }
                dfd.resolve("hurray");
            });

        } else {
            dataList = settings.source;
            dfd.resolve("hurray");
        }

        return dfd.promise();
    }



    function genHtml(data) {
        var fitToElement = (settings.fitToElement) ? 'width:100%;' : '';
        var str = '<ul class="' + style.listHolderClass + ' dropdown-menu" role="listbox" style="top: 31px; left: 0px; display: block; height: auto; max-height: 350px; overflow-x: hidden; ' + fitToElement + '">';

        if (data.length > 0) {

            $.each(data, function(key, item) {
                var label = settings.highlighter(item);
                var anchorLink = (settings.anchorLink) ? item[settings.anchorLink] : "#";
                str += '<li class="active"><a class="dropdown-item" href="' + anchorLink + '" role="option">' + label + '</a></li>';
            });

        } else {
            str += '<li class="active"><a class="dropdown-item" href="#" role="option">' + settings.noRecordFoundMessage + '</a></li>';
        }

        str += '</ul>';
        return str;
    }




})(jQuery);


$(function() {
    $('#global_search').micoSearch({

        displayText: 'label',
        anchorLink : 'link',
        fitToElement : true,
        noRecordFoundMessage : global_config.lang_no_record_found,
        source: function (query) {
             return $.get( global_config.url_global_search , { query: query }, function (data) {                    
                       return data;                    
            });
        },
        
        highlighter: function(item) {     
                        
              var str   = '<div>' + item.label + '</div>';
              str       = str + '<small id="emailHelp" class="form-text text-muted">' + item.type  + '</small>'
              return (str);
        }                
                     
    });

});
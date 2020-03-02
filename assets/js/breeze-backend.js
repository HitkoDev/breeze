jQuery(document).ready(function ($) {

    var $compatibility_warning = $('#breeze-plugins-notice');
    if ($compatibility_warning.length) {
        $(document).on('click tap', '.notice-dismiss', function () {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {action: "compatibility_warning_close", 'breeze_close_warning': '1'},
                dataType: "json", // xml, html, script, json, jsonp, text
                success: function (data) {

                },
                error: function (jqXHR, textStatus, errorThrown) {

                },
                // called when the request finishes (after success and error callbacks are executed)
                complete: function (jqXHR, textStatus) {

                }
            });
        });
    }

    // Topbar action
    $('#wp-admin-bar-breeze-purge-varnish-group').click(function(){
        breeze_purgeVarnish_callAjax();
    });
    $('#wp-admin-bar-breeze-purge-file-group').click(function(){
        breeze_purgeFile_callAjax();
    });
    // Varnish clear button
    $('#purge-varnish-button').click(function(){
        breeze_purgeVarnish_callAjax();
    });

    //clear cache by button
    function breeze_purgeVarnish_callAjax(){
        $.ajax({
            url:ajaxurl,
            dataType:'json',
            method:'POST',
            data:{
                action:'breeze_purge_varnish',
				is_network: $('body').hasClass( 'network-admin' ),
                security : breeze_token_name.breeze_purge_varnish
            },
            success : function(res){
                current = location.href;
                if(res.clear){
                    var div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>Varnish Cache has been purged.</strong></div>';
                    //backend
                    $("#wpbody .wrap h1").after(div);
                    setTimeout(function(){
                        location.reload();
                    },2000);
                }else{
                    window.location.href = current+ "breeze-msg=purge-fail";
                    location.reload();
                }
            }
        });
    }

    function breeze_purgeFile_callAjax(){
        $.ajax({
            url:ajaxurl,
            dataType:'json',
            method:'POST',
            data:{
                action:'breeze_purge_file',
                security : breeze_token_name.breeze_purge_cache
            },
            success : function(res){
                current = location.href;
                res = parseFloat(res) ;

                window.location.href = current+ "#breeze-msg=success-cleancache&file="+res;
                location.reload();
            }
        });
    }

    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    var url = location.href;
    var fileClean = parseFloat(getParameterByName('file',url) );

    $( window ).load(function() {
        var patt = /wp-admin/i;
        if(patt.test(url)){
            //backend
            var div = '';
            if(url.indexOf("msg=success-cleancache") > 0 && !isNaN(fileClean) ) {
                if(fileClean > 0){
                    div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>Internal cache has been purged: '+fileClean+'Kb cleaned</strong></div>';
                }else{
                    div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>Internal cache has been purged.</strong></div>';

                }

                $("#wpbody .wrap h1").after(div);

                var url_return = url.split('breeze-msg');
                setTimeout(function(){
                    window.location = url_return[0];
                    location.reload();
                },2000);
            }
        }else{
            //frontend
        }

    });

    $('#breeze-hide-install-msg').unbind('click').click(function () {
        $(this).closest('div.notice').fadeOut();
    })


    function current_url_clean() {
        var query_search = location.search;
        if (query_search.indexOf('breeze_purge=1') !== -1 && query_search.indexOf('_wpnonce') !== -1) {
            var params = new URLSearchParams(location.search);
            params.delete('breeze_purge')
            params.delete('_wpnonce')
            history.replaceState(null, '', '?' + params + location.hash)
        }
    }

    current_url_clean();

});

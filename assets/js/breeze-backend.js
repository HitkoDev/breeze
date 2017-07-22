jQuery(document).ready(function ($) {
    // Topbar action
    $('#wp-admin-bar-breeze-purge-varnish-group').click(function(){
        breeze_purgeVarnish_callAjax();
    });
    $('#wp-admin-bar-breeze-purge-file-group').click(function(){
        breeze_purgeFile_callAjax();
    });
    $('#wp-admin-bar-breeze-purge-database-group').click(function(){
        breeze_purgeDatabase_callAjax();
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
                action:'breeze_purge_varnish'
            },
            success : function(res){
                current = location.href;
                if(res.clear){
                    var div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>OK! Purge Varnish Success</strong></div>';
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
                action:'breeze_purge_file'
            },
            success : function(res){
                current = location.href;
                res = parseFloat(res) ;
                if(current.indexOf("page=breeze_config") > 0){
                    window.location.href = current+ "#breeze-msg=success-cleancache&file="+res;
                }else{
                    window.location.href = current+ "breeze-msg=success-cleancache&file="+res;
                }
                location.reload();
            }
        });
    }

    function breeze_purgeDatabase_callAjax(){
        $.ajax({
            url:ajaxurl,
            dataType:'json',
            method:'POST',
            data:{
                action:'breeze_purge_database'
            },
            success : function(res){
                current = location.href;
                if(res.clear){
                    var div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>OK! Database cleanup successful</strong></div>';
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
                    div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>OK cache is clean: '+fileClean+'Kb cleaned</strong></div>';
                }else{
                    div = '<div id="message" class="notice notice-success" style="margin-top:10px; margin-bottom:10px;padding: 10px;"><strong>OK ! Cache file cleaned successfully</strong></div>';

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


});
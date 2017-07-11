var getserverName = function(ver){
    var domain = domainURI(document.location.href);
    $.ajax({
        type: "get",
        url: "http://" + domain + "/mmsapi" + ver + "/get/status/@monengine",
        async: true,
        dataType: "json",
        success: function(json, textStatus, jqXHR){//如果调用php成功
            //alert(JSON.stringify(json))
            if (json == null) {
                console.log("服务器传的数据为空");
            }
            else {
                for (var i in json) {
                    //console.log($("#warmForm ul li select"));
                    var option = $("<option></option>");
                    option.text(json[i].name);
                    option.val(json[i].name);
                    option.attr("name", "current_engine");
                    $("select").append(option);
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            switch (jqXHR.status) {
                case 400:
                    alert("NOT Found!!!");
                    break;
                case 500:
                    alert("Server Error!!!");
                    break;
            }
        }
    });
    setTimeout(function(){
        $.ajax({
            type: "get",
            url: "http://" + domain + "/mmsapi" + ver + "/get/alarmSetting/@self",
            async: true,
            dataType: "json",
            success: function(json, textStatus, jqXHR){//如果调用php成功
                if (json == null) {
                    alert("服务器传的数据为空");
                }
                else {
                    (function(){
						var _x_ = arguments.callee;
						var _option_ = $("select option");
						if(!_option_.length){
							setTimeout(_x_,500);
							return;
						}
                        _option_.each(function(){
                            var current = $(this).val();
                            if (current == json.current_engine) {
                                //console.log(current);
                                $(this).attr("selected", true);
                            }
                        });
                    })();
                    
                    $('#warnForm input[name="all_default_gp_down"]').val(json.all_default_gp_down);
                    $('#warnForm input[name="all_cust_gp_down"]').val(json.all_cust_gp_down);
                    $('#warnForm input[name="one_default_gp_down"]').val(json.one_default_gp_down);
                    $('#warnForm input[name="one_cust_gp_down"]').val(json.one_cust_gp_down);
                    $('#warnForm input[name="one_default_server_down"]').val(json.one_default_server_down);
                    $('#warnForm input[name="one_cust_server_down"]').val(json.one_cust_server_down);
                    $('#warnForm input[name="general_server_event"]').val(json.general_server_event);
                    $('#warnForm input[name="recover_notifiction"]').val(json.recover_notifiction);
                    //console.log(json.recover_notifiction);
                    if (json.recover_notifiction == 0) {
                        $('#warnForm input[name="recover_notifiction"]').attr("checked", false);
                    }
                    else 
                        if (json.recover_notifiction == 1) {
                            $('#warnForm input[name="recover_notifiction"]').attr("checked", true);
                        }
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                switch (jqXHR.status) {
                    case 400:
                        alert("NOT Found!!!");
                        break;
                    case 500:
                        alert("Server Error!!!");
                        break;
                }
            }
            
        });
    }, 10);
};




$(document).ready(function(){
    var version = url_params("version");
    var domain = domainURI(document.location.href);
    getserverName(version);
    $('#warnForm input[name="recover_notifiction"]').click(function(){
        //console.log($(this).val());
        if ($(this).attr("checked")) {
            $(this).val(1);
        }
        else {
            $(this).val(0);
        }
    });
    $("#warnForm").submit(function(event){
        event.preventDefault();
        var data = {
            "current_engine": $("select").find("option:selected").val(),
            "all_default_gp_down": $('#warnForm input[name="all_default_gp_down"]').val(),
            "all_cust_gp_down": $('#warnForm input[name="all_cust_gp_down"]').val(),
            "one_default_gp_down": $('#warnForm input[name="one_default_gp_down"]').val(),
            "one_cust_gp_down": $('#warnForm input[name="one_cust_gp_down"]').val(),
            "one_default_server_down": $('#warnForm input[name="one_default_server_down"]').val(),
            "one_cust_server_down": $('#warnForm input[name="one_cust_server_down"]').val(),
            "general_server_event": $('#warnForm input[name="general_server_event"]').val(),
            "recover_notifiction": $('#warnForm input[name="recover_notifiction"]').val()
        };
        post_data("http://" + domain + "/mmsapi" + version + "/update/alarmSetting/@self", "true", data);
        //warnPost();
    });
});

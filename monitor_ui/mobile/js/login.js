function login(version, domain){
    var username = $("input[name='username']").val(),
	    passwd = $("input[name='passwd']").val(),
    	keeplogin = $("input[name='keeplogin']")[0].checked;
    var data = {
        "username": username,
        "passwd": passwd,
        "keeplogin": keeplogin ? 1:0
    };
    //console.log(data);
    if (username == "" || passwd == "") {
        var error = $("#error");
        if (error.length != 0) {
            error.remove();
        }
        $('<div id="error" class="bubble">请输入用户名或者密码</div>').insertBefore($("#loginForm label:first-child"));
    }
    else {
        $.ajax({
            type: "post",
            url: "http://" + domain + "/mmsapi" + version + "/update/login/@self",
            async: false,
            data: data,
            success: function(json, textStatus, jqXHR){//如果调用php成功
                if (jqXHR.status == 200) {
                    location.href = "main.html?version=" + version;
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                if (jqXHR.status == 401) {
                    var error = $("#error");
                    if (error.length != 0) {
                        error.remove();
                    }
                    $('<div id="error" class="bubble">您输入的用户名或密码不正确。</div>').insertBefore($("#loginForm label:first-child"));
                }
            }
        });
    }
    
    
}

$(document).ready(function(){
    var version = urlParams("version");
    var domain = domainURI();
    
    $.ajax({
        type: "get",
        url: "http://" + domain + "/mmsapi" + version + "/get/login/@self",
        async: true,
        dataType: "json",
        success: function(json, textStatus, jqXHR){
            if (jqXHR.status == 200) {
                location.href = "main.html?version=" + version;
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            if (jqXHR.status == 401) {
                $("#loginForm").show();
            }
        }
    });
    
    
    /*******************************************/
    $("#loginsubmit").click(function(){
        login(version, domain);
		return false;
    });
    
});

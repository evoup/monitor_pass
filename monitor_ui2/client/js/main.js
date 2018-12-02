function get_status_logininfo_Callback(json, textStatus, jqXHR) {
    if (json == null) {
        console.log("json null");
    } else {
        var account, ver = url_params("version");
        if (json[1] == null) {
            window.location.href = "login.html?version=" + ver;
        } else {
            if (json[0] == null) {
                account = json[1];
            } else {
                account = json[0] + "：" + json[1];
            }
            $("#header .account cite").html(account);
        }
    }
}


function exitLogin(_url) {
    var vers = url_params("version");
    $.ajax({
        type: "post",
        url: _url,
        async: false,
        data: {'token': window.localStorage['mms_token']},
        success: function (json, textStatus, jqXHR) {
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401 || jqXHR.status == 400) {
                window.location.href = "login.html?version=" + vers;
            }
        }
    });
}


function contentIframeHeight() {

    var notIframeHeight = $("#header").height() + $("#top_navigation").height() + $("#footer").height() + 30,
        iframeHeight = $(window).height() - notIframeHeight;

    $("#contentIframe").height(iframeHeight);
}

$(document).ready(function () {
    var version = url_params("version");
    var domain = domainURI(document.location.href);

    bindClickNav();

    var yearLastVersion = new Date().getFullYear(); //页脚版本结束年份

    $("#footer cite").text(yearLastVersion);

    get_data_notLoad("http://" + domain + "/mmsapi" + version + "/get/status/@logininfo", get_status_logininfo_Callback);
    $("#header .account a").click(function () {
        window.localStorage['mms_token'] = null;
        window.location.href = "login.html?version=" + version;
    });
    $("#contentIframe").load(function () {
        contentIframeHeight();
    });
    window.onresize = contentIframeHeight;

    exitLogin("http://" + domain + "/mmsapi" + version + "/login/status/");

    window.setInterval(function () {
        exitLogin("http://" + domain + "/mmsapi" + version + "/login/status/");
    }, 300000);
});

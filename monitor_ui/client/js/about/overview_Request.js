/**********************get主程序的down/up***************************************************/
function get_indexPro_Callback(json, textStatus, jqXHR){
    //console.log(json); 
    if (json == null) {
        console.log("服务器传的数据为空");
    }
    else {
        var str = "", on = 0, down = 0, _json = json;
        for (var one = 0; one < _json.length; one++) {
            if (_json[one].status === 1) {
                on++;
            }
            else 
                if (_json[one].status === 0) {
                    down++;
                }
        }
        str = on + "个中央节点on" + "<br/>" + down + "个中央节点down";
        //on=1,down=3;
        $("#MonitorServer0 td:eq(3)").html(str);
        if (down == 0) {
            $("#MonitorServer0 td:eq(3)").css({
                "background": "#CCFF99",
                "-webkit-animation": ""
            });
        }
        else 
            if (down / parseFloat(down + on) >= parseFloat(1 / 3) && down / parseFloat(down + on) < parseFloat(2 / 3)) {
                $("#MonitorServer0 td:eq(3)").css({
                    "background": "#FEFF6F",
                    "-webkit-animation": ""
                });
            }
            else 
                if (down / parseFloat(down + on) >= parseFloat(2 / 3)) {
                    $("#MonitorServer0 td:eq(3)").css({
                        "background": "#FF795F",
                        "-webkit-animation": "twinkling 1s infinite ease-in-out",
                        "-moz-animation": "twinkling 1s infinite ease-in-out"
                    });
                }
    }
    
}





/*************************get MDB 的down/up****************************************************/
function get_mdb_Callback(json, textStatus, jqXHR){
    if (json == null) {
        console.log("服务器传的数据为空");
    }
    else {
        var str = [], on = 0, down = 0, total;
        for (var one = 0; one < json.length; one++) {
            if (json[one].status === 1) {
                on++;
            }
            else 
                if (json[one].status == 0) {
                    down++;
                }
            str[one] = json[one].name;
        }
        total = on + down;
        // on=1,down=3;
        $("#MonitorServer0 td:eq(4)").html(on + "个服务器on" + "<br/>" + down + "个服务器down");
        if (down == 0 || down / parseFloat(down + on) < parseFloat(1 / 3)) {
            $("#MonitorServer0 td:eq(4)").css({
                "background": "#CCFF99",
                "-webkit-animation": ""
            });
        }
        else 
            if (down / parseFloat(down + on) >= parseFloat(1 / 3) && down / parseFloat(down + on) < parseFloat(2 / 3)) {
                $("#MonitorServer0 td:eq(4)").css({
                    "background": "#FEFF6F",
                    "-webkit-animation": ""
                });
            }
            else 
                if (down / parseFloat(down + on) >= parseFloat(2 / 3)) {
                    $("#MonitorServer0 td:eq(4)").css({
                        "background": "#FF795F",
                        "-webkit-animation": "twinkling 1s infinite ease-in-out",
                        "-moz-animation": "twinkling 1s infinite ease-in-out"
                    });
                }
        
        //检测中央节点和服务器的监控状态
        var eq3 = $("#MonitorServer0 td:eq(3)"), eq4 = $("#MonitorServer0 td:eq(4)"), eq5 = $("#MonitorServer0 td:eq(5)");
        if (eq3.css("background") == "rgb(254, 255, 111)" && eq4.css("background") != "rgb(255, 121, 95)" || eq4.css("background") == "rgb(254, 255, 111)" && eq3.css("background") != "rgb(255, 121, 95)") {//其中之一为黄色并且没有红色
            eq5.css({
                "background": "#FEFF6F",
                "font-size": "14px",
                "-webkit-animation": ""
            });
            eq5.text("注意");
            
        }
        else 
            if (eq3.css("background") == "rgb(255, 121, 95)" || eq4.css("background") == "rgb(255, 121, 95)") {//其中一个为红色
                eq5.css({
                    "background": "#FF795F",
                    "font-size": "14px",
                    "-webkit-animation": "twinkling 1s infinite ease-in-out",
                    "-moz-animation": "twinkling 1s infinite ease-in-out"
                });
                eq5.text("紧急");
                
            }
            else 
                if (eq3.css("background") == "rgb(255, 121, 95)" && eq4.css("background") == "rgb(254, 255, 111)" || eq3.css("background") == "rgb(254, 255, 111)" && eq4.css("background") == "rgb(255, 121, 95)") { //一红一黄
                    eq5.css({
                        "background": "#FF795F",
                        "font-size": "14px",
                        "-webkit-animation": "twinkling 1s infinite ease-in-out",
                        "-moz-animation": "twinkling 1s infinite ease-in-out"
                    });
                    eq5.text("紧急");
                }
                else { //绿色
                    eq5.css({
                        "background": "#CCFF99",
                        "font-size": "14px",
                        "-webkit-animation": ""
                    });
                    eq5.text("良好");
                    
                }
    }
    
}





/*******************************************************************************************************/
function get_health_Callback(json, textStatus, jqXHR){
    if (json == null) {
        console.log("服务器传的数据为空");
    }
    else {
        var str = ["监控事件health", "", "主机Health", "", "事件Health", ""];
        for (var i in json) {
            if (i == "hosts") {
                str[3] = json[i];
                continue;
            }
            else 
                if (i == "events") {
                    str[5] = json[i];
                    continue;
                }
            
        }
        var tableObj = $("#MonitorServer1");
        if (tableObj.length == 0) {
            var table = new Table(3, 2, "MonitorServer1", "serverBox_health");
            table.setTable("100%", "100%");
        }
        $("#MonitorServer1 tr td").each(function(m){
            if (m == 3 || m == 5) {
                $(this).html("<p style='padding:0px;margin:0px;background:#F0F0F0;'>" + str[m] + "</p>");
            }
            else {
                $(this).html(str[m]);
            }
        });
        $("#MonitorServer1 tr td p").each(function(m){
            var per = Number($(this).text());
            if (per >= 50) {
                $(this).css({
                    "background": "#ACDF79",
                    "border": "1px solid #ACDF79",
                    "border-radius": "2px",
                    "color": "#2D69D9",
                    "-webkit-animation": ""
                });
                //$(this).css({"background":"-webkit-gradient(linear, 0 0, 0 100%, from(#B6FB7F), to(#76BB3F))",	"background":"-moz-linear-gradient(center top , #B6FB7F, #76BB3F)","border":"1px solid #ACDF79","border-radius":"2px","color":"#4D89F9"});
            
                // $(this).addClass("b");
            }
            else 
                if (per < 50) {
                    $(this).css({
                        "background": "#FF795F",
                        "border": "1px solid #DF593F",
                        "border-radius": "2px",
                        "-webkit-animation": "twinkling 1s infinite ease-in-out",
                        "-moz-animation": "twinkling 1s infinite ease-in-out",
                        "color": "#2D69D9"
                    });
                }
            $(this).text(per + "%");
            $(this).css({
                "width": per + "%"
            });
        });
    }
    
}





function get_server_all_Callback(json, textStatus, jqXHR){
    if (json == null) {
        console.log("服务器传的数据为空");
    }
    else {
        var num = 0, count = 0, notMonitorNum = 0, data = ["宕机", "在线", "未监控"];
        for (var k in json) {
            $.each(json[k], function(k, v){
                if (k == 0) {
                    switch (v) {
                        case "0":
                            num++;
                            break;
                        case "1":
                            count++;
                            break;
                        case "2":
                            count++;
                            break;
                        case "3":
                            count++;
                            break;
                        case "4":
                            count++;
                            break;
                        case "5":
                            notMonitorNum++;
                            break;
                    }
                }
            });
        }
        data.push(num);
        data.push(count);
        data.push(notMonitorNum);
        var tableObj = $("#MonitorClient0");
        if (tableObj.length == 0) {
            var table = new Table(2, 3, "MonitorClient0", "serverBox_all");
            table.setTable("100%", "100%");
        }
        
        $("#MonitorClient0 tr td").each(function(m){
            switch (m) {
                case 0:
                    $(this).html(data[m]);
                    break;
                case 1:
                    $(this).html(data[m]);
                    break;
                case 2:
                    $(this).html(data[m]);
                    break;
                case 3:
                    $(this).html("<a href='../server/serverList.html?version=" + url_params("version") + "&status=down'>" + data[m] + "</a>");
                    break;
                case 4:
                    $(this).html("<a href='../server/serverList.html?version=" + url_params("version") + "&status=up' >" + data[m] + "</a>");
                    break;
                case 5:
                    $(this).html("<a href='../server/serverList.html?version=" + url_params("version") + "&status=unmonitored' >" + data[m] + "</a>");
                    break;
            }
            
        });
        /*$("#MonitorClient0 td:eq(3)").html(num);
         $("#MonitorClient0 td:eq(4)").html(count);*/
        if (num > 0) {
            $("#MonitorClient0 td:eq(3)").css({
                "background": "#FF795F",
                "-webkit-animation": "twinkling 1s infinite ease-in-out",
                "-moz-animation": "twinkling 1s infinite ease-in-out",
                "-o-animation": "twinkling 1s infinite ease-in-out"
            });
        }
        else 
            if (num == 0) {
                $("#MonitorClient0 td:eq(3)").css({
                    "background": "#CCFF99",
                    "-webkit-animation": "",
                    "-moz-animation": "",
                    "-o-animation": ""
                });
            }
        if (count > 0) {
            $("#MonitorClient0 td:eq(4)").css({
                "background": "#CCFF99",
                "-webkit-animation": "",
                "-moz-animation": "",
                "-o-animation": ""
            });
        }
        else 
            if (count == 0) {
                $("#MonitorClient0 td:eq(4)").css({
                    "background": "#FF795F",
                    "-webkit-animation": "twinkling 1s infinite ease-in-out",
                    "-moz-animation": "twinkling 1s infinite ease-in-out",
                    "-o-animation": "twinkling 1s infinite ease-in-out"
                });
            }
        $("#MonitorClient0 td:eq(5)").css({
            "background": "#DFDFDF"
        });
    }
    
}




function get_status_eventsummary_Callback(json, textStatus, jqXHR){
    //console.log(json);
    if (json == null) {
        console.log("服务器传的数据为空");
    }
    else {
        var data = ["严重事件", "注意事件", "正常"], str = [];
        for (var m in json) {
            if (m == "warning") {
                str[0] = json[m];
                continue;
            }
            else 
                if (m == "caution") {
                    str[1] = json[m];
                    continue;
                }
                else 
                    if (m == "ok") {
                        str[2] = json[m];
                        continue;
                    }
        }
        for (var i in str) {
            data.push(str[i]);
        }
        var tableObj = $("#MonitorClient1");
        if (tableObj.length == 0) {
            var table = new Table(2, 3, "MonitorClient1", "serverBox_eventsummary");
            table.setTable("100%", "100%");
        }
        $("#MonitorClient1 tr td").each(function(m){
            //$(this).html(data[m]);
            switch (m) {
                case 0:
                    $(this).html(data[m]);
                    break;
                case 1:
                    $(this).html(data[m]);
                    break;
                case 2:
                    $(this).html(data[m]);
                    break;
                case 3:
                    $(this).html("<a href='../monitor/monitorEvent.html?version=" + url_params("version") + "&eventStatus=warning'>" + data[m] + "</a>");
                    break;
                case 4:
                    $(this).html("<a href='../monitor/monitorEvent.html?version=" + url_params("version") + "&eventStatus=caution'>" + data[m] + "</a>");
                    break;
                case 5:
                    $(this).html("<a href='../monitor/monitorEvent.html?version=" + url_params("version") + "&eventStatus=ok' >" + data[m] + "</a>");
                    break;
                    
            }
            
        });
        /*for(var x in str){
         if(str[x]==0){
         $("#MonitorClient1 td:eq("+(x+4)+")").css({"background":"#CCFF99"});
         }else{
         $("#MonitorClient1 td:eq("+(x+4)+")").css({"background":"#FF795F"});
         }
         } */
        if (str[0] == 0) {
            $("#MonitorClient1 td:eq(3)").css({
                "background": "#CCFF99",
                "-webkit-animation": ""
            });
        }
        else {
            $("#MonitorClient1 td:eq(3)").css({
                "background": "#FF795F",
                "-webkit-animation": "twinkling 1s infinite ease-in-out",
                "-moz-animation": "twinkling 1s infinite ease-in-out"
            });
        }
        if (str[1] == 0) {
            $("#MonitorClient1 td:eq(4)").css({
                "background": "#CCFF99"
            });
        }
        else {
            $("#MonitorClient1 td:eq(4)").css({
                "background": "#FEFF6F"
            });
        }
        if (str[2] == 0) {
            $("#MonitorClient1 td:eq(5)").css({
                "background": "#FF795F",
                "-webkit-animation": "twinkling 0.8s infinite ease-in-out",
                "-moz-animation": "twinkling 1s infinite ease-in-out"
            });
        }
        else {
            $("#MonitorClient1 td:eq(5)").css({
                "background": "#CCFF99",
                "-webkit-animation": ""
            });
        }
    }
    
}


function get_unhandled_Callback(json, textStatus, jqXHR){ //获取未处理事件问题
    if (json == null) {
        // console.log("json null");
        if ($("#Tips").length != 0) {
            $("#Tips").remove();
        }
        if ($("#unhandledTable").length != 0) {
            $("#unhandledTable").remove();
        }
        
        var tips = $("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
        tips.insertBefore($("#unhandledEventBox"));
        Tips("complete", "目前没有未处理的问题事件!");
    }
    else {
        //console.log(json);
        var unhandled = ["主机", "事件项", "状态", "持续时间", "上次检查", "状态信息"], row = 1;
        var chartId = [], id = 0;
        var ver = url_params("version"), domain = domainURI(document.location.href);
        
        $.each(json, function(i){
            //console.log(json[i]);
            ++row;
            $.each(json[i], function(j){
                j != 2 ? unhandled.push(json[i][j]) : chartId.push(json[i][j]);
                if (j == 3) {
                    unhandled.pop();
                    switch (json[i][j]) {
                        case 2:
                            unhandled.push("注意");
                            break;
                        case 3:
                            unhandled.push("严重");
                            break;
                    }
                }
            });
        });
        //console.log(unhandled);
        
        if ($("#unhandledTable").length != 0) { //定时请求要清除之前的表格或者提示框
            $("#unhandledTable").remove();
        }
        if ($("#Tips").length != 0) {
            $("#Tips").remove();
        }
        var unhandledTable = new Table(row, 6, "unhandledTable", "unhandledEventBox");
        unhandledTable.setTable("100%", "100%");
        
        $("#unhandledTable tr td").each(function(m){
        
            //m%6==0 && m!=0 ? $(this).html("<a href='../server/serverStatus.html?version="+url_params("version")+"&name="+unhandled[m]+"'>"+unhandled[m]+"</a>") : $(this).text(unhandled[m]); //主机添加链接（服务器明细状态）
            if (m != 0 && m != 1) {
                switch (m % 6) {
                    case 0:
                        $(this).html("<a href='../server/serverStatus.html?name=" + unhandled[m] + "&version=" + url_params("version") + "'>" + unhandled[m] + "</a>");
                        break;
                    case 1:
                        $(this).html("" + unhandled[m] + "<a href='#' id='" + chartId[id++] + "' class='monitorChart'><img src='../images/monitorEventChart.png' style='width:16px;height:16px;float:right;margin:0px 4px 0px 0px;'/></a>");
                        break;
                    default:
                        $(this).html(unhandled[m]);
                        break;
                }
            }
            else {
                $(this).html(unhandled[m]);
            }
            
            switch (unhandled[m]) {
                case "注意":
                    $(this).css({
                        "background": "#FEFF6F",
                        "-webkit-animation": ""
                    });
                    break;
                case "严重":
                    $(this).css({
                        "background": "#FF795F",
                        "-webkit-animation": "twinkling 1s infinite ease-in-out",
                        "-moz-animation": "twinkling 1s infinite ease-in-out"
                    });
                    break;
            }
        });
        
        $(".monitorChart").tooltip({
            delay: 0,
            showURL: false,
            top: -230,
            left: 20,
            bodyHandler: function(){
                //return $(this).children("img").attr("src", this.src);
                var Id = $(this).attr("id");
                var host = $(this).parent().prev().text();
                var urlImage = "http://" + domain + "/mmsapi" + ver + "/get/graph/@" + Id + "/" + host;
                //console.log(urlImage);
                var img = $("<img src='" + urlImage + "'/>");
                //$(".bar").insertAfter(img);
                $(this).children("img").css({
                    "border": "2px solid #0B6B04",
                    "padding": "0px"
                });
                return img;
            }
        });
    }
    
}


function Request(){
    var v = url_params("version");//version
    var domain = domainURI(document.location.href);
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/status/@monengine", "#MonitorServer0 td:eq(3)", "loading_small.gif", get_indexPro_Callback);
    //console.log(ajax);
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/status/@mdb", "#MonitorServer0 td:eq(4)", "loading_small.gif", get_mdb_Callback);
    
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/status/@health", "#serverBox_health", "loading_middle.gif", get_health_Callback);
    
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/server/@all", "#serverBox_all", "loading_middle.gif", get_server_all_Callback);
    
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/status/@eventsummary", "#serverBox_eventsummary", "loading_middle.gif", get_status_eventsummary_Callback);
    
    //get_data_notLoad(url, successCallback)
    get_data_ajax("http://" + domain + "/mmsapi" + v + "/get/event/@unhandled", "#unhandledEventBox", "loading_middle.gif", get_unhandled_Callback); //请求未处理事件问题列表
    //get_data_notLoad("http://211.136.105.207:8282/mmsapi"+v+"/get/event/@unhandled",get_unhandled_Callback); //请求未处理事件问题列表	 
    setTimeout(arguments.callee, 180000); //定时请求数据
    var date = new Date();
    var month = date.getMonth() + 1;
    var time = date.getFullYear() + "/" + month + "/" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes();
    $("#updateTime cite").text(time);
}


function contentHeight(){
    //contentIframe Height
    var notIframeHeight = $("#header").height() + $("#top_navigation").height() + $("#footer").height(), iframeHeight = document.documentElement.clientHeight - notIframeHeight;
    //console.log($("#right").height());
    $("#left").height(iframeHeight - 10);
    $("#right").height(iframeHeight - 10);
}

$(document).ready(function(){
    Request();
    show_hide_Table();
    // alertCloud();
});

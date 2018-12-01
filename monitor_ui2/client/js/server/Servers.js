var get_Servers = function(){
    var ver = url_params("version");
    var domain = domainURI(document.location.href);
    $.ajax({
        type: "get",
        url: "http://" + domain + "/mmsapi" + ver + "/get/serverGroup/@all",
        async: true,
        dataType: "json",
        beforeSend: function(){
            var load_div = $("<div id='loading'><img src='../images/loading_middle.gif'/></div>");
            $("#ServersBox").append(load_div);
        },
        success: function(json, textStatus, jqXHR){//如果调用php成功
            // console.log(json);
            var data = ["主机组", "在线数", "宕机数", "正常事件数", "注意事件数", "严重事件数", "操作"], row = 1;
            for (var i in json) {
                data.push(i);
                ++row;
                var add_array = 0;
                $.each(json[i], function(key, value){
                    if (key != 5) {
                        add_array += value;
                        switch (key) {
                            case 0:
                                data.push(value + "在线");
                                break;
                            case 1:
                                data.push(value + "宕机");
                                break;
                            case 2:
                                data.push(value + "个正常事件");
                                break;
                            case 3:
                                data.push(value + "个注意事件");
                                break;
                            case 4:
                                data.push(value + "个严重事件");
                                break;
                        }
                    }
                });
                //console.log(add_array);
                if (add_array == 0) { //在线和宕机之和为0，显示没有服务器!
                    for (var j = 0; j < 5; j++) {
                        data.pop();
                    }
                    data.push("该组下没有服务器")
                    for (var x = 0; x < 4; x++) {
                        data.push("");
                    }
                }
                $.each(json[i], function(k, v){
                    if (k == 5) { // 操作属性
                        //   console.log(v);
                        switch (v) {
                            case 0:
                                data.push("default");
                                break; //0不出操作按钮
                            case 1:
                                data.push("Modify|Delete");
                                break; //1 出删除和修改按钮
                        }
                    }
                });
            }
            //console.log(data);
            var table = new Table(row, 7, "Servers_table", "ServersBox");
            table.setTable("98%", "98%");
            $("#Servers_table tr td").each(function(m){
                switch (data[m]) {
                    case "Modify|Delete":
                        $(this).html("<a href='javascript:void(0)'><div id='modifyIcon'></div></a><a href='javascript:void(0)'><div id='deleteIcon'></div></a>");
                        break;
                    case "default":
                        $(this).html("");
                        break;
                    default:
                        $(this).html(data[m]);
                        break;
                }
            });
            
            $("#Servers_table tr td:contains('在线'):not(:first)").css({
                "background": "#CCFF99"
            }); //lime
            $("#Servers_table tr td:contains('宕机'):not(:first)").css({
                "background": "#FF795F"
            }); //red
            $("#Servers_table tr td:contains('正常事件'):not(:first)").css({
                "background": "#CCFF99"
            }); //lime
            $("#Servers_table tr td:contains('注意事件'):not(:first)").css({
                "background": "#FEFF5F"
            }); //yellow
            $("#Servers_table tr td:contains('严重事件'):not(:first)").css({
                "background": "#FF795F"
            }); //red
            $("#Servers_table tr td").each(function(n){
                var text = $(this).text();
                if (text == "0宕机" || text == "0个注意事件" || text == "0个严重事件") {
                    $(this).css({
                        "background": "#CCFF99"
                    }); //lime
                }
                else 
                    if (text == "0在线" || text == "0个正常事件") {
                        $(this).css({
                            "background": "#FF795F"
                        }); //red
                    }
            });
            
            //TablePage("#Servers",8);  
            /*if($(".page").length==0){
             TablePage("#Servers_table",15);
             }else{
             $(".page").remove();
             TablePage("#Servers_table",15);
             }*/
            $("#Servers_table tr td a #deleteIcon").each(function(m){ //Delete 操作
                //console.log($(this));
                $(this).bind("click", function(){
                    //console.log($(this).parent().parent().parent().children("tr td:nth-child(1)").text());
                    var servers_name = $(this).parent().parent().parent().children("tr td:nth-child(1)").text();
                    del_data("http://" + domain + "/mmsapi" + ver + "/delete/serverGroup/@self/" + servers_name);
                    get_Servers();
                    Tips("complete", "已经成功删除数据!");
                });
            });
            
            $("#Servers_table tr td a #modifyIcon").each(function(m){ //修改操作
                $(this).bind("click", function(){
                    //console.log($(this).parent().parent().children("tr td:nth-child(1)").text());
                    var servers_name = $(this).parent().parent().parent().children("tr td:nth-child(1)").text();
                    //window.open("../server/modifyServers.html?name=" + servers_name + "&version=" + ver, target = "_self");
                    location.href = "../server/modifyServers.html?name="+servers_name+"&version="+ver;
                    //$(this).attr("href","../servers/modifyServers.html?name="+servers_name);
                });
            });
        },
        complete: function(){
            $("#loading").remove();
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
};

$(document).ready(function(){
    get_Servers();
});

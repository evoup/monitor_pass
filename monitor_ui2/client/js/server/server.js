function get_server_allNum_Callback(json, textStatus, jqXHR){ //获取宕机，在线，未监控数据
 if(json==null){
    console.log("json null");
 }else{ 
   var row=1,num=0,count=0,notMonitorNum=0;
   for(var i in json){
     if(i){++row;}
     $.each(json[i],function(k,v){
      // console.log(v);
      if(k==0){
        //console.log(v);
        switch(v){
          case "0" : ++num;break;
          case "1" : ++count;break;
          case "2" : ++count;break;
          case "3" : ++count;break;
          case "4" : ++count;break;
					case "5" : ++notMonitorNum;break;
           }
      }else{
      }
      });
      }
      //console.log(row);
      var all=["宕机","在线","未监控"],allObj=$("#MonitorClient0");
      all.push(num);
      all.push(count);
			all.push(notMonitorNum);
     //console.log(allObj.length);
      if(allObj.length==0){
         var t=new Table(2,3,"MonitorClient0","serverBox_all");
         t.setTable("100%","100%");
       }
      $("#MonitorClient0 tr td").each(function(m){
         //$(this).html(all[m]);
          switch(m){
					   case 0 : $(this).html(all[m]);break;
						 case 1 : $(this).html(all[m]);break;
						 case 2 : $(this).html(all[m]);break;
						 case 3 : $(this).html("<a href='../server/serverList.html?version="+url_params("version")+"&status=down'>"+all[m]+"</a>");break;
						 case 4 : $(this).html("<a href='../server/serverList.html?version="+url_params("version")+"&status=up' >"+all[m]+"</a>");break;
					   case 5 : $(this).html("<a href='../server/serverList.html?version="+url_params("version")+"&status=unmonitored' >"+all[m]+"</a>");break;
         } 
			});
      if(num>0){
           $("#MonitorClient0 td:eq(3)").css({"background":"#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
      }else if(num==0){
           $("#MonitorClient0 td:eq(3)").css({"background":"#CCFF99","-webkit-animation":""});
      }

      if(count>0){
          $("#MonitorClient0 td:eq(4)").css({"background":"#CCFF99","-webkit-animation":""});
      }else if(count==0){
           $("#MonitorClient0 td:eq(4)").css({"background":"#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
       }

		   $("#MonitorClient0 td:eq(5)").css({"background":"#DFDFDF","-webkit-animation":""});
  }

}



function get_server_all_Callback(json, textStatus, jqXHR){ //获取服务器列表数据
    if(json==null){
       //console.log("服务器传的数据为空");
			  if($("#unhandledTable").length!=0){ //定时请求要清除之前的表格或者提示框
            $("#unhandledTable").remove();
        }
        if($("#Tips").length!=0){
            $("#Tips").remove();
        }
       var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
       tips.appendTo($("#clientBox"));
       Tips("complete","无相应的服务器列表信息!"); 
    }else{
         //console.log(json);
         var row=1,summary=["主机","状态","IP","上次上传时间","监控节点","总计在线时间"],num=0,count=0,notMonitorNum=0;
           for(var i in json){
             //summary.push(i);
            summary.push(i);
            if(i){++row;}
						 $.each(json[i],function(k,v){
              // console.log(v);
							 if(k==0){
								//console.log(v);
							  switch(v){
								 case "0" : v="宕机";++num;break; //宕机计数
								 case "1" : v="在线";++count;break; //在线计数
								 case "2" : v="注意";++count;break;
								 case "3" : v="严重";++count;break;
								 case "4" : v="注意+严重";++count;break;
								 case "5" : v="未监控";++notMonitorNum;break;//未监控计数
								} 
							  summary.push(v);
							 }else{
							  summary.push(v);
							 }
          });
        }
				 var version=url_params("version"),clientObj=$("#MonitorClient2");
				 //console.log(clientObj.length);
				 if(clientObj.length!=0 || $("#Tips").length!=0){
				    clientObj.remove();
						$("#Tips").remove();
						$("input[name='search']").val("");
          }
         var table=new Table(row,6,"MonitorClient2","clientBox");
         table.setTable("100%","100%");
				 $("#MonitorClient2 tr td").each(function(i){

					 i%6==0 && i!=0 ? $(this).html("<a href='serverStatus.html?name="+summary[i]+"&version="+version+"'style='margin-right:10px;display:inline-block;padding-top:5px;'>"+summary[i]+"</a><a href='../monitor/monitorEvent.html?selector="+summary[i]+"&version="+version+"'><cite class='linkMonitor'></cite></a>") :  $(this).html(summary[i]); //添加服务器明细和监控事件的链接

						switch(summary[i]){
							  case "在线"     : $(this).css({"background" : "#B2FF5F","-webkit-animation":""});break;
                case "宕机"   : $(this).css({"background" : "#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});break;
                case "注意": $(this).css({"background" : "#FFFF00","-webkit-animation":""});break;
								case "严重": $(this).css({"background" : "#FF7F00","-webkit-animation":""});break;
								case "注意+严重": $(this).css({"background":"#FF3030","-webkit-animation":""});break;
								case "未监控" : $(this).css({"background" : "#E0E0E0","-webkit-animation":""});break;
						}
         });
				 /*if($(".page").length==0){  //table 分页
           TablePage("#MonitorClient2",15);
          }else{
					 $(".page").remove();
					 TablePage("#MonitorClient2",15);
					}*/
				 }

}



function get_status_eventsummary_Callback(json,textStatus,jqXHR){//获取事件状态数据
   //console.log(json);
   if(json==null){
        console.log("服务器传的数据为空");
    }else{
        var data=["严重事件","注意事件","正常"],str=[];
        for(var m in json){
         if(m=="warning"){
              str[0]=json[m];continue;
         }else if(m=="caution"){
              str[1]=json[m];continue;
         }else if(m=="ok"){
              str[2]=json[m];continue;
             }
        }
     for(var i in str){
       data.push(str[i]);
     }
		 var obj=$("#MonitorClient1");
		 if(obj.length!=0){
		   obj.remove();
		 }
		 var table=new Table(2,3,"MonitorClient1","serverBox_eventsummary");
		 table.setTable("100%","100%");
     $("#MonitorClient1 tr td").each(function(m){
         //$(this).html(data[m]);
         switch(m){
             case 0 : $(this).html(data[m]);break;
             case 1 : $(this).html(data[m]);break;
             case 2 : $(this).html(data[m]);break;
             case 3 : $(this).html("<a href='../monitor/monitorEvent.html?version="+url_params("version")+"&eventStatus=warning'>"+data[m]+"</a>");break;
             case 4 : $(this).html("<a href='../monitor/monitorEvent.html?version="+url_params("version")+"&eventStatus=caution'>"+data[m]+"</a>");break;
             case 5 : $(this).html("<a href='../monitor/monitorEvent.html?version="+url_params("version")+"&eventStatus=ok' >"+data[m]+"</a>");break;      
             
      }

     });
      /*for(var x in str){
         if(str[x]==0){
           $("#MonitorClient1 td:eq("+(x+4)+")").css({"background":"#CCFF99"});
         }else{
           $("#MonitorClient1 td:eq("+(x+4)+")").css({"background":"#FF795F"});
        }
       } */
      if(str[0]==0){
            $("#MonitorClient1 td:eq(3)").css({"background":"#CCFF99","-webkit-animation":""});
       }else {
            $("#MonitorClient1 td:eq(3)").css({"background":"#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
        }
       if(str[1]==0){
            $("#MonitorClient1 td:eq(4)").css({"background":"#CCFF99"});
        }else{
            $("#MonitorClient1 td:eq(4)").css({"background":"#FEFF6F"});
          }
       if(str[2]==0){
           $("#MonitorClient1 td:eq(5)").css({"background":"#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
        }else{
           $("#MonitorClient1 td:eq(5)").css({"background":"#CCFF99","-webkit-animation":""});
       }
  }
}
function requestServerData(){
  var version=url_params("version"),status=url_params("status");//down /online
  var domain=domainURI(document.location.href); 
	 get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all","#serverBox_all","loading_small.gif",get_server_allNum_Callback) 
   
   status==null ?	get_HostData_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all","#clientBox","loading_small.gif",get_server_all_Callback) : get_HostData_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all"+status,"#clientBox","loading_small.gif",get_server_all_Callback); //

	get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/status/@eventsummary","#serverBox_eventsummary","loading_small.gif",get_status_eventsummary_Callback);
  setTimeout(arguments.callee,180000); //定时请求数据
}

function get_HostData_ajax(url,load_parent_id,load_image,successCallback){
     $.ajax({
        type : "get",
         url :  url,
       async :  true,
    dataType : "json",
  beforeSend : function(){
               var load_div=$("<div id='loading'><img src='../images/"+load_image+"'/></div>");
               $(load_parent_id).append(load_div);
               },
     success : successCallback,
    complete : function(jqXHR, textStatus){
            //if(jqXHR.status==200){
                $("#loading").remove();
            //  }
               },
      error :  function(jqXHR, textStatus, errorThrown){
                 $("#clientBox").children("table").remove();
                 if($("#Tips").length!=0){
                    $("#Tips").remove();
                  }
                 var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
                 tips.appendTo("#clientBox");
                 switch(jqXHR.status){
                   case 404 : Tips("complete","列表不存在相应的数据!");break;
                   case 500 : Tips("alert","服务器出错");break;
                  }
                }
   });  

}



function  serverAjax(_url){
  $.ajax({
         type  : "get",
         url   : _url,
         async : true,
       success : get_server_all_Callback,
         error : function(jqXHR, textStatus, errorThrown){
                 $("#clientBox").children("table").remove();
                 if($("#Tips").length!=0){
                     $("#Tips").remove();
                  }
                 var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
                 tips.appendTo("#clientBox");

                 switch(jqXHR.status){
                  case 404 : Tips("alert","数据不存在!");break;
                  case 500 : Tips("alert","服务器出错!");break;
                }
           }
         });

}
	
$(document).ready(function(){
  requestServerData();
	show_hide_Table();
  
	var version=url_params("version"),status=url_params("status");
  var domain=domainURI(document.location.href);
	if(status!=null){ //是否有筛选器存在
    if($("#selector").length!=0){
       $("#selector").remove();
		 }  
		 $("<p id='selector' style='clear:both;font:normal normal 12px arial,sans-serif;margin:0px 0px 0px 0px;padding:0px;line-height:24px;color:#4D89F9;float:left;'>筛选器：<cite style='font-style:normal;'>"+status+"</cite><img src='../images/close_small.png' style='vertical-align:middle;margin-left:4px;cursor:pointer;'/></p>").insertBefore($("input[name='search']").parent("p"));
      //_url="http://211.136.105.207:8282/mmsapi"+version+"/get/server/@all"+status;

     $("#selector img").click(function(){
       window.location.href="serverList.html?version="+version;
     });
   //}else if(status==null){
      //_url="http://211.136.105.207:8282/mmsapi"+version"/get/server/@all";
   }
	

 
  $("#search").click(function(){  //search服务器列表功能
    var find=$("input[name='search']").val();
		//console.log(find);
    if($("#Tips").length!=0){
       $("#Tips").hide();
     }
    if(find!="" && status==null ){ //search
       serverAjax("http://"+domain+"/mmsapi"+version+"/get/server/@all/"+find);
    }else if(find!="" && status!=null){//search +status
		   serverAjax("http://"+domain+"/mmsapi"+version+"/get/server/@all"+status+"/"+find);
		}else if(find=="" && status!=null){//status
       serverAjax("http://"+domain+"/mmsapi"+version+"/get/server/@all"+status);
    }else if(find=="" && status==null){
	     serverAjax("http://"+domain+"/mmsapi"+version+"/get/server/@all");		 
	  }
	});
});


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
           $("#MonitorClient0 td:eq(3)").css({"background":"#CCFF99","-webkit-animation" : ""});
        }
      if(count>0){
           $("#MonitorClient0 td:eq(4)").css({"background":"#CCFF99","-webkit-animation" : ""});
      }else if(count==0){
           $("#MonitorClient0 td:eq(4)").css({"background":"#FF795F","-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
       }

       $("#MonitorClient0 td:eq(5)").css({"background":"#DFDFDF"});
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
 if(str[0]==0){
            $("#MonitorClient1 td:eq(3)").css({"background":"#CCFF99","-webkit-animation" : ""});
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
           $("#MonitorClient1 td:eq(5)").css({"background":"#CCFF99","-webkit-animation" : ""});
       }
  }
}

function requestHostEventData(version){
   var domain=domainURI(document.location.href);
	 get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all","#serverBox_all","loading_small.gif",get_server_allNum_Callback);
   
   get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/status/@eventsummary","#serverBox_eventsummary","loading_small.gif",get_status_eventsummary_Callback);
     

}

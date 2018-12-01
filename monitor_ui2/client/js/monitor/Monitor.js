function SuccessCallback(json, textStatus, jqXHR){//getMonitorEventAll
  if(json==null){
      console.log("服务器传的数据为空");
    //Tips("complete","没有相关监控事件数据!");
  }else{
     //console.log(json);
     var host=[],row=1,summary=["主机","监控事件","状态","持续时间","上次检查","状态信息"],flag=false; //是否是第一组主机名
     var ver=url_params("version"),chartId=[],id=0;
		 var domain=domainURI(document.location.href);
		 $.each(json,function(key,value){
        if(key=="records"){
           //console.log(value);
           if(value==null){
              //console.log(value);
							if($("#MonitorEvent0").length!=0){
							   $("#MonitorEvent0").remove();
							}

							if($("#Tips").length!=0){
								  $("#Tips").remove();
								 }

              var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
              tips.insertAfter($("#eventList"));
              Tips("complete","没有相关监控事件数据!");
							$("#page_info").remove();
           }else{ 
            $.each(value,function(k,v){
           //console.log(v);
            /*k==0? flag=true : flag=false;*/
               ++row;
             $.each(v,function(m,n){
                  //console.log(n);
                   /*m==0 && flag==false ? summary.push(""):summary.push(n);*/
									 
									 m!=2 ? summary.push(n) : chartId.push(n); 	
                   
									 if(m==3){
                     //console.log(n);
                     summary.pop();
                     switch(n){
                       case "0" : summary.push("down");break;
                       case "1" : summary.push("正常");break;
                       case "2" : summary.push("注意");break;
                       case "3" : summary.push("紧急");break;
                    }
                   }
                });
              });
				 var eventObj=$("#MonitorEvent0");
         if(eventObj.length!=0 || $("#Tips").length!=0){
            eventObj.remove();
						$("#Tips").remove();
						$("input[name='search']").val("");
				 }
         var table=new Table(row,6,"MonitorEvent0","monitorEventBox");
         table.setTable("100%","100%");
            //pager("http://211.136.105.207:8282/mmsapi"+ver+"/get/event/@all",ver,"#monitorEventBox");
         $("#MonitorEvent0 tr td").each(function(m){

						   //m%6==0 && m!=0 ? $(this).html("<a href='../server/serverStatus.html?name="+summary[m]+"&version="+url_params("version")+"'>"+summary[m]+"</a>") :  $(this).html(summary[m]);//加服务器明细链接 		
               if(m!=0 && m!=1){
							   switch(m%6){
								   case 0  : $(this).html("<a href='../server/serverStatus.html?name="+summary[m]+"&version="+url_params("version")+"'>"+summary[m]+"</a>");break;
									 case 1  : $(this).html(""+summary[m]+"<a href='#' id='"+chartId[id++]+"' class='monitorChart'><img src='../images/monitorEventChart.png' style='width:16px;height:16px;float:right;margin:0px 4px 0px 0px;'/></a>");break;
								   default : $(this).html(summary[m]);break;
 								 }
							 }else{
							     $(this).html(summary[m]);
							 }

							 switch(summary[m]){
                  //case "down":$(this).css({"background":"#0000ff"});break;
                  case "正常":$(this).css({"background":"#B2FF5F","-webkit-animation":""});break;
                  case "注意":$(this).css({"background":"#FEFF5F","-webkit-animation":""});break;
                  case "紧急":$(this).css({"background":"#FF795F","-webkit-animation":"twinkling 0.8s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});break;
                }
           });

					$("#loading").remove(); 
        }
       }
		  });
		 if(url_params("eventStatus")==null && json.records!=null){
      
			 var pagerObj=$("#page_info");
        if(pagerObj.length!=0){
           pagerObj.remove();
        }
        //调用Ajax分页功能
        url_params("selector")==null ? $("input[name='search']").val()=="" ? pager("http://"+domain+"/mmsapi"+ver+"/get/event/@all",ver,"#monitorEventBox") : pager("http://"+domain+"/mmsapi"+ver+"/get/event/@all/"+$("input[name='search']").val(),ver,"#monitorEventBox") : pager("http://"+domain+"/mmsapi"+ver+"/get/event/@all/"+url_params("selector"),ver,"#monitorEventBox"); 
			 
			 $.each(json,function(key,value){    
		    if(key="page_info"){
          $.each(value,function(k,v){
               //console.log();
              switch(k){
                 case "total_pages"    : $("#total_page").text(v);break;
                 case "current_page"   : $("input[name='current_page']").val(v);break;
                 case "line_per_page"  : $("select[name='line_per_page']").children("option[value='"+v+"']").attr("selected",true);break;
                 //case "next_page"     : $("input[name='next_page']").val(v);break;
                 //case "prev_page"     : $("input[name='prev_page']").val(v);break;
                }
            });
         }
        });
		 }
    // console.log(domain);
		$(".monitorChart").tooltip({
          delay : 0,
        showURL : false,
				    top : -180,
					 left : 20,
     bodyHandler: function(){
               //return $(this).children("img").attr("src", this.src);
              var Id=$(this).attr("id");
              var host=$(this).parent().prev().text();
							var urlImage= "http://"+domain+"/mmsapi"+ver+"/get/graph/@"+Id+"/"+host;
              //console.log(urlImage);
							var img=$("<img src='"+urlImage+"'/>");
              //$(".bar").insertAfter(img);
              $(this).children("img").css({"border":"2px solid #0B6B04","padding":"0px"});
							return img;
            }
         });
	  // });
	
	}

}


function monitorInfo(){
   var ver=url_params("version"),obj=$("#MonitorEvent0"),_url;
	 var domain=domainURI(document.location.href);
	 var selector=url_params("selector"),eventStatus=url_params("eventStatus"),selectorContent;
   //console.log(eventStatus); 
	 
	 if(selector!=null ||eventStatus!=null /*&& $("#selector").length==0*/){ //是否有筛选器存在
      selector==null ? selectorContent=eventStatus : selectorContent=selector; 
      if($("#selector").length!=0){
			   $("#selector").remove();
			}
			$("<p id='selector' style='clear:both;font:normal normal 12px arial,sans-serif;margin:0px 0px 0px 0px;padding:0px;line-height:24px;color:#4D89F9;float:left;'>筛选器：<cite style='font-style:normal;'>"+selectorContent+"</cite><img src='../images/close_small.png' style='vertical-align:middle;margin-left:4px;cursor:pointer;'/></p>").insertBefore($("#eventList").prev());
     
			eventStatus!=null ? _url="http://"+domain+"/mmsapi"+ver+"/get/event"+eventStatus+"/@all" : _url="http://"+domain+"/mmsapi"+ver+"/get/event/@all/"+selector;
      
     $("#selector img").click(function(){
       window.location.href="monitorEvent.html?version="+ver;
     }); 
   }else if(url_params("selector")==null){
      _url="http://"+domain+"/mmsapi"+ver+"/get/event/@all";
   }
  
	 
	 $.ajax({
        type: "get",
        url : _url,
        async: true,
        dataType:"json",
        beforeSend:function(){
				 if(obj.length==0){
				  	var load_div=$("<div id='loading' style='margin-left:50px;'><img src='../images/loading_middle.gif'/></div>");
            $("#monitorEventBox").append(load_div);
          }
				 },
        success: SuccessCallback,
        complete:function(jqXHR, textStatus){
          if(obj.length==0 || jqXHR.status==200){
					  $("#loading").remove();
					 }	
         },
        error:function(jqXHR, textStatus, errorThrown){
          $("#monitorEventBox").children("table").remove();
          
          if($("#Tips").length!=0){
             $("#Tips").remove();
          }
 
					var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
          tips.appendTo("#monitorEventBox"); 
					switch(jqXHR.status){
            case 404 : Tips("complete","列表没有相应的数据!");break;
            case 500 : Tips("alert","服务器出错!");break;
          }
       }

   });
  setTimeout(arguments.callee,180000); //定时请求数据
}

function eventAjax(_url){
  $.ajax({
         type  : "get",
         url   : _url,
         async : true,
       success : SuccessCallback,
         error : function(jqXHR, textStatus, errorThrown){
                 
                 $("#monitorEventBox").children("table").remove();
                 if($("#Tips").length!=0){
                     $("#Tips").remove();
                  }
                 var tips=$("<div id='Tips' style='width:97%;'><div></div><cite></cite></div>");
                 tips.appendTo("#monitorEventBox"); 
					       
                 switch(jqXHR.status){
                  case 404 : Tips("alert","不存在相应的数据!");break;
                  case 500 : Tips("alert","服务器出错!");break;
                  }  
           }
         });  

}

$(document).ready(function(){
  // showMenu();  
  var version=url_params("version"); 
  var domain=domainURI(document.location.href);	
	monitorInfo();
  show_hide_Table();

	requestHostEventData(version);
  //console.log(url_params("selector")); 
  /*if(url_params("selector")!=null){
	 var selector=url_params("selector");
	 console.log($("#monitorEventBox").prev());
	 $("<cite id='selector' style='font:normal normal 12px arial,sans-serif;margin-left:40px;color:#000000;'>筛选器：<cite>"+selector+"</cite></cite>").appendTo($("#monitorEventBox").prev());
	} */
	
////////////////////////////////
var filterConf = {
	url:"http://"+domain+"/mmsapi"+version+"/get/metric/@all",
	noLoading:1
};
var filterCall = function(e, json){
    var $th = $(this), html;
    json.unshift({
        'all monitor metrics': ''
    });
    html = template('t_a', {
        list: json
    });
    $th.html(html);
};


$('#filter').bind('ajax.success',filterCall).getData(filterConf).change(function(){
	var p = $(this).val();
	if (p) {
		eventAjax("http://" + domain + "/mmsapi" + version + "/get/event/@self/" + p);
	}else{
		monitorInfo();
	}
});	
///////////////////////////////		
	
  $("#search").click(function(){  //search服务器列表功能
    var find=$("input[name='search']").val(),_request;
    //console.log(find);
    if($("#Tips").length!=0){
       $("#Tips").hide();
     }
    if(find!="" && url_params("eventStatus")==null){ //搜索框
		   eventAjax("http://"+domain+"/mmsapi"+version+"/get/event/@all/"+find);
    }else if(find!="" && url_params("eventStatus")!=null){ //搜索框+事件状态
       eventAjax("http://"+domain+"/mmsapi"+version+"/get/event"+url_params("eventStatus")+"/@all/"+find); 
		}else if(find=="" && url_params("eventStatus")!=null){ //事件状态
	     eventAjax("http://"+domain+"/mmsapi"+version+"/get/event"+url_params("eventStatus")+"/@all");  
		}else if(find=="" && url_params("eventStatus")==null){
		 	eventAjax("http://"+domain+"/mmsapi"+version+"/get/event/@all");
	  }
  });


});

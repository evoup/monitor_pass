function get_server_status_callback(json, textStatus, jqXHR){
 if(json==null){
   console.log("json null");
 }else{
   //console.log(json);
	 $("#serverdetail p:first").html("<cite>"+json.host+"</cite><cite style='margin-left:4px;color:#0000ff;'></cite>");
   $("#serverdetail p:first cite:last").text("("+json.desc+")");
	 json.group=="" ? $("#serverdetail p:last cite").text("无"):$("#serverdetail p:last cite").text(json.group);
	 
	 $("#serverInfo .tipsContent").text(json.post_info);
	 $("#serverAddr cite").text(json.addr);
	 
	 if(json.status=="1"){
	   $("#tabs2").show();
	 	 $("#serverstatus cite:last").text("在线");
     $("#serverInfo").attr("class","ok");
		 //$("#serverInfo img").attr("src","../images/right_icon.png");
	   lineChart();//调用线性图表
	    
	 }else if(json.status=="0" || json.status=="5"){
	   
		 json.status=="0" ? $("#serverstatus cite:last").text("宕机") : $("#serverstatus cite:last").text("未监控");
     json.status=="0" ? $("#serverInfo").attr("class","fail") : $("#serverInfo").attr("class","unmonitored");
     //json.status=="0" ? $("#serverInfo img").attr("src","../images/wrong_icon.png") :  $("#serverInfo img").attr("src","../images/servers/unmonitored.png");
		 //$("#tabs2").show();
	   //$("#container_loadAverage").children().remove();
		 //$("#container_tcp").children().remove();
	 
	 }
	 $("#runtime cite:last").text(json.summary_uptime);
	 $("#update cite:last").text(json.last_check);
	 $("#clientVersion cite:last").text(json.client_ver);
   //$("#tabs5_content textarea[name='memo']").val(json.memo); //备注
 }

}

function get_server_detail_callback(json, textStatus, jqXHR){  //get服务器明细状态数据
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
	  $.each(json,function(key,value){
		  var text=key+"：&nbsp&nbsp&nbsp&nbsp"+value;
		  //$("#tabs3_content p").insertAfter("<p>"+text+"</p>"); 
		  //$("<p>"+text+"</p>").insertAfter($("#tabs3_content p:last"));
		  $("<p><cite style='display:inline-block;width:10%;text-align:right;margin-right:8px;'>"+key+"</cite>:<cite style='margin-left:8px;'>"+value+"</cite></p>").appendTo($("#detailStatus"));

		});
	}
}


$(document).ready(function(){
  var serverId=url_params("name"),version=url_params("version");
	var domain=domainURI(document.location.href);
	//console.log(serverId);
	get_data_notLoad("http://"+domain+"/mmsapi"+version+"/get/server/@self/"+serverId,get_server_status_callback);//tab1
	get_data_notLoad("http://"+domain+"/mmsapi"+version+"/get/server/@self_detail/"+serverId,get_server_detail_callback);//tab3
  
	/*$("#memo_submit").click(function(){
	  var memoVal=$("#tabs5_content textarea[name='memo']").val();
		//console.log(memoVal);
   	$.ajax({
      type : "post",
       url : "http://211.136.105.207:8282/mmsapi"+version+"/update/server/@self_desc/"+serverId,
     async : false,
      data : {memo : memoVal},
      success: function(data, textStatus, jqXHR){
          if(jqXHR.status==205){
						Tips("complete","添加备注信息成功");//显示成功提示框
					}        
       },
      error: function(jqXHR, textStatus, errorThrown){
        switch(jqXHR.status){
           case 400 : Tips("alert","没有找到相关数据");break;//显示出错提示框
           case 500 : Tips("alert","数据库出错");break;
          }       
 			}
     }); 
	});*/
	$("#modifyIcon").click(function(){ //修改服务器配置
	  //console.log($("#serverdetail p:first cite:first").text());
		var hostname=$("#serverdetail p:first cite:first").text();  //serverId
    window.location.href="../server/modifyServer.html?version="+version+"&hostname="+hostname;	
	});

  /*$("img[src='../images/Delete.png']").click(function(){  //删除服务器
	  //console.log("delete");
		var serverId=$("#serverdetail p:first cite:first").text(); //serverId
    $.ajax({
         type : "get",
         url  : "http://211.136.105.207:8282/mmsapi"+version+"/delete/server/@self/"+serverId,
        async : false,
      success : function(data, textStatus, jqXHR){
        if(jqXHR.status=="200"){
          Tips("complete","删除服务器成功");
          }
       },
        error : function(jqXHR, textStatus, errorThrown){
          switch(jqXHR.status){
            case 400 : Tips("alert","数据不存在");break;
            case 500 : Tips("alert","服务器出错");break;
            case 404 : Tips("alert","删除服务器失败");break;   
       }
      }
     });
 	  
	});*/
});

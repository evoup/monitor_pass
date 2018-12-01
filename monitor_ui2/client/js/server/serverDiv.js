function get_server_allDiv_Callback(json, textStatus, jqXHR){ //获取服务器列表数据
    if(json==null){
       console.log("服务器传的数据为空");
    }else{
       //console.log(json);
       var version=url_params("version");
       $.each(json,function(key,value){
         var serverId=key.substring(0,6);
         //console.log(serverId);  
         $("<li><a><!--img src='../images/serverDiv.png'/--><cite>"+serverId+"</cite></a><div class='tooltip'><div class='serverDetail' id='"+key+"'><div class='serverTitle'>"+key+"</div></div><a href='serverStatus.html?name="+key+"&version="+version+"' style='text-decoration:underline;color:#FF0000;float:right;'>&#187more</a></div></li>").appendTo($(".serverList ul"));
         
				 //console.log(value);
         var detailTitle=["状态","IP","上次上传时间","监控节点","总计在线时间"];
			   $.each(value,function(k,v){
            if(k==0){
              switch(v){
               case "0" : $("#"+key+"").parent().parent().css({"background":"#9F0000","border":"1px solid #9F0000"/*,"-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"*/});v="DOWN";break;
               case "1" : $("#"+key+"").parent().parent().css({"background":"#CCFF99","border":"1px solid #8CBF59"});v="UP";break;
							 case "2" : $("#"+key+"").parent().parent().css({"background":"#FFFF00","border":"1px solid #DFDF00"});v="注意";break;
							 case "3" : $("#"+key+"").parent().parent().css({"background":"#FF7F00","border":"1px solid #DF5F00"});v="严重";break;
               case "4" : $("#"+key+"").parent().parent().css({"background":"#FF3030","border":"1px solid #FF1010"});v="严重+注意";break;
							 case "5" : v="未监控";break;
              }
            }
            $("<p>"+detailTitle[k]+"：<cite>"+v+"</cite></p>").appendTo($(".serverDetail#"+key+""));
         });  
			 });
      
        $(".serverList ul li a").tooltip({opacity:0.9});
		
		
		}
}


function  serverAjax(_url){
  $.ajax({
         type  : "get",
         url   : _url,
         async : true,
       success : get_server_allDiv_Callback,
         error : function(jqXHR, textStatus, errorThrown){
                 /*$("#clientBox").children("table").remove();
                 if($("#Tips").length!=0){
                     $("#Tips").remove();
                  }
                 var tips=$("<div id='Tips' style='width:92%;'><div></div><cite></cite></div>");
                 tips.appendTo("#clientBox");*/

                 switch(jqXHR.status){
                  case 404 : Tips("alert","数据不存在!");break;
                  case 500 : Tips("alert","服务器出错!");break;
                }
           }
         });

}


$(document).ready(function(){
   var version=url_params("version"),status=url_params("status");
   var domain=domainURI(document.location.href);

   //serverAjax("http://"+domain+"/mmsapi"+version+"/get/server/@all"); 
   get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all",".serverList","loading_middle.gif",get_server_allDiv_Callback);

});

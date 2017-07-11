
function getCommonSettingSuccessCallback(json, textStatus ,jqXHR){
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
		$.each(json,function(key,value){
		  switch(key){
			  case "engine" : $("input[name='watchdogUrl']").val(value.watchdogUrl);/*getEngineInfo(value);*/break;
				case "client" : /*$("input[name='keepAliveOvertimeSec']").val(value.keepAliveOvertimeSec);*/$("input[name='sleepSecPerReq']").val(value.sleepSecPerReq);break;
        case "daily"  : getDailyInfo(value);break;			
			}
		
		});
   	
		$("input[name='sendMail']").click(function(){
		   $("input[name='sendMail']").attr("checked")==true ? $("input[name='sendMail']").val(1) : $("input[name='sendMail']").val(0);
		});
		//$("input[name='sendMail']").attr("checked")==true ? $("input[name='sendMail']").val(1) : $("input[name='sendMail']").val(0);
	
	}

}

/*function getEngineInfo(data){
   //console.log(data);
	 var info=["监控引擎","状态","上次在线","运行时间"],row=1, count, upNum=0;
	 $.each(data,function(key,value){
	    info.push(key);
      ++row;
      value.status==1 ? info.push("UP"): info.push("DOWN");
      info.push(value.lastOnline);
      info.push(value.totalOnline);
	 });
   //console.log(info);
   var tableObj=$("#commonSettingTable");
   if(tableObj.length==0){
     var t=new Table(row, 4, "commonSettingTable", "commonSettingBox");
     t.setTable("100%","100%");
   }
   $("#commonSettingTable tr td").each(function(i){
      $(this).text(info[i]);
      switch(info[i]){
        case "UP"   : $(this).css({"background":"#CCFF99"});++upNum;break;
        case "DOWN" : $(this).css({"background":"#FF795F","-webkit-animation":"twinkling 0.5s ease-in-out"});break;   
      }                
   });
	 count=row-1;
	 $("#commonSettingBox").prev().children("cite").html(upNum+"&nbsp/&nbsp"+count);
}*/


function getDailyInfo(data){
   for(var i=0;i<=23;i++){ //sendHour
    i<=9 ? $("<option value='0"+i+"'>0"+i+"</option>").appendTo($("select[name='sendHour']")) : $("<option value='"+i+"'>"+i+"</option>").appendTo($("select[name='sendHour']")); 
   }
   
   for(var j=0;j<=59;j++){
    j<=9 ? $("<option value='0"+j+"'>0"+j+"</option>").appendTo($("select[name='sendMin']")) : $("<option value='"+j+"'>"+j+"</option>").appendTo($("select[name='sendMin']")); //sendMin
   
    j<=9 ? $("<option value='0"+j+"'>0"+j+"</option>").appendTo($("select[name='sendSec']")) : $("<option value='"+j+"'>"+j+"</option>").appendTo($("select[name='sendSec']")); //sendSec
   }

   data.sendMail==1 ? $("input[name='sendMail']").attr("checked",true) : $("input[name='sendMail']").attr("checked",false);
   data.sendMail==1 ? $("input[name='sendMail']").val(1) : $("input[name='sendMail']").val(0);

   data.sendHour<=9 ? $("select[name='sendHour']").val("0"+data.sendHour) : $("select[name='sendHour']").val(data.sendHour);
   
   data.sendMin<=9  ? $("select[name='sendMin']").val("0"+data.sendMin) : $("select[name='sendMin']").val(data.sendMin);

   data.sendSec<=9  ? $("select[name='sendSec']").val("0"+data.sendSec) : $("select[name='sendSec']").val(data.sendSec); 
}

function IsURL(url){
  /*var strRegex = "^((https|http|ftp|rtsp|mms)?://)"
        + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" //ftp的user@
        //+ "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184
        + "(((([0-9]|1[0-9]{2}|[1-9][0-9]|2[0-4][0-9]|25[0-5])[.]{1}){3}([0-9]|1[0-9]{2}|[1-9][0-9]|2[0-4][0-9]|25[0-5]))" 
	      + "|" // 允许IP和DOMAIN（域名）
        + "([0-9a-z_!~*'()-]+\.)*" // 域名- www.
        + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名
        + "[a-z]{2,6})" // first level domain- .com or .museum
        + "(:[0-9]{1,4})?" // 端口- :80
        + "((/?)|" // a slash isn't required if there is no file name
        + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";*/
	 var strRegex = "^((https|http):\/\/)?"
     + "(((([0-9]|1[0-9]{2}|[1-9][0-9]|2[0-4][0-9]|25[0-5])[.]{1}){3}([0-9]|1[0-9]{2}|[1-9][0-9]|2[0-4][0-9]|25[0-5]))" // IP形式的URL- 199.194.52.184
     + "|"
     + "([0-9a-zA-Z\u4E00-\u9FA5\uF900-\uFA2D-]+[.]{1})+[a-zA-Z-]+)" // DOMAIN（域名）形式的URL
     + "(:[0-9]{1,4})?" // 端口- :80
     + "((/?)|(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?)$"; 
   var re=new RegExp(strRegex);
  // console.log(re.test(url));
	 return re.test(url);


 } 

$(document).ready(function(){
 var version=url_params("version");
 var domain=domainURI(document.location.href);
 get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/generic_setting/@self", "commonSettingBox", "../images/loading_middle.gif",getCommonSettingSuccessCallback);

 //console.log(IsURL("http://www.baid.com/monitorv2watchdog/watchDog.php"))	 
 /*$("#resetNode").click(function(){ //重置节点
	 $.ajax({
          type: "get",
          url : "http://"+domain+"/mmsapi"+version+"/delete/generic_setting/@server",
          async: true,
          dataType:"json",
          success: function(json, textStatus, jqXHR){//如果调用php成功
            if(jqXHR.status==200){
               get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/generic_setting/@self", "commonSettingBox", "../images/loading_middle.gif",getCommonSettingSuccessCallback);
               Tips("complete","重置节点成功!"); 
          } 
           },
		      error: function(jqXHR, textStatus, errorThrown){
		          switch(jqXHR.status){
               case 400 : Tips("alert" ,"数据未找到");break;
               case 500 : Tips("alert" ,"服务器出错");break;
               case 409 : Tips("alert" ,"用户名冲突");break;

            }  
		       }
          });  
   
 
 });*/

 
 $("#common_settings").submit(function(){
   var watchdogUrl=$("input[name='watchdogUrl']").val(),
	     sleepSecRep=$("input[name='sleepSecPerReq']").val(),
	     //keepAliveOvertimeSec=$("input[name='keepAliveOvertimeSec']").val(),
			 sendMail=$("input[name='sendMail']").val(),
			 sendHour=$("select[name='sendHour']").val(),
			 sendMin=$("select[name='sendMin']").val(),
			 sendSec=$("select[name='sendSec']").val();
  
	// console.log(IsURL(watchdogUrl)); 
  if(!IsURL(watchdogUrl)){
	   
		 Tips("alert","Watchdog URL格式出错!请重新输入");
	
	}else{
     var generic_Settings={
		    "watchdogUrl"  : watchdogUrl,
	      "sleepSecPerReq" : sleepSecRep,
				//"keepAliveOvertimeSec" : keepAliveOvertimeSec,
				"sendMail" : sendMail,
				"sendHour" : sendHour,
				"sendMin"  : sendMin,
				"sendSec"  : sendSec
	   };
  
		// console.log(generic_Settings);
    $.ajax({
        type: "post",
        url : "http://"+domain+"/mmsapi"+version+"/update/generic_setting/@self",
        async: false,
        data: generic_Settings,
        success: function(json, textStatus, jqXHR){//如果调用php成功
            if(jqXHR.status==200 || jqXHR.status==205){
               Tips("complete","修改常规设置成功!");
          }
           },
        error: function(jqXHR, textStatus, errorThrown){
            switch(jqXHR.status){
               case 400 : Tips("alert" ,"数据未找到");break;
               case 500 : Tips("alert" ,"服务器出错");break;
               case 409 : Tips("alert" ,"用户名冲突");break;

            }      
           }
       });  
 
	}
 });

});

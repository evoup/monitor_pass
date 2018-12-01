function getEventSettingKeepAliveSuccessCallback(json, textStatus, jqXHR){
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
    //var keepalive=$("<h5>检查心跳请求超时秒数</h5><hr/><ul><li><input type='text' name='keepAliveOvertimeSec' value='"+json+"'>秒</li></ul>");	
	  //keepalive.appendTo("#event_settings ul");
	  $("input[name='keepAliveOvertimeSec']").val(json);
	}

}
function getEventSettingSuccessCallback(json, textStatus, jqXHR){
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
    $.each(json, function(name,detail){
		  var module;
			$.each(detail, function(i){
			  module=$("<h5>"+detail[2]+"</h5><hr/><ul id='"+name+"' style='width:95%;'><li><label class='tag'>黄色警报下限：</label><input type='text' value='"+detail[0]+"'/>"+detail[3]+"</li><li><label class='tag'>红色警报下限：</label><input type='text' value='"+detail[1]+"'/>"+detail[3]+"</li></ul>");
			  //module.appendTo("#event_settings");
			}); 
			 module.appendTo("#event_settings");
		});	 
     var submit=$("<input type='submit' value='保存'/>");
		 submit.appendTo("#event_settings");
	 }


    $("input[type='text']").each(function(){
		  $(this).bind("blur",function(){ //失去焦点
			   //console.log(isNaN($(this).val()));
			   if(!!isNaN($(this).val())){ //判断输入的值是否是数字
					 Tips("alert","输入的数据类型必须为数字");
				   $("input[type='submit']").attr("disabled",true);
				 }else if($(this).val().length>20 ||$(this).val()<0){
				   Tips("alert","输入的数据位数超过20位或者为负数");
					 $("input[type='submit']").attr("disabled",true);
				 }else{
				   $("#Tips").hide();
					 $("input[type='submit']").attr("disabled",false);
				 
				 }

			}); 
		});

    
}


$(document).ready(function(){
  var version=url_params("version");
  var domain=domainURI(document.location.href);
	get_data_notLoad("http://"+domain+"/mmsapi"+version+"/get/event_setting/@keepalive",getEventSettingKeepAliveSuccessCallback);

	get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/event_setting/@self", "#event_settings", "loading_middle.gif", getEventSettingSuccessCallback);
  

  $("#event_settings").submit(function(){
		
		var nameArray=[],numArray=[]; 
		var keepAliveOvertimeSec=$("input[name='keepAliveOvertimeSec']").val();
		$("#event_settings ul:not(:first)").each(function(){
		   var name=$(this).attr("id"),num="";
       nameArray.push(name);
			 //console.log(name);
			 //console.log($(this).children().children("input"));
			 $(this).children("li").children("input").each(function(j){
			   //console.log($(this).val());
			   j==0 ? num+=$(this).val()+"|" : num+=$(this).val();
			 });
			 //console.log(num);
			 numArray.push(num);
		});
      var eventSettingData="";
      for(var m in nameArray){
			  m==nameArray.length-1 ? eventSettingData+=nameArray[m]+"="+numArray[m] :  eventSettingData+=nameArray[m]+"="+numArray[m]+"&"; //组成post数据格式
			} 
      eventSettingData+="&keepAliveOvertimeSec="+keepAliveOvertimeSec;
			console.log(eventSettingData);

         //提交表单数据		    
      $.ajax({
           type  : "post",
           url   : "http://"+domain+"/mmsapi"+version+"/update/event_setting/@self",
           async : false,
           data  : eventSettingData,
          success: function(data, textStatus, jqXHR){
              if(jqXHR.status==200||jqXHR.status==205){
			        Tips("complete","事件设置成功");	
				   }	
		      	},
          error: function(jqXHR, textStatus, errorThrown){
             switch(jqXHR.status){
               case 400 : Tips("alert","数据不存在");break;
               case 500 : Tips("alert","服务器出错");break;
               case 502 : Tips("alert","用户名冲突");break;  
				   }       
 		     	}
       }); 
		 	 
	
	});

});

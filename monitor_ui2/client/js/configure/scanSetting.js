function getScanSettingSuccessCallback(json, textStatus, jqXHR){
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
    $.each(json, function(name,detail){
		  var module;
			$.each(detail, function(i){
			  module=$("<h5>"+detail[4]+"</h5><hr/><ul id='"+name+"' style='width:95%;'><li>每<input type='text' style='width:60px;' value='"+detail[0]+"'/>分钟扫描一次事件</li><br/><li>每<input type='text' style='width:60px;' value='"+detail[1]+"'/>分钟重新初始化事件检查，累计直到达到<input type='text' style='width:60px;' value='"+detail[2]+"'>次则生成警报</li><br /><li>每<input type='text' style='width:60px;' value='"+detail[3]+"'/>次连续回归正常，则生成恢复通知</ul>");
			  //module.appendTo("#event_settings");
			}); 
			 module.appendTo("#scan_settings");
		});	 
     var submit=$("<input type='submit' value='保存'/>");
		 submit.appendTo("#scan_settings");
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
  var ver=url_params("version");
  var domain=domainURI(document.location.href);
	
	get_data_ajax("http://"+domain+"/mmsapi"+ver+"/get/scan_setting/@self", "#scan_settings", "loading_middle.gif", getScanSettingSuccessCallback);

 $("#scan_settings").submit(function(){
		var nameArray=[],numArray=[]; 
		$("#scan_settings ul").each(function(){
		   var name=$(this).attr("id"),num="";
       nameArray.push(name);
			 //console.log(name);
			 //console.log($(this).children().children("input"));
			 var len=$(this).children("li").children("input").length;//li length
			 $(this).children("li").children("input").each(function(j){
			   //console.log($(this).val());
				 var data=$(this).val();//input value
				 if(data==""){
				   data=0;
				 }
			   j!=len-1 ? num+=data+"|" : num+=data;
			 });
			 //console.log(num);
			 numArray.push(num);
		});
      var scanSettingData="";
      for(var m in nameArray){
			  m==nameArray.length-1 ? scanSettingData+=nameArray[m]+"="+numArray[m] :  scanSettingData+=nameArray[m]+"="+numArray[m]+"&"; //组成post数据格式
			} 
      scanSettingData+="";
			
			console.log(scanSettingData);

         //提交表单数据		    
      $.ajax({
           type  : "post",
           url   : "http://"+domain+"/mmsapi"+ver+"/update/scan_setting/@self",
           async : false,
           data  : scanSettingData,
          success: function(data, textStatus, jqXHR){
              if(jqXHR.status==200||jqXHR.status==205){
			        Tips("complete","扫描设置成功");	
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

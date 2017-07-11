 var mailGet=function(ver,domain){ //get邮件设置的数据
   $.ajax({
       type: "get",
       url : "http://"+domain+"/mmsapi"+ver+"/get/mailSetting/@self",
       async: true,
       dataType:"json",
       success: function(json, textStatus, jqXHR){//如果调用php成功
				 if(json==null){
					  console.log("服务器传的数据为空");
					}else{
				    //console.log(json); 
					  if(json.send_mail_type==0){
				       $("#send_mail").attr("checked","true");
					   }else if(json.send_mail_type==1){
					    $("#smtp").attr("checked","true");
					   }
				  //$('#Mail input[name="mail_from"]').attr("value",json.mail_from);
					   $('#Mail input[name="mail_from"]').val(json.mail_from);
					   $('#Mail input[name="sender_name"]').val(json.sender_name);
						 $('#Mail input[name="smtp_server"]').val(json.smtp_server);
			       $('#Mail input[name="smtp_domain"]').val(json.smtp_domain);
					   $('#Mail input[name="smtp_port"]').val(json.smtp_port);
					   $('#Mail input[name="smtp_username"]').val(json.smtp_username);
					   $('#Mail input[name="smtp_password"]').val(json.smtp_password);
			       $('#Mail input[name="smtp_auth"]').val(json.smtp_auth);
					   if(json.smtp_auth==0){
					     $('#Mail input[name="smtp_auth"]').attr("checked",false);
					   }else if(json.smtp_auth==1){
					     $('#Mail input[name="smtp_auth"]').attr("checked",true);
					   }
					} 
			 },
       error:function(jqXHR, textStatus, errorThrown){
          switch(jqXHR.status){
           case 400 : alert("NOT Found!!!");break;
           case 500 : alert("Server Error!!!");break;
          }      
			 }
     
	 });

};




$(document).ready(function(){
  var version=url_params("version");
	var domain=domainURI(document.location.href);
	mailGet(version,domain);
	$('#Mail input[name="send_mail_type"]').each(function(){
	  $(this).bind("click",function(){
		  $(this).attr("checked",true);
		  //console.log($(this).val());
		});
	});
	$('#Mail input[name="smtp_auth"]').click(function(){
    //console.log($(this).attr("checked"));
	  if($(this).attr("checked")){
      $(this).val(1);
		}else{
		  $(this).val(0);
		}
	});
	$("#Mail").submit(function(event){
	  event.preventDefault();
  //  mailPost();
   var send_mail_type=Number($('#Mail input[name="send_mail_type"]:checked').val()),
	     mail_from=$('#Mail input[name="mail_from"]').val(),
			 sender_name=$('#Mail input[name="sender_name"]').val(),
			 smtp_server=$('#Mail input[name="smtp_server"]').val(),
			 smtp_domain=$('#Mail input[name="smtp_domain"]').val(),
	     smtp_port= Number($('#Mail input[name="smtp_port"]').val()),
	     smtp_username=$('#Mail input[name="smtp_username"]').val(),
			 smtp_password=$('#Mail input[name="smtp_password"]').val(),
			 smtp_auth= Number($('#Mail input[name="smtp_auth"]').val());
	
			 var data={
	       "send_mail_type"  : send_mail_type,
			   "mail_from"       : mail_from,
			   "sender_name"     : sender_name,
			   "smtp_server"     : smtp_server,
			   "smtp_domain"     : smtp_domain,
	       "smtp_port"       : smtp_port,
			   "smtp_username"   : smtp_username,
			   "smtp_password"   : smtp_password,
			   "smtp_auth"       : smtp_auth
	};
	post_data("http://"+domain+"/mmsapi"+version+"/update/mailSetting/@self","true",data);
 });
});

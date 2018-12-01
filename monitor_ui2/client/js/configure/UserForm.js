/*************************验证邮箱格式***********************************************/
 function isEmail(str){
       //var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
       var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	     return reg.test(str);
 }




function get_username_edit_Callback(json, textStatus, jqXHR){
  if(json==null){
     console.log("json null");
  }else{
    // console.log(json.desc);
    var userId=url_params("name");
    $("#User_form input[name='UserId']").val(userId);
    $("#User_form input[name='realname']").val(json.realname);
    $("#User_form input[name='email']").val(json.email);
    $("#User_form input[name='passwd']").val(json.passwd);
    //$("#User_form select[name='mailtype']").find("option:selected").val(json.mailtype);
    $("#User_form select[name='mailtype'] option").each(function(){
		   if($(this).val()==json.mailtype){
			   $(this).attr("selected",true);
			 }
		});
		$("#User_form textarea[name='desc']").val(json.desc);
  
	  /*$("#User_form input[name='email']").bind("blur",function(){
	     if(!isEmail($(this).val())){
			    Tips("alert","邮箱格式不对，请重新填写"); 
			 }	  
		
		});*/
	}
}





$(document).ready(function(){
  //var userId=$("#addUser_form input[name='UserId']").val();
	var user_name=url_params("name"),version=url_params("version");
  //console.log(user_name);
	var domain=domainURI(document.location.href);
	var isAdd=true,_url;
	if(user_name!=null){
    get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/user/@self/"+user_name,"","loading_middle.gif",get_username_edit_Callback);
		isAdd=false;
		$("#User_form input[name='UserId']").attr("readonly", "readonly");
    $("#User_form input[name='UserId']").attr("disabled", "disabled"); 
	  $("div.title").html("修改用户");
	
	};

  
  $("#return").click(function(){  //return
	  
		window.location.href="userManager.html?version="+version;
	  
	});
 
  $("input[name='UserId']").blur(function(){  //检测用户名是否含有中文
      var name=$(this).val();
      if(!!isChina(name)){  //字符串含有中文
        Tips("alert","用户名不能含有中文");
      }else{
        $("#Tips").hide();
      }
   });
  
	
	$("#User_form input[name='email']").bind("blur",function(){ //检测邮箱格式是否正确
       if(!isEmail($(this).val())){
          Tips("alert","邮箱格式不对，请重新填写");
          $("input[type='submit']").attr("disabled",true);
        }else{
          $("#Tips").hide();
          $("input[type='submit']").attr("disabled",false);
        }

   });

	$("#User_form input[name='passwd']").bind("blur",function(){
	    if($(this).val()==""){
		    Tips("alert","登录密码不能为空，请重新填写");
        $("input[type='submit']").attr("disabled",true); 	
			}else{
        $("#Tips").hide();
        $("input[type='submit']").attr("disabled",false);
       }   
	}); 
  
	$("#User_form").submit(function(){
    var user_Id=$("#User_form input[name='UserId']").val(),
		    realname=$("#User_form input[name='realname']").val(),
				email=$("#User_form input[name='email']").val(),		
        passwd=$("#User_form input[name='passwd']").val(),
				//mailtype=$("addUser_form select[name='mailtype']").val(),
				mailtype=$("#User_form select[name='mailtype']").find("option:selected").val(),
				desc=$("#User_form textarea[name='desc']").val();
    
    //if(!($("#Tips").attr("class")=="alert" && $("#Tips").css("display")=="block")){ //验证邮箱格式 
   // console.log(passwd);
    var add_user_param={
        "realname" : realname,
        "email" : email,
        "passwd" : passwd,
        "mailtype" : mailtype,
        "desc" : desc
        };
	    if(!!isAdd){
		     _url="http://"+domain+"/mmsapi"+version+"/create/user/@self/"+user_Id+"";
	     }else{
	      _url="http://"+domain+"/mmsapi"+version+"/update/user/@self/"+user_Id+"";	
     	}
//	console.log(_url);
    if(passwd==""){
       Tips("alert","登录密码不能为空，请重新填写"); 
     }else{ 
       $.ajax({
         type: "post",
         url : _url,
         async: false,
         data: add_user_param,
         success: function(data, textStatus, jqXHR){
           if(jqXHR.status==200||jqXHR.status==205){
             //alert("create success");
             Tips("complete","添加/修改用户成功!"); 
					}
         },
         error: function(jqXHR, textStatus, errorThrown){
          // console.log(jqXHR.status);
           switch(jqXHR.status){
               case 400 : Tips("alert","数据不存在!");break;
               case 500 : Tips("alert","服务器出错!");;break;
               case 409 : Tips("alert","用户名已经存在!");;break;  
					 }
        }
       }); 
      }
	//	}//else{
     //alert("邮箱格式不对，请重新填写");
		// Tips("alert","邮箱格式不对，请重新填写");
  // }
	});
});

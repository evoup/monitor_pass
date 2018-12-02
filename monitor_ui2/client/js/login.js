function url_params(param){ //匹配url参数
   var reg = new RegExp("(^|&)" + param + "=([^&]*)(&|$)","i");
   var r = window.location.search.substr(1).match(reg);
   if (r!=null) return decodeURIComponent(r[2]);return null;
 }

function domainURI(str){
  var durl=/http:\/\/([^\/]+)\//i;
  domain = str.match(durl);
  //domain = domain[0].slice(0,-1);
  return domain[1];
}

function login(version,domain){
    var username=$("#loginForm input[name='username']").val(),
        passwd=$("#loginForm input[name='passwd']").val();
    var iskeepLogin=$("#loginForm input[name='keeplogin']").attr("checked");
    if(!!iskeepLogin){
        $("#loginForm input[name='keeplogin']").val(1);
    }else{
        $("#loginForm input[name='keeplogin']").val(0);
    }
    var keeplogin=$("#loginForm input[name='keeplogin']").val();
    var data={
        "username" : username,
        "password" : passwd,
        "keeplogin" : keeplogin
      };
   //console.log(data);
   if(username=="" || passwd==""){
      var error=$("#error");
      if(error.length!=0){
        error.remove();
      }
      $("<li id='error' style='color:#FF0000;margin-left:100px;'>请输入用户名或者密码</li>").appendTo($("#loginForm ul"));
   }else{
     $.ajax({
        type: "post",
        url : "http://"+domain+"/mmsapi"+version+"/login/",
        async : false,
        data : data,
        success: function(json, textStatus, jqXHR){
          if(jqXHR.status==200){
              window.localStorage['mms_token'] = json.token;
             window.location.href="main.html?version="+version;
          }
           },
        error: function(jqXHR, textStatus, errorThrown){
          if(jqXHR.status==401){
             var error=$("#error");
             if(error.length!=0){
               error.remove();
             }
             $("<li id='error' style='padding:0px;'>您输入的用户名或密码不正确。</li>").insertAfter($("#loginForm ul li:nth-of-type(2)"));
          }
         }
     });
  }


}

$(document).ready(function(){
 var version=url_params("version");
 //console.log(domainURI("http://211.136.105.207:8282/mmsapi/get/login/@self"));
 var domain=domainURI(document.location.href);
 //console.log(domain);
 var lastYearVersion=new Date().getFullYear();
 $(".footer cite").text(lastYearVersion);

 $.ajax({
     type: "post",
     url : "http://"+domain+"/mmsapi"+version+"/login/status/",
     async: false,
     data : {'token': window.localStorage['mms_token']},
     headers:{'Content-Type':'application/json;charset=utf8'},
     success: function(json, textStatus, jqXHR){//如果调用php成功
        if(jqXHR.status==200){
           window.location.href="main.html?version="+version;
          }
           },
		 error: function(jqXHR, textStatus, errorThrown){
		     if(jqXHR.status==401){
          $("#loginForm").show();
        }
		  }
   });

/* $("#loginForm input[name='passwd']").blur(function(){
    if($("#loginForm input[name='username']").val()=="" || this.val()==""){
       ("请填写用户名或者密码").insertAfter($())
    }
 });*/
  $(document).bind("keydown",function(e){  //回车键登录
		if(e.keyCode==13){
	    login(version,domain);
     }

	});

/***************提交登录表单数据****************************/
 $("#loginsubmit").click(function(){
    login(version,domain);
 });

});

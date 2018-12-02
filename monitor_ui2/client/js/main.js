function get_status_logininfo_Callback(json, textStatus, jqXHR){
  if(json==null){
    console.log("json null");
	}else{
    //console.log(json);
		var account,ver=url_params("version");
	  if(json[1]==null){
	    window.location.href="login.html?version="+ver;
	  }else{
	     if(json[0]==null){
		     account=json[1];
		    }else{
		     account=json[0]+"："+json[1];
        }
		 $("#header .account cite").html(account);
    }
	 //account=json[0]+"："+json[1];
	 //console.log(account);
	 //$(".header .account cite").html(account);
	}
}




function exitLogin(_url){
 var vers=url_params("version");
 $.ajax({
      type: "get",
       url:  _url,
     async:  true,
  dataType: "json",
   success: function(json, textStatus, jqXHR){
            //alert(jqXHR.status+"s");
	 },
  error: function(jqXHR, textStatus, errorThrown){
		 if(jqXHR.status==401){
			 window.location.href="login.html?version="+vers;
       }
      }
   });
}


function contentIframeHeight(){ 
	//contentIframe Height
  var notIframeHeight=$("#header").height()+$("#top_navigation").height()+$("#footer").height()+30,
	    iframeHeight=$(window).height()-notIframeHeight;
	 //console.log(iframeHeight);
	 $("#contentIframe").height(iframeHeight);
}

$(document).ready(function(){
  var version=url_params("version");
	var domain=domainURI(document.location.href);
	//console.log(version);
	bindClickNav();

	var yearLastVersion=new Date().getFullYear(); //页脚版本结束年份
	//console.log(yearLastVersion);
	$("#footer cite").text(yearLastVersion);
  
	get_data_notLoad("http://"+domain+"/mmsapi"+version+"/get/status/@logininfo", get_status_logininfo_Callback);
  $("#header .account a").click(function(){
  	// exitLogin("http://"+domain+"/mmsapi"+version+"/delete/login/@self");
	  window.localStorage['mms_token']=null;
	  window.location.href="login.html?version="+version;
  });
	$("#contentIframe").load(function(){
	   contentIframeHeight();	
	}); 
  //contentIframeHeight();
  window.onresize=contentIframeHeight;
  //console.log(document.documentElement.clientHeight+","+$("#top_navigation").height()+","+$("#header").height()+","+$("#footer").height());
  exitLogin("http://"+domain+"/mmsapi"+version+"/get/login/@self");

	window.setInterval(function(){
		exitLogin("http://"+domain+"/mmsapi"+version+"/get/login/@self");
	},300000);
});

 function get_cloudview_Callback(json, textStatus, jqXHR){
    if(json==null){
      console.log("json null");
    }else{
      //console.log(json);
      var version=url_params("version");
      //clearInterval(timer);
      if($("#div1:has(a)").length!=0){ //clear 上次加载的数据
          $("#div1 a").each(function(i){
            $(this).remove();
        });
        //3d alertCloud default
        radius = 160; dtr = Math.PI/180; d=800;
        mcList = [];
        active = false;
        lasta = 1; lastb = 1;
        distr = true;tspeed=7;size= 800;
        mouseX=0;mouseY=0;
        howElliptical=1;
        aA=null;oDiv=null;
      }
      $.each(json,function(key,value){
        var status,link;
        switch(value){
          case "0" : status="lake";break;
          case "1" : status="lime";break;
          case "2" : status="yellow";break;
          case "3" : status="red";break;
          case "4" : status="orange";break;
          case "5" : status="gray";break;
        }
        link=$("<a href='../server/serverStatus.html?name="+key+"&version="+version+"' class='"+status+"'>"+key+"</a>");
        link.appendTo($(".alertCloud #div1"));
      });
     //console.log($("#div1"));
     alertCloud(); //监控云
    }

}

$(document).ready(function(){
  var v=url_params("version");
  //console.log(window.location.href);
  var domain=domainURI(document.location.href);
	get_data_notLoad("http://"+domain+"/mmsapi"+v+"/get/cloudview/@all",get_cloudview_Callback);
  show_hide_Table();
}); 

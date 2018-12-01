function monitorDaemon(tableName,tableParent,process){ //形成监控引擎的表格
  var _process={};
	_process=process;


	var data=["项目","状态","操作","进程状态"],action="";

	_process.process_status==1 ? data.push("greenLight"):data.push("redLight");//红绿灯

	_process.action_start==0 && _process.action_stop==1 ? action="pause" : action="play"; //播放或者暂停

	_process.action_restart==1 ? action+="|restart" : action+="";//重启

  _process.action_bemaster==1 ? action+="|bemaster": action+="";//

	data.push(action);
  data.push("<p>进程开始时间: "+_process.process_starttime+"</p>"+"<p>总计运行时间:"+_process.process_uptime+"</p>"+"<p>进程ID:"+_process.process_pid+"</p>");
 
  var processTable=new Table(3,3,tableName,tableParent);
  processTable.setTable("100%","100%");
  
  $("#"+tableName).find("tbody tr td").each(function(i){
    var operate=[],Img="";
    
    if(i==4){ //light
       $(this).html("<section class='engineImage' id='"+data[i]+"'></section>");
       
			 if(data[i]=="redLight"){
			    $(this).find("#"+data[i]).css({"-webkit-animation":"twinkling 1s infinite ease-in-out","-moz-animation":"twinkling 1s infinite ease-in-out"});
		   }else{
			    $(this).find("#"+data[i]).css({"-webkit-animation":"","-moz-animation":""});
			 } 
		}else if(i==5){ //action
       operate=data[i].split("|");
       for(var j in operate){
          var img="<section class='engineImage' id='"+operate[j]+"'/></section>";
          Img+=img;
       }
     //  console.log(Img);
       $(this).html(Img);
    }else{
       $(this).html(data[i]);
   
    }

  });
  
}

function get_monengine_Callback(json,textStatus,jqXHR){
   if(json==null){
     console.log("服务器传送的数据为空");
   }else{
   	 //$("#monitorBox").html(JSON.stringify(json));
     console.log(json);
     $.each(json,function(key,value){
       //console.log(key);
	   var k = key.replace(/[-.]/img,'_');
       var wrap=$("<div id='"+k+"Box'><p class='subtitle'>"+key+"</p></div>");
       wrap.appendTo($("#monitorBox"));
	   
       monitorDaemon(k+"Table",k+"Box",value);
     }); 
   }

}

$(document).ready(function(){
   
   var version=url_params("version"); 
   var domain=domainURI(document.location.href);
	 get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/monengine/@all","#monitorBox","loading_middle.gif",get_monengine_Callback);



});

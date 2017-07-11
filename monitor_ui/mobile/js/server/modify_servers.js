
/*************************获取用户组成员********************************************/
function get_usergroup_all_callback(json, textStatus, jqXHR){
  if(json==null){
	 console.log("json null");
	}else{
	  //console.log(json);
   var desc=[],member=[],name=[];
    for(var i in json){
    $.each(json[i],function(j){
      if(j==0){
        desc.push(json[i][0]);
       }
      if(j==1){
       for(var m in json[i][j]){
        member.push(json[i][j][m]);  
       }
      } 
    });
    //$("<option id='"+member[i]+"' value='"+desc[i]+"'>"+i+"</option>").appendTo($("#usergroup_optional"));  
    name.push(i);
   }
   for(var n in name){
    $("<option id='"+member[n]+"' value='"+desc[n]+"'>"+name[n]+"</option>").appendTo($("#usergroup_optional"));
   }
  }
 }



function addMemberTab(){
  var ver=url_params("version"),serversName=url_params("name");
	var domain=domainURI(document.location.href);
	$("<p>添加用户组到本服务器组</p><ul class='usergroup_select'><li><label>可选用户组</label><br/><select size='8' id='usergroup_optional'  multiple='multiple'></select></li><li><a href='javascript:void(0)'><img id='add_option' src='../images/arrow.png' style='-webkit-transform:translateY(-40px);-moz-transform:translate(0px,-40px);'/></a></li><li><label>已加入该组的用户组</label><br/><select id='add_usergroup_old' size='8' multiple='multiple'></select></li><li><div class='jqmClose'><input type='button' id='add_user_submit' value='确定'/><input type='button' id='add_user_reset'  value='取消'/></div></li></ul>").appendTo($("#jqmContent"));
 
 get_data_notLoad("http://"+domain+"/mmsapi"+ver+"/get/usergroup/@all",get_usergroup_all_callback); 

 $("#add_option").click(function(){
	 addSelectOptionAction("#addMemberTable"); 
 });  
 
 $("#add_user_submit").click(function(){ //弹窗点击按钮
   add_memberAction("addMemberTable","addMemberBox");//添加成员动作
 });
}

function addCheckBox(obj,idName){
  //console.log(obj); 
  var n=0;
  $.each(obj,function(i){
    $("<input type='checkbox'id='"+idName+i+"' name='"+i+"' style='display:none;'/>"+"<label class='checkboxText' style='display:none'></label>").appendTo($("#jqmContent"));
  });
}
/*****************************************************************************************************/
function get_monitoritem_all_callback(json, textStatus, jqXHR){
  if(json==null){
   console.log("json null");
  }else{
   //console.log(json);
   var type=[];
   for(var i in json){
    type.push(i);
    switch(i){
			case "generic" : addCheckBox(json[i],"generic_");break;
      case "mysql"   : addCheckBox(json[i],"mysql_");break;
      case "serving" : addCheckBox(json[i],"serving_");break;
      case "daemon"  : addCheckBox(json[i],"daemon_");break;
      case "report"  : addCheckBox(json[i],"report_");break;
      case "mdn"     : addCheckBox(json[i],"mdn_");break;
      case "hdfs"    : addCheckBox(json[i],"hdfs_");break;
      case "mdb"     : addCheckBox(json[i],"mdb_");break;
			case "jail"    : addCheckBox(json[i],"jail_");break;
      case "gslb"    : addCheckBox(json[i],"gslb_");break;
      case "security": addCheckBox(json[i],"security_");break;
      case "monitor" : addCheckBox(json[i],"monitor_");break; 
    }
   }
   for(var j in type){
     $("<section id='"+type[j]+"Window'></section>").appendTo("#jqmContent");
    }
   //console.log(genericList);
	 /*for(var j in type){
    var obj=type[j]+"List"; 
	  $.each(obj,function(i){
			console.log($(this));
		  $("<input id='genericList"+i+"' type='checkbox' name='"+genericList[i]+"'/>"+"<label class='checkboxText'></label>").appendTo($("#jqmContent"));
	 if((i+1)%3==0){
		  $("<br/>").appendTo($("#jqmContent"));
		}
	 });
	 }*/
	}
}

/**********************monitorItem Tab show content*************************************/
function  monitorItemTab(parent,idText,title){
   //$("<section id='"+parent+"'></section>").insertBefore($("#jqmContent").children(":first"));
	
	$("#jqmContent input[id^='"+idText+"_']").each(function(i){
		  //console.log($(this));
		  $(this).next("label").text($(this).attr("name"));
      var movText=$(this).next("label").clone(true);
			var mov=$(this).clone(true);
			//console.log(typeof($("#isAll_"+idText).val()));
			//if($("#isAll"+idText).val()=="1"){
			 // mov.attr("checked",true);
			  //  }
			$(parent).append(mov.show());
      $(parent).append(movText.show());
			if((++i)%3==0){
        $("<br/>").appendTo($(parent));
       }
      //mov.insertAfter($("<label class='checkboxText'>"+$(this).attr("name")+"</label>"));   
  });
  $("<p>"+title+"</p>").insertBefore($(parent).children(":first")); 
  $("<br/><div class='jqmClose'><input type='submit' id='"+"Sumbit"+idText+"_' value='确定'/></div>").appendTo($(parent));  
  $("#Sumbit"+idText+"_").bind("click",function(){
    var param="";
		var len=$(this).parent().parent().children("input[id^='"+idText+"_']").length-1;
		$(this).parent().parent().children("input[id^='"+idText+"_']").each(function(i){
		  //var len=$(this).parent().parent().children("input[id^='"+idText+"_']").length-1;
			if($(this).attr("checked")==true){
			   $(this).val("1");
			}else if($(this).attr("checked")==false){
			   $(this).val("0");
			}
		  if(i==len){
				//console.log(len);
			  param+=$(this).attr("name")+":"+$(this).val();
			}else{	
		    param+=$(this).attr("name")+":"+$(this).val()+"|";
			}
		});
		//console.log(typeof($(this).parent().parent().children("input[id^='"+idText+"_']").length));
    $("<cite>"+param+"</cite>").hide().insertAfter($("#"+idText));
  });
}
$(document).ready(function(){
  var version=url_params("version"),serversName=url_params("name");
	var domain=domainURI(document.location.href);
	//console.log(version+"vadd");
	Tabs();
/**********jqModal 弹窗列表*************************************************/
 //console.log("http://211.136.105.207:8282/mmsapi"+url_params("version")+"/get/monitoritem/@all+"+"dddd"); 
 get_data_notLoad("http://"+domain+"/mmsapi"+url_params("version")+"/get/monitoritem/@all",get_monitoritem_all_callback); 
  
  $(".jqModal").each(function(){
	   $(this).bind("click",function(){
		    //console.log($(this));
      if($("#jqmContent").children().length!=0){
         $("#jqmContent").children().empty();
       }
      switch($(this).attr("id")){
        case "add_memberButton" : addMemberTab();break;
        case "generic" : monitorItemTab("#genericWindow","generic","下表选择Generic的明细监控项目");break;
        case "mysql"   : monitorItemTab("#mysqlWindow","mysql","下表选择Mysql的明细监控项目");break;   
        case "serving" : monitorItemTab("#servingWindow","serving","下表选择Serving的明细监控项目");break;
        case "daemon"  : monitorItemTab("#daemonWindow","daemon","下表选择Daemon的明细监控项目");break;
        case "report"  : monitorItemTab("#reportWindow","report","下表选择Report的明细监控项目");break;
        case "mdn"     : monitorItemTab("#mdnWindow","mdn","下表选择Mdn的明细监控项目");break;
        case "hdfs"    : monitorItemTab("#hdfsWindow","hdfs","下表选择Hdfs的明细监控项目");break;
        case "jail"    : monitorItemTab("#jailWindow","jail","下表选择FreeBSD Jail的明细监控项目");break;
        case "mdb"     : monitorItemTab("#mdbWindow","mdb","下表选择Mdb的明细监控项目");break;
				case "gslb"    : monitorItemTab("#gslbWindow","gslb","下表选择Global Load的明细监控项目");break;
        case "security": monitorItemTab("#securityWindow","security","下表选择Security的明细监控项目");break;
        case "monitor" : monitorItemTab("#monitorWindow","monitor","下表选择Mad Monitor的明细监控项目");break; 
     }
		 });
	 });
    $(".monitorList input[id^='isAll_']").each(function(){ //全选
     $(this).bind("click",function(){
       var itemType=$(this).attr("id").substring(6);
       //console.log(itemType);
       if($(this).attr("checked")==true){
           $(this).val("1");
           $("#jqmContent input[id^='"+itemType+"_']").each(function(){
              $(this).val("1");
              $(this).attr("checked",true);
           });
        }else if($(this).attr("checked")==false){
           $(this).val("0");
            $("#jqmContent input[id^='"+itemType+"_']").each(function(){
                 $(this).val("0");
             });
        }
      });
     });  
  
   /**************************提交表单***************************************************/
	 $("#addServers_form").submit(function(){
     var servers_name=$('#addServers_form input[name="name"]').val(),
		     servers_desc=$('#addServers_form textarea[name="desc"]').val(),
		     servers_mailtype=$('#addServers_form select[name="mailtype"]').val();
       //post_data("http://211.136.105.207:8282/mmsapi/create/serverGroup/@self",{"name":servers_name,"desc":servers_desc});
		 var usergroup=[];   
     $("#addMemberTable tr").each(function(d){
       if(d!=0){
         usergroup.push($(this).children(":first").text());
       }
      });   
		 var membergroup=usergroup.join("|");
		 var monitoritem="";
     /*$(".monitorList input[id^='isAll_']").each(function(){ //全选
		   var itemType=$(this).attr("id").substring(6);
			 //console.log(itemType);
			 if($(this).attr("checked")==true){
				   $(this).val("1");
			     $("#jqmContent input[id^='"+itemType+"_']").each(function(){
					    $(this).val("1");
					 });		  
				}else if($(this).attr("checked")==false){
				   $(this).val("0");
            $("#jqmContent input[id^='"+itemType+"_']").each(function(){
							   $(this).val("0");
			       });
				}
		 });*/
		 $(".monitorList .jqModal").each(function(i){
		    //console.log($(this).next("cite").html());
			  var citeObj=$(this).next("cite"),item="",
            len=$("#jqmContent input[id^='"+$(this).attr("id")+"_']").length-1;
			  if(i==0 && citeObj.length!=0){
           monitoritem+=$(this).attr("id")+"|"+$(this).next("cite").html();  
		     }else if(i==0 && citeObj.length==0){
           $("#jqmContent input[id^='"+$(this).attr("id")+"_']").each(function(j){
               if(j==len){
                 item+=$(this).attr("name")+":"+$(this).val();
               }else{
                 item+=$(this).attr("name")+":"+$(this).val()+"|";
               }
            });
              monitoritem+=$(this).attr("id")+"|"+item;
         
         }else if(i!=0 && citeObj.length!=0){
				    monitoritem+="#"+$(this).attr("id")+"|"+$(this).next("cite").html();
				 }else if(i!=0 && citeObj.length==0){
            $("#jqmContent input[id^='"+$(this).attr("id")+"_']").each(function(j){
               if(j==len){
                 item+=$(this).attr("name")+":"+$(this).val();
               }else{
                 item+=$(this).attr("name")+":"+$(this).val()+"|";
               }
            });
				    monitoritem+="#"+$(this).attr("id")+"|"+item;
        }
				});
		 //console.log(monitoritem);
		 var add_servers_param={
               "name" : servers_name,
               "desc" : servers_desc,
           "mailtype" : servers_mailtype,
        "membergroup" : membergroup,
        "monitoritem" : monitoritem
      }
     if(servers_name==""||servers_name.indexOf(" ")!=-1){
				  //alert("警告：服务器组名不能为空或者含空格"); 
		    Tips("alert","警告：服务器组名不能为空或者含空格!!!");	  
		 }else if(servers_desc.length>200){
			   // alert("备注字数不能超过200");
		    Tips("alert","备注字数不能超过200!!!");
		 }else if(isNaN(servers_name)==false){
			  //  alert("服务器组名不能为数字!!!");      		
		    Tips("alert","服务器组名不能为数字!!!"); 
		 }else{
				//	 console.log(version);
				$.ajax({
           type: "post",
           url : "http://"+domain+"/mmsapi"+version+"/create/serverGroup/@self",
           async: false,
           data:  add_servers_param,
           success: function(data, textStatus, jqXHR){//如果调用php成功
               //alert("success");
               if(jqXHR.status==200||jqXHR.status==205){
							   //alert("创建服务器组成功");
							   Tips("complete","添加自定义服务器组成功")
							 }
						 },
           error: function(jqXHR, textStatus, errorThrown){
               console.log(jqXHR.status);
							 switch(jqXHR.status){
                 case 400 : Tips("alert","数据没有找到!");break;
                 case 500 : Tips("alert","服务器出错!");break;
								 case 409 : Tips("alert","用户名已经存在!");break;
               } 
						 }
          }); 
		    }
		});


		$("#return").click(function(){ //返回主界面
		  //console.log("click");
			window.location.href="serversList.html?version="+version; 
		});
	 });

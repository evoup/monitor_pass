function editServerSuccessCallback(json, textStatus, jqXHR){
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(JSON.stringify(json));
	  window._server_info = json;
	  $("input[name='server_name']").val(json.server_name);
		$("input[name='alias']").val(json.alias);
    //console.log(json.server_type.daemon);
	  /*$.each(json.server_type,function(key,value){ //get服务器类型 checked  
			//console.log(key);
	    value==0 ? $("input[name="+key+"]").attr("checked",false) : $("input[name="+key+"]").attr("checked",true);	
		});*/
	 
	  $("input[name='ip']").val(json.ip);
		$("select[name='auth_type']").val(json.auth_type);

	  $.each(json.upload_direction,function(key,value){ //get上传方向
		   $("<option value='"+key+"'>"+key+"</option>").appendTo($("select[name='upload_direction']")); 
		   if(value==1){
				 $("select[name='upload_direction']").attr("selected",true);
			 }
		});

    $("select[name='monitored']").val(json.monitored);

	  $("textarea[name='memo']").val(json.memo);
	}
}

function getGroupSuccessCallback(json, textStatus, jqXHR){
  if(json==null){
	   console.log("json null");
	}else{
	   console.log(json);
		 var row=1,data=["服务器组名","描述"],flag;//标记是否是已经添加的
	   $.each(json,function(key,value){
		   flag=false;	
			 $.each(value,function(k,v){
			   if(k==0&& v==1){
				   ++row;
					 data.push(key);
				   flag=true;
				 }
				 if(!!flag && k==1){
				   data.push(v);
				   $("<option value='"+v+"'>"+key+"</option>").appendTo($("#add_usergroup_old"));
				 }else if(!flag && k==1){
				   $("<option value='"+v+"'>"+key+"</option>").appendTo($("#usergroup_optional"));
				 }
			});
		});
		//console.log(data);
    var tableObj=$("#addedGroupTable");
		if(tableObj.length==0 && row!=1){
		  var t=new Table(row,2,"addedGroupTable","addedGroupBox");
			t.setTable("90%","90%");
		}
		$("#addedGroupTable tr td").each(function(i){
		  $(this).text(data[i]); 
		});
	}
}

function get_usergroup_allWindow_callback(json,textStatus,jqXHR){
   if(json==null){
     console.log("json null");
   }else{
     //console.log(json);
     var flag;//标记是否是已经添加的
     $.each(json,function(key,value){
       flag=false;
       $.each(value,function(k,v){
         if(k==0&& v==1){
          // ++row;
           //data.push(key);
           flag=true;
         }
         if(!!flag && k==1){
           //data.push(v);
           $("<option value='"+v+"'>"+key+"</option>").appendTo($("#add_usergroup_old"));
         }else if(!flag && k==1){
           $("<option value='"+v+"'>"+key+"</option>").appendTo($("#usergroup_optional"));
         }
      });
    });

    var isAdded=[];
    $("#addedGroupTable tr").each(function(x){ //检测是否已经添加到表格,恢复到之前所选的服务器组
      var isAddedItem=$(this).children(":first").text();
      if(x!=0){
        isAdded.push(isAddedItem);
       }
      });
    // console.log(isAdded);
    for(var m in isAdded){
      if($("#add_usergroup_old option:contains('"+isAdded[m]+"')").length==0){
    // $("<option value='"+ $("#addedGroup tr contains('"+isAdded[m]+"')").children(":last").text()+"'>"+isAdded[m]+"</option>").appendTo($("#add_usergroup_old"));
        var name=$("#usergroup_optional option:contains('"+isAdded[m]+"')").text();
        var desc=$("#usergroup_optional option:contains('"+isAdded[m]+"')").val();
      //  console.log("jjjj");
        $("#usergroup_optional option:contains('"+isAdded[m]+"')").remove();
       $("<option value='"+desc+"'>"+name+"</option>").appendTo($("#add_usergroup_old"));

     }

   }

    //console.log(data);
  }
   

}
function addMemberTab(){
  var ver=url_params("version");
  var domain=domainURI(document.location.href); 
	$(".jqmWindow").css({"width":"600px"});

  $("<p>选择要加入的服务器组</p><ul class='usergroup_select'><li><label>可加入服务器组</label><br/><select size='8' id='usergroup_optional'  multiple='multiple'></select></li><li><a href='javascript:void(0)' ><img id='add_option' src='../images/arrow.png' style='-webkit-transform:translate(20px,-80px);-moz-transform:translate(20px,-80px);' /></a><a href='javascript:void(0)'><img id='del_option' src='../images/arrowLeft.png' style='-webkit-transform:translate(-20px,0px);-moz-transform:translate(-20px,0px);'/></a></li><li><label>已加入的服务器组</label><br/><select id='add_usergroup_old' size='8' multiple='multiple'></select></li><li><div class='jqmClose'><input type='button' id='add_user_submit' value='确定'/><input type='button' id='add_user_reset'  value='取消'/></div></li></ul>").appendTo($("#jqmContent"));

  get_data_notLoad("http://"+domain+"/mmsapi"+ver+"/get/server/@self_group/"+url_params("hostname"),get_usergroup_allWindow_callback);
 
 /*var isAdded=[];
 $("#addedGroup tr").each(function(x){ //是否已经添加到表格
		var isAddedItem=$(this).children(":first").text();
		if(x!=0){
      isAdded.push(isAddedItem);
		}
 });
 
 for(var m in isAdded){
   if($("#add_usergroup_old option:contains('"+isAdded[m]+"')").length==0){
	  // $("<option value='"+ $("#addedGroup tr contains('"+isAdded[m]+"')").children(":last").text()+"'>"+isAdded[m]+"</option>").appendTo($("#add_usergroup_old"));
	   var name=$("#usergroup_optional option:contains('"+isAdded[m]+"')").text();
		 var desc=$("#usergroup_optional option:contains('"+isAdded[m]+"')").val();
     console.log("jjjj");
		 $("#usergroup_optional option:contains('"+isAdded[m]+"')").remove();
     $("<option value='"+desc+"'>"+name+"</option>").appendTo($("#add_usergroup_old"));
		 
	 }
 
 }*/

 $("#add_option").bind("click",function(){ //右移用户
   addSelectOptionAction("#addedGroupTable");
 });
 
 $("#del_option").bind("click",function(){ 
    delSelectOptionAction("#addedGroupTable");//左移用户
  });
 
  $("#add_user_submit").click(function(){
  // add_memberAction("addedGroupTable","addedGroupBox");//添加成员动作
   var tableObj=$("#addedGroupTable");
   if(tableObj.length==0){
     var table=["用户组名","描述"],row=1,addMember=[];
     $(".usergroup_select select:last option").each(function(){
       table.push($(this).text());
       table.push($(this).val());
       //table.push($(this).attr("id"));
    //  addMember.push($(this).text());
       ++row;
       //$(this).val($(this).text());
       //$(this).attr("id","");
      });
      if(row!=1){
         var tab=new Table(row,2,"addedGroupTable","addedGroupBox");
         tab.setTable("90%","90%");
      }
      $("#addedGroupTable tr td").each(function(x){
        $(this).html(table[x]);
     });
    }else if(tableObj.length!=0){
     var current=[];
     var add_user_content=[],old_user=[],add_row=0,n=0;
     $("#addedGroupTable tr").each(function(d){
       if(d!=0){
         current.push($(this).children(":first").text());
       }
     });
    $(".usergroup_select select:last option").each(function(){
       var flag=false;
       for(var i in current){
         if(current[i]==$(this).text()){
            flag=true;
          }
        }
     if(!flag){
        add_user_content.push($(this).text());
        add_user_content.push($(this).val());
        //add_user_content.push($(this).attr("id"));
        //$(this).val($(this).text());
        //$(this).attr("id","");
        ++add_row;
       }
    });
    for(var j=0;j<add_row;j++){
      $("<tr><td></td><td></td></tr>").appendTo($("#addedGroupTable"));
    }
    $("#addedGroupTable tr td").each(function(m){
      //console.log($(this));
      if($(this).text()!=""){
       ++n;
      }else{
        $(this).text(add_user_content[m-n]);
      }
    });
  }
  });


}

function addCheckBox(obj,idName){
  //console.log(obj);
  var n=0,isAllChecked=true;
  //console.log(obj);
	
	if(obj.length!=0){
	  $.each(obj,function(k,v){
	    if(v=="0"){
		    isAllChecked=false;
		  }

     v=="1" ? $("<input type='checkbox'id='"+idName+"_"+k+"' name='"+k+"' value='"+v+"' style='display:none;' checked/>"+"<label class='checkboxText' style='display:none'></label>").appendTo($("#jqmContent")) : $("<input type='checkbox'id='"+idName+"_"+k+"' name='"+k+"' value='0' style='display:none;'/>"+"<label class='checkboxText' style='display:none'></label>").appendTo($("#jqmContent"));
    
	 });

	 if(!!isAllChecked){
	   $("#isAll_"+idName).val("1").attr("checked","true"); 
	 }
	}

}


function get_monitoritem_all_callback(json, textStatus, jqXHR){
  if(json==null){
   console.log("json null");
  }else{
   console.log(JSON.stringify(json));
   var type=[];
   for(var i in json){
    type.push(i);
		//console.log(json[i]);
    switch(i){
      case "generic" : addCheckBox(json[i],"generic");break;
      case "mysql"   : addCheckBox(json[i],"mysql");break;
      case "serving" : addCheckBox(json[i],"serving");break;
      case "daemon"  : addCheckBox(json[i],"daemon");break;
      case "report"  : addCheckBox(json[i],"report");break;
      case "mdn"     : addCheckBox(json[i],"mdn");break;
      case "hadoop"    : addCheckBox(json[i],"hadoop");break;
      case "mdb"     : addCheckBox(json[i],"mdb");break;
      case "jail"    : addCheckBox(json[i],"jail");break;
      case "gslb"    : addCheckBox(json[i],"gslb");break;
      case "security": addCheckBox(json[i],"security");break;
      case "monitor" : addCheckBox(json[i],"monitor");break;
    }
   }
   for(var j in type){
     $("<section id='"+type[j]+"Window'></section>").appendTo("#jqmContent");
    }

 
 }
}


//+++++++++++++<<
function get_district_all_callback(json, textStatus, jqXHR){
	var x = arguments.callee;
	var info = window._server_info;
	var options = '';
	var i;
	x.count = ( x.count || 0) + 1;
	if(!info && x.count < 10){
		setTimeout(function(){x(json, textStatus, jqXHR)},100);
		return;
	}
	if (json == null) {
		console.log("json null");
	}
	else {
		for(var i in json){
			options += '<option value="' + (i*1) +'"' + (i*1 == info.district*1 ? ' selected="selected"' : '') + '>' + json[i] + '</option>';
		}
		$("select[name='district']").html(options);
	}
}

function get_carrier_all_callback(json, textStatus, jqXHR){
	var x = arguments.callee;
	var info = window._server_info;
	var options = '';
	var i;
	x.count = ( x.count || 0) + 1;
	if(!info && x.count < 10){
		setTimeout(function(){x(json, textStatus, jqXHR)},100);
		return;
	}
	if (json == null) {
		console.log("json null");
	}
	else {
		for(var i in json){
			options += '<option value="' + (i*1) +'"' + (i*1 == info.carrier*1 ? ' selected="selected"' : '') + '>' + json[i] + '</option>';
		}
		$("select[name='carrier']").html(options);
	}
}
//+++++++++++++>>

function  monitorItemTab(parent,idText,title){
	$(".jqmWindow").css({"width":"750px"});
   //$("<section id='"+parent+"'></section>").insertBefore($("#jqmContent").children(":first"));
  var citeDate=$(".monitorList .jqModal").next("cite[id='"+idText+"']");
  var isAll=$(".monitorList input[id^='isAll_"+idText+"']"); //是否全选
	//console.log(isAll);
	if(citeDate.length!=0 && isAll.attr("checked")==false){  //解析之前的选择值checkbox
     //console.log(citeDate.text());
     var save=citeDate.text();
     //citeDate.remove();
     var data=save.split("|"); 
	   //console.log(data);
	   for(var e in data){
		   var item=data[e].split(":");
       var name=item[0],val=item[1]; //name val
		   
			 val=="1" ? $("#jqmContent input[id^='"+idText+"_"+item[0]+"']").attr("checked",true): $("#jqmContent input[id^='"+idText+"_"+item[0]+"']").attr("checked",false);//显示选中项
		 }
	 }


  $("#jqmContent input[id^='"+idText+"_']").each(function(i){ //显示window中对应待选项
      $(this).next("label").text($(this).attr("name"));
      var movText=$(this).next("label").clone(true); //复制文字
      var mov=$(this).clone(true);  //复制checkbox
      //console.log($("#isAll_"+idText).val());
			$(parent).append(mov.show());
      $(parent).append(movText.show());
      if((++i)%3==0){
        $("<br/>").appendTo($(parent));
       }
  });

  //$("<p>"+title+"</p><input type='checkbox' id='allChecked_"+idText+"'/><label class='checkboxText'>全选</label><br/>").insertBefore($(parent).children(":first"));
  $("<p>"+title+"</p>").insertBefore($(parent).children(":first"));
	$("<br/><div class='jqmClose'><input type='submit' id='"+"Sumbit"+idText+"_' value='确定'/></div>").appendTo($(parent));
  
  //console.log($("#jqmContent input[id^='"+idText+"_']"));
  
  $("#jqmContent input[id^='"+idText+"_']").bind("click",function(){
		 if($(this).attr("checked")==false){
	      //console.log($(".monitorList input[id^='isAll_"+idText+"']"));	 
		    //isAll.val("0").attr("checked","false");
		    $(".monitorList input[id^='isAll_"+idText+"']").val(0).attr("checked",false);
		 }
	
	});
  

  $("#Sumbit"+idText+"_").bind("click",function(){ //保存各项明细的选中项目
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
    $("<cite id='"+idText+"'>"+param+"</cite>").hide().insertAfter($("#"+idText));
  });
}

 
 $(document).ready(function(){
   var hostname=url_params("hostname"),version=url_params("version");
 //console.log(version+"");
   var domain=domainURI(document.location.href);
	 get_data_notLoad("http://"+domain+"/mmsapi"+version+"/get/server/@self_setting/"+hostname, editServerSuccessCallback);

   get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@self_group/"+hostname, "#groupBox", "loading_middle.gif", getGroupSuccessCallback);
   //获取监控项数据
   get_data_notLoad("http://"+domain+"/mmsapi"+url_params("version")+"/get/monitoritem/@all/"+hostname,get_monitoritem_all_callback);
   
   //++++++++++++++++<<
   //获取机房所在地区数据，设置下拉选项框
   get_data_notLoad("http://"+domain+"/mmsapi"+url_params("version")+"/get/district/@all",get_district_all_callback);
  //获取运营商数据，设置下拉选择框
   get_data_notLoad("http://"+domain+"/mmsapi"+url_params("version")+"/get/carrier/@all",get_carrier_all_callback);
   //++++++++++++++++>>
   
  $(".jqModal").each(function(){
     $(this).bind("click",function(){
      //  console.log($(this));
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
        case "hadoop"    : monitorItemTab("#hadoopWindow","hadoop","下表选择Hadoop的明细监控项目");break;
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
        }else if($(this).attr("checked")==false){ //不全选
           $(this).val("0");
					 $("#jqmContent input[id^='"+itemType+"_']").each(function(){  //
              $(this).val("0");
              $(this).attr("checked",false);   
					 });
        }
      });
     });

	 

  $("#modify_server_form").submit(function(){ //提交设置的数据
     var server_name = $("input[name='server_name']").val(),
               alias = $("input[name='alias']").val(),
							    ip = $("input[name='ip']").val(),
           auth_type = $("select[name='auth_type']").val(),
    upload_direction = $("select[name='upload_direction']").val(),
           monitored = $("select[name='monitored']").val(),
                memo = $("textarea[name='memo']").val(),
			district = $("select[name='district']").val(),
			 carrier = $("select[name='carrier']").val();
     var group="",len=$("#addedGroupTable").find("tr").length-1; //目前行数
     
		 $("#addedGroupTable tr").each(function(j){//分配服务器组
       if(j!=0){
         j!=len ? group+=$(this).children(":first").text()+"|" : group+=$(this).children(":first").text();
       }
     });
  
     var monitoritem="";  //监控项数据合并
     $(".monitorList .jqModal").each(function(i){
        //console.log($(this).attr("id"));
        var citeObj=$(this).next("cite"),item="",
            len=$("#jqmContent input[id^='"+$(this).attr("id")+"_']").length-1;
        if(i==0 && citeObj.length!=0){
           monitoritem+=$(this).attr("id")+"|"+$(this).next("cite").html();
         }else if(i==0 && citeObj.length==0){
           $("#jqmContent input[id^='"+$(this).attr("id")+"_']").each(function(j){
             //  console.log($(this).val());
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
  
  console.log(monitoritem);
     //console.log(group);
     var editServerParams={
         "alias"     : alias,
         "auth_type" : auth_type,
			    "ip" : ip,
  "upload_direction" : upload_direction,
         "monitored" : monitored,
              "memo" : memo,
             "group" : group,
       "monitoritem" : monitoritem,
	  	  "district" : district,
	       "carrier" : carrier       
   };    
     
    //console.debug(JSON.stringify(editServerParams));
     $.ajax({
           type: "post",
           url : "http://"+domain+"/mmsapi"+version+"/update/server/@self_setting/"+server_name,
           async: false,
           data:  editServerParams,
           success: function(data, textStatus, jqXHR){
						 //console.log(jqXHR.status+"right");
               if(jqXHR.status==200||jqXHR.status==205){
								 Tips("complete","修改服务器成功");
							 }
						 },
           error: function(jqXHR, textStatus, errorThrown){
              // console.log(jqXHR.status+"error");
							 switch(jqXHR.status){
                 case 400 : Tips("alert","数据不存在");break;
                 case 500 : Tips("alert","服务器出错");break;
								 case 409 : Tips("alert","用户名冲突");break;
               } 
						 }
          }); 

  }); 


	 $("#return").click(function(){
	   window.location.href="serverStatus.html?version="+version+"&name="+hostname;
	 });

});

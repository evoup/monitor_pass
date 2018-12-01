
function get_userGroup_groupId_Callback(json, textStatus, jqXHR){
  console.log(json);
  if(json==null){
	 console.log("json null");
	}else{
	 var permission=[],per_name=[],row=0;
	 for(var i in json){
	 if(i==1){
		$.each(json[i],function(j){
	   //console.log(j);
		 ++row; 
		 per_name.push("");
		 for(var m in json[i][j]){
		   //console.log(json[i][j][m]);
			if(m>=2){
			  //console.log(json[i][j][m]);
				continue; 
			 }
			permission.push(json[i][j][m]);
		 }
		 per_name.push(j);
		});
	 }
	}

	 var table=new Table(row,2,"add_usergroup_table","add_usergroup_box");
	 table.setTable("65%","100%");
	 $("#add_usergroup_table tr td").each(function(n){
	   //console.log(per_name);
		 if(n%2==0){
		   //console.log(per_name[n]);
			 $(this).html(permission[n]);
		 }else if(n%2==1){
       //console.log(per_name[n]);
		   var per=permission[n].split("|");
		   $(this).html("<select id='per"+n+"' name='"+per_name[n]+"' style='width:100px;'></select>");
			 for(var x in per){
				switch(per[x]){
				 case "1" : $("<option value='1'>无权限</option>").appendTo($("#per"+n+""));break;
				 case "2" : $("<option value='2'>读取</option>").appendTo($("#per"+n+""));break;
				 case "3" : $("<option value='3'>读取创建</option>").appendTo($("#per"+n+""));break;
				 case "4" : $("<option value='4'>读取修改</option>").appendTo($("#per"+n+""));break;
				 case "5" : $("<option value='5'>读取创建修改</option>").appendTo($("#per"+n+""));break;
				 case "6" : $("<option value='6'>读取创建修改删除</option>").appendTo($("#per"+n+""));break;
				}
			 }
   }
	 });
	 $("#add_usergroup_table tr:first").each(function(){
	     //console.log($(this));
			 $(this).css({"background":"#F0F0F0","color":"#4D89F9","font-weight":"normal"});
	 });
	}
}
function get_userAll_Callback(json, textStatus, jqXHR){
  //console.log(json);
  if(json==null){
   console.log("json null");
  }else{
   var user=[],desc=[],old_usergroup=[],option_user=[];
   for(var i in json){
    //console.log(json[i]);
    user.push(i);
		desc.push(json[i][2]);
	 }
	// console.log(desc);
  var gname=url_params("name");
  //console.log(gname);
  if(gname!=null){
  setTimeout(function(){
    get_usergroup_old(old_usergroup,true);
   /*setTimeout(function(){
    for(var x in old_usergroup){
      for(var j in user){
				if(old_usergroup[x]!=user[j]){
         option_user.push(user[j]);
       }
    } 
   }
   },1000);*/
  },800);
  } 
  //console.log(option_user);
  for(var j in user){
     $("<option value='"+desc[j]+"'>"+user[j]+"</option>").appendTo($(".usergroup_select select:first")); 
   }
  }
}
function get_userGroup_data_Callback(json, textStatus, jqXHR){
	if(json==null){
	  console.log("json null");
	}else{
		//console.log(json);
		var usergroup_name=url_params("name"),row1=1,edit_user_name=["主机名","说明"],selected=[],per_name=[],permission=[];
    $("#addUserGroup_form  input[name='groupname']").val(usergroup_name);
		$("#addUserGroup_form  textarea[name='desc']").val(json[0]);
    $.each(json[1],function(j){
      per_name.push("");
     for(var m in json[1][j]){
       //console.log(json[i][j][m]);
      if(m>=2){
        selected.push(json[1][j][m]);continue;
       }
      permission.push(json[1][j][m]);
     }
     per_name.push(j);
    });
    $.each(json[2],function(j){
     edit_user_name.push(j);
     edit_user_name.push(json[2][j]);
     ++row1;
   });
   //console.log(selected);
   setTimeout(function(){
     $("#add_usergroup_table tr td select").each(function(d){
       $(this).children("option[value='"+selected[d]+"']").attr("selected",true);
      });
   },500);
   var tableobj=$("#userGroup_member_table");
   if(tableobj.length==0 && row1!=1){
	   var table_1=new Table(row1,2,"userGroup_member_table","userGroup_member_box");
     table_1.setTable("90%","90%");
	  }
   $("#userGroup_member_table tr td").each(function(d){
     $(this).html(edit_user_name[d]);
   });
	}
	}


 function get_usergroup_old(old_usergroup,isadd){
  $("#userGroup_member_table tr").each(function(i){
   if(i==0){
     return;
   }else if(!!isadd){
     var old=$(this).children(":first").text();
     var oldDesc=$(this).children(":last").text();
		 //console.log(oldDesc);
		 $("<option value='"+oldDesc+"'>"+old+"</option>").appendTo($("#add_usergroup_old"));
     old_usergroup.push(old);
   }else if(!isadd){
     var old=$(this).children(":first").text();
     old_usergroup.push(old);
   }
 });
}

/*************************main 入口*************************************************************/
$(document).ready(function(){

	var groupname=url_params("name"),version=url_params("version"),isAddusergroup=true;
  var domain=domainURI(document.location.href);
	//console.log(groupname);
	//
	//if(groupname!=null){
   //get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/userGroup/@self/"+groupname,"#add_usergroup_box","loading_middle.gif",get_userGroup_data_Callback);
   //isAddusergroup=false;
   //$("#addUserGroup_form input[name='groupname']").attr("readonly", "readonly");
   //$("#addUserGroup_form input[name='groupname']").attr("disabled", "disabled");
   //$("div.title").html("修改数据收集器"); 
    
//}	
   
  //get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/userGroup/@self","#add_usergroup_box","loading_middle.gif",get_userGroup_groupId_Callback); 
  //get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/datacollector/@self","#add_usergroup_box","loading_middle.gif",get_userGroup_groupId_Callback); 
  //get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/user/@allmember","#add_userall_box","loading_middle.gif",get_userAll_Callback);
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/datacollector/@allmember","#add_userall_box","loading_middle.gif",get_userAll_Callback);

/****************************添加用户组动作****************************************/
  $("#add_option").bind("click",function(){
	 // event.stopPropagation(); 
		addSelectOptionAction("#userGroup_member_table");//添加弹出框Select从左到右的移动
	});

  $("#del_option").bind("click",function(){
	 // event.stopPropagation();
		delSelectOptionAction("#userGroup_member_table");//左移用户
  });

/**************************add_usersgroup memeber 添加到表格里**********************************************************/	
  $("#add_user_submit").click(function(){
		//event.stopPropagation();
		var obj=$("#userGroup_member_table"),current=[];
	  if(obj.length==0){
		  var add_user_content=["主机名","说明"],row=1;
      $(".usergroup_select select:last option").each(function(){
        add_user_content.push($(this).text());
        add_user_content.push($(this).val());
        //$(this).val($(this).text());
        ++row;
      }); 
			if(row!=1){
			   var t=new Table(row,2,"userGroup_member_table","userGroup_member_box");
	  	   t.setTable("90%","90%");
			 }
	    $("#userGroup_member_table tr td").each(function(m){
	     $(this).html(add_user_content[m]);	
		  });
		}else if(obj.length!=0){  //扩展添加一行
			//console.log($("#userGroup_member_table tr td:last"));
		  var add_user_content=[],old_user=[],add_row=0,n=0;
      get_usergroup_old(old_user,false);
      $(".usergroup_select select:last option").each(function(){
       var flag=false; 
       for(var i in old_user){
         if(old_user[i]==$(this).text()){
            flag=true;
          }
        }
     if(!flag){
        add_user_content.push($(this).text());
        add_user_content.push($(this).val());
				//$(this).val($(this).text());
        ++add_row;
       }
    });
    //console.log(add_user_content);
    for(var j=0;j<add_row;j++){
     $("<tr><td></td><td></td></tr>").appendTo($("#userGroup_member_table"));
    }
		var rowNum=$("#userGroup_member_table tr").length;
		//console.log(rowNum);
		var addNum=rowNum-add_row;//添加的单元格下标
		//console.log(addNum);
    $("#userGroup_member_table tr td").each(function(m){
      //console.log($(this));
      if(m<2*addNum){ 
        ++n;
      }else{
        $(this).text(add_user_content[m-n]); //从计算的位置添加单元格内容
        }
    });
     //$("#userGroup_member_table tr td:last");
		}
	});


  
	 $("#return").click(function(){  //return

     window.location.href="datacollector.html?version="+version;

  });
 
   $("input[name='groupname']").blur(function(){
	    var name=$(this).val();
			if(!!isChina(name)){  //字符串含有中文
	      Tips("alert","数据收集器名不能含有中文");		
			}else{
			  $("#Tips").hide();
			}
	 });	
	/****************************submit editServers Form **********************************************************************/
  $("#addUserGroup_form").submit(function(){
    var datacollectorname_val=$("input[name='groupname']").val();
    var interface=$("input[name='interface']").val();
		var desc_val=$("textarea[name='desc']").val();
    var _url;
		$("#add_usergroup_box select").each(function(x){
		  var pri=$(this).attr("name")+"#"+$(this).val();
		});
    var member=[];
		$(".usergroup_select select:last option").each(function(){
			 member.push($(this).text());
		 });
		 var member_param=member.join("|");
		 var add_usergroup_param={
      "interface" : interface,
			"memberServers" : member_param
		 };
		 if(!!isAddusergroup){
        _url="http://"+domain+"/mmsapi"+version+"/create/datacollector/@self/"+datacollectorname_val;
     }else{
       _url="http://"+domain+"/mmsapi"+version+"/update/datacollector/@self/"+datacollectorname_val;
     }
     $.ajax({
       type: "post",
       url : _url,
       async: false,
       data: add_usergroup_param,
       success: function(data, textStatus, jqXHR){
           //alert("success");
          if(jqXHR.status==200||jqXHR.status==205){
		   		   //alert("create success");
					  Tips("complete","添加/修改数据收集器成功!"); 
					}
			  },
       error: function(jqXHR, textStatus, errorThrown){
           //console.log(jqXHR.status);
			  	 switch(jqXHR.status){
               case 400 : Tips("alert","数据不存在");break;
               case 500 : Tips("alert","服务器出错");break;
							 case 409 : Tips("alert","账号已经存在");break;
             } 
				}
      }); 
 
	});	
});

function get_userGroup_all_Callback(json, textStatus, jqXHR){
	if(json==null){
	  console.log("服务器数据为空");
	}else{
		var data=["用户组名","用户组描述","成员用户","操作"],row=1;
	  for(var i in json){
		 //console.log(i);
		 data.push(i);
		 ++row;
		 $.each(json[i],function(j){
	     //console.log(json[i][j]);
			 data.push(json[i][j]);
			 if(j==1){
			   data.pop();
				 if(json[i][j].length!=0){
		       data.push(json[i][j].join(","));
				 }else{
				  data.push("");
				 }
		   }
			 if(j==2){
			 // console.log(json[i][j]);
				data.pop();
				if(json[i][j]==0){
					data.push("Modify");
			  }else if(json[i][j]==1){
				  data.push("Delete,Modify");
				}
			 }
		 });
		}
   //console.log(data);
	 var table=new Table(row,4,"userGroup_all_table","userGroup_all"), ver=url_params("version");//创建表格
	 table.setTable("100%","100%");
	 $("#userGroup_all_table tr td").each(function(n){//将数据显示在表格内
	     if(data[n]=="Modify"){
          $(this).html("<a href='javascript:void(0)'><div id='modifyIcon'></div></a>");
       }else if(data[n]=="Delete,Modify"){
		      $(this).html("<a href='javascript:void(0)'><div id='modifyIcon'></div></a><a href='javascript:void(0)'><div id='deleteIcon'></div></a>");
		   }else{ 
          $(this).html(data[n]);
      }
	 });


   $("#userGroup_all_table tr td a #deleteIcon").each(function(m){ //绑定删除事件
     $(this).bind("click",function(){
      var del_groupname=$(this).parent().parent().parent().children("tr td:nth-child(1)").text();
      var domain=domainURI(document.location.href);
			//console.log(del_groupname);
      
			del_data("http://"+domain+"/mmsapi"+ver+"/delete/userGroup/@self/"+del_groupname);
      get_data_ajax("http://"+domain+"/mmsapi"+ver+"/get/userGroup/@all","#userGroup_all","loading_middle.gif",get_userGroup_all_Callback);
      Tips("complete","删除用户组成功!");
     });
   });



 $("#userGroup_all_table tr td a #modifyIcon").each(function(m){ //绑定修改事件
    $(this).bind("click",function(){
        var usergroup_name=$(this).parent().parent().parent().children("tr td:nth-child(1)").text();
        //console.log(usergroup_name);
        window.open("../configure/add_edit_usergroup.html?name="+usergroup_name+"&version="+ver,target="_self");
        //$(this).attr("href","../servers/modifyServers.html?name="+servers_name);
      });
    });
	}
}
$(document).ready(function(){
   var version=url_params("version");
   var domain=domainURI(document.location.href); //域名

	 get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/userGroup/@all","#userGroup_all","loading_middle.gif",get_userGroup_all_Callback); 

});

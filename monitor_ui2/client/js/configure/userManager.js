 function get_user_list_Callback(json, textStatus, jqXHR){
   if(json==null){
	   console.log("json null");
	 }else{
		var list=["用户名","真实名字","Email","用户描述","操作"],row=1;
	  for(var i in json){
		 list.push(i);
		 $.each(json[i],function(j){
	    list.push(json[i][j]);	 
		  if(j==3){
			  list.pop();
				switch(json[i][j]){
				 case 0 : list.push("Modify");break;
				 case 1 : list.push("Modify|Delete");break;
				}
			}
		 });
		 ++row;
		 }
		//console.log(list);
		var table=new Table(row,5,"userList_table","userList_box");
		table.setTable("100%","100%");
		$("#userList_table tr td").each(function(m){
		  switch(list[m]){
			 case "Modify" : $(this).html("<a href='javascript:void(0)'><div id='modifyIcon'></div></a>");break;
			 case "Modify|Delete" : $(this).html("<a href='javascript:void(0)'><div id='modifyIcon'></div></a><a href='javascript:void(0)'><div id='deleteIcon'></div></a>");break;
			 default : $(this).html(list[m]);break;
			}
			//$(this).html(list[m]);
		});

    var version=url_params("version");
		var domain=domainURI(document.location.href);
    $("#userList_table tr td a #modifyIcon").each(function(m){
			$(this).bind("click",function(){
        var user_name=$(this).parent().parent().parent().children("tr td:nth-child(1)").text();
        //console.log(user_name);
				window.open("../configure/UserForm.html?name="+user_name+"&version="+version,target="_self");
      });
    });


    $("#userList_table tr td a #deleteIcon").each(function(m){
     $(this).bind("click",function(){
        var del_user=$(this).parent().parent().parent().children("tr td:nth-child(1)").text();
        //console.log(del_user);
				del_data("http://"+domain+"/mmsapi"+version+"/delete/user/@self/"+del_user);
        //window.reload();
		    get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/user/@all","#userList_box","loading_middle.gif",get_user_list_Callback);
				Tips("complete","删除用户成功!");
		 });
    });
    
	 }
 }

$(document).ready(function(){
  var ver=url_params("version");
	var domain=domainURI(document.location.href);
	get_data_ajax("http://"+domain+"/mmsapi"+ver+"/get/user/@all","#userList_box","loading_middle.gif",get_user_list_Callback);
 
});

/***********************Ajax Post data****************************************/
var post_data=function(url,async,data){
  var _url=url,_data=data,_async=async;
  //console.log(_data);
	$.ajax({
      type: "post",
      url : _url,
      async: _async,
      data: _data,
      success: function(data, textStatus, jqXHR){
        if(jqXHR.status==200||jqXHR.status==205){
			    Tips("complete","更新数据成功!");	
				}	
			},
      error: function(jqXHR, textStatus, errorThrown){
        switch(jqXHR.status){
           case 400 : Tips("alert","数据不存在!");break;
           case 500 : Tips("alert","服务器出错");break;
           case 502 : Tips("alert","服务器程序错误");break;  
				}       
 			}
     }); 

};
/***************************************************************/

/*********************delete data ******************************************/
var del_data=function(url){
	$.ajax({
      type: "get",
      url : url,
      async: false,
      success: function(data, textStatus, jqXHR){
        //console.log(jqXHR.status);
        if(jqXHR.status=="200"){
					//window.location.reload();
					 $("table").remove();
					//Tips("complete","已经成功删除数据!");
					} 
			},
      error: function(jqXHR, textStatus, errorThrown){
        switch(jqXHR.status){
           case 400 : Tips("alert","数据不存在");break;
           case 500 : Tips("alert","服务器出错");break;
				}
      }
     }); 
};




/***************************************************************/
function get_data_ajax(url, load_parent_id, load_image, successCallback){
 $.ajax({
      type: "get",
       url:  url,
     async:  true,
     cache:  false,
  dataType: "json",
beforeSend: function(){
     var load_div=$("<div id='loading'><img src='../images/"+load_image+"'/></div>");
      $(load_parent_id).append(load_div);
       },
   success: successCallback,         
  complete: function(jqXHR, textStatus){
		        //if(jqXHR.status==200){
                $("#loading").remove();
            //  }
						 },
	error: function(jqXHR, textStatus, errorThrown){
	   switch(jqXHR.status){
         case 400 : Tips("alert","数据不存在");break;
         case 500 : Tips("alert","服务器出错");break;      
		     
		 }
		  }
   }); 

}




function get_data_notLoad(url, successCallback){
  $.ajax({
      type: "get",
       url:  url,
     async:  true,
     cache:  false,
  dataType: "json",
   success: successCallback,
  error: function(jqXHR, textStatus, errorThrown){
     switch(jqXHR.status){
       case 400 : Tips("alert","数据不存在");break;
       case 500 : Tips("alert","服务器出错");break;   
      }
      }
   });
 
}

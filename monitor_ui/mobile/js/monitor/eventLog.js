function SuccessCallback(json,textStatus,jqXHR){  //getLogAllInfo
  if(json==null){
	  console.log("json null");
	}else{
	  //console.log(json);
	  $.each(json,function(key,value){
		  if(key=="record"){
			  //console.log(value);
			  var data=["等级","发生时间","内容"],row=1;
				$.each(value,function(k,v){
				 // console.log(v);
					++row;
			    $.each(v,function(sub,elem){
					 // console.log(elem);
					 data.push(elem);
					});	
				});
			 //console.log(data);
			 var tableObj=$("#eventLogTable");
	     if(tableObj.length==0){
           var table=new Table(row,3,"eventLogTable","logEventBox");
	         table.setTable("100%","100%");
			  }
       $("#eventLogTable tr td").each(function(i){
          if(i!=0 && i%3==0){
			       $(this).html("<div class='logRankImage' id='logRank"+data[i]+"'></div>");		  
					}else{
				     $(this).text(data[i]);
					}
        });
	      /*var pagerObj=$("#page_info"),ver=url_params("version");
        if(pagerObj.length==0){
           pager("http://211.136.105.207:8282/mmsapi"+ver+"/get/log/@all",ver,"#logEventBox");
         }else if(pagerObj.length!=0){
           //console.log("remvoe");
           pagerObj.remove();
           pager("http://211.136.105.207:8282/mmsapi"+ver+"/get/log/@all",ver,"#logEventBox");
        }*/
			}
    });		
    /*$.each(json,function(key,value){ //page info  
       if(key=="page_info"){
         $.each(value,function(k,v){
            switch(k){
              case "total_page"     : $("#total_page").text(v);break;
              case "current_page"   : $("input[name='current_page']").val(v);break;
              case "line_per_page"  : $("select[name='line_per_page']").children("option[value='"+v+"']").attr("selected",true);break;
              //case "next_page"     : $("input[name='next_page']").val(v);break;
              //case "prev_page"     : $("input[name='prev_page']").val(v);break;
                }
            });
          }
         });*/
	}

} 




$(document).ready(function(){
	var ver=url_params("version");
  var domain=domainURI(document.location.href);
	get_data_ajax("http://"+domain+"/mmsapi"+ver+"/get/log/@all","#logEventBox","../images/loading_middle.gif",SuccessCallback);
  // pager("http://211.136.105.207:8282/mmsapi"+ver+"/get/log/@all",ver,"#eventLogTable");
});


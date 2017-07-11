 function postPageInfo(url,info,ver,name){//ajax 上传分页信息
   var _url=url,_info=info,_ver=ver;
   $.ajax({
        type : "post",
         url : _url,
       async : false,
        data : _info,
     success : function(json, textStatus, jqXHR){
           if(jqXHR.status==200){
            console.log("上传页数数据成功");
           // console.log(json);
            $(name).children("table").remove();
            SuccessCallback(json,textStatus,jqXHR);
         }
        },
       error : function(jqXHR, textStatus, errorThrown){
          switch(jqXHR.status){
             case 400 : alert("Data NOT Found!!!");break;
             case 500 : alert("Server Error!!!");break;
          }
      }
   });

}
function createPagerStyle(obj){//创建分页导航条样式
  var pagerBar=$("<div id='page_info' style='width:100%'><ul><li><a href='javascript:void(0)' id='find' style='background-position:-16px 0px;'></a></li><img src='../images/line.gif' height='18px' style='margin-top:4px;'/><li><select name='line_per_page'><option value='5'>5 Per Page</option><option value='10'>10 Per Page</option><option value='15'>15 Per Page</option><option value='20'>20 Per Page</option><option value='50'>50 Per Page</option><option value='100'>100 Per Page</option><option value='1000'>1000 Per Page</option></select></li><img src='../images/line.gif' height='18px' style='margin-top:4px;'/><li><a href='javascript:void(0)' id='first_page' style='background-position:-32px 0px;'></a><a href='javascript:void(0)' id='prev_page' style='background-position:-210px 0px;'></a></li><img src='../images/line.gif' height='18px' style='margin-top:4px;'/><li>第<input type='text' name='current_page'/>页，共&nbsp<cite id='total_page' style='font-style:normal'></cite>&nbsp页</li><img src='../images/line.gif' height='18px' style='margin-top:4px;'/><li><a href='javascript:void(0)' id='next_page' style='background-position:-178px 0px;'></a><a href='javascript:void(0)' id='last_page' style='background-position:-48px 0px;'></a></li><img src='../images/line.gif' height='18px' style='margin-top:4px;'/><li><a href='javascript:void(0)' id='go' style='background-position:-82px 0px;'></a></li></ul><div>");
	  //ul.appendTo(div);
		//console.log($(obj));
    pagerBar.appendTo($(obj));

}

function pager(url,version,tableName){//Next,Prev,first,last,go Click模块
  createPagerStyle(tableName);   

  $("#next_page").click(function(){
    //console.log($(this));
    var current=Number($("input[name='current_page']").val()),
        next=current+1,
        line_per_page=$("select[name='line_per_page']").val(),
        total=$("#total_page").text();
    var pageNextInfo={
       "line_per_page" : line_per_page,
       "current_page"  : next
    };
    if(current!=total){
       //console.log(pageNextInfo);
       postPageInfo(url,pageNextInfo,version,tableName);
      }
   });


 $("#prev_page").click(function(){
   var current=Number($("input[name='current_page']").val());
   var prev=current-1,
       line_per_page=$("select[name='line_per_page']").val();
   var pagePrevInfo={
       "line_per_page" : line_per_page,
       "current_page"  : prev
    }
   if(current!=1){
      //console.log(pagePrevInfo);
      postPageInfo(url,pagePrevInfo,version,tableName);
   }
 });


 $("#go").click(function(){
   var current=Number($("input[name='current_page']").val()),
       line_per_page=$("select[name='line_per_page']").val(),
       total=$("#total_page").text();
   var pageCurrentInfo={
       "line_per_page" : line_per_page,
       "current_page"  : current
  };
   if(current>=1 && current<=total){
      postPageInfo(url,pageCurrentInfo,version,tableName);
   }
 });


 $("#first_page").click(function(){
  var line_per_page=$("select[name='line_per_page']").val();
  var pageFirstInfo={
     "line_per_page" : line_per_page,
     "current_page"  : 1
   }
   postPageInfo(url,pageFirstInfo,version,tableName);
 });


 $("#last_page").click(function(){
  var line_per_page=$("select[name='line_per_page']").val(),
      last_page=$("#total_page").text();
  var pageLastInfo={
     "line_per_page" : line_per_page,
     "current_page"  : last_page
   }
   postPageInfo(url,pageLastInfo,version,tableName);
 });

}

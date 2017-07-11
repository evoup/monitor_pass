/**************绑定导航栏点击效果************************/
var bindClickNav=function(){ 
  var ver=url_params("version"); 
	var menu=["about/about.html?version="+ver,"server/server.html?version="+ver,"monitor/monitor.html?version="+ver,"ue/ue.html?version="+ver,"configure/configure.html?version="+ver,"download/download.html?version="+ver/*,"manager/Manager.html?version="+ver*/];
   $("#top_navigation ul>li a").each(function(i){
    $(this).bind("click",function(){       
      $("iframe").attr("src",menu[i]);
     // contentIframeHeight();
			$(this).attr("class","myClick");
			$("#top_navigation ul>li a").not($(this)).attr("class","");
    });
   }); 
  };




/*****************动态生成任意行/列的表格********************************/
var Table=function(row, col, id, parent){
  this.row=row;
  this.col=col;
  this.id=id;
  this.parent=parent;
};
Table.prototype.setTable=function(w, h){
   var _id=this.id;
   var _parent=this.parent;
   var table=$("<table cellpadding='0' cellspacing='0'></table>"),tbody=$("<tbody></tbody>");
   table.attr("width",w);
   table.attr("height",h);
   table.attr("id",_id);
	 for(var j=0;j<this.row;j++){
    var r=$("<tr></tr>");
    for(var m=0;m<this.col;m++){
     var c=$("<td></td>");
     r.append(c);
    }
    tbody.append(r);
   }
   table.append(tbody);
   //$("#"+_parent+"").append(table);
   table.appendTo($("#"+_parent+""));   
};
/* function FillTd(row,col,id,parent,w,h,summary,jsonString){
   console.log($("td").length);
	 var table=new Table(row,col,id,parent);
    table.setTable(w,h);
    for(var i in jsonString){
        summary.push(jsonString[i]);
       }
    for(var x in summary){
       if(summary[x]==undefined||summary[x]==null){
           break;
       }else{
         var td=$("td");
           for(j=0;j<td.length;j++){
             td[j].html(summary[j]);
           }
         }
       }
  }*/
/*****************************************************/





/***************Table分页**************************************/
 function TablePage(id, size){
   var $table = $(id);
    var currentPage = 0;  //当前页
    var pageSize = size;  //每页行数（不包括表头）
		$table.bind("repaginate", function()
    {
       //console.log($table.find("tbody tr").eq(0)); 
			$table.find("tbody tr").hide().slice(currentPage * pageSize, (currentPage + 1) * pageSize).show();
      $table.find("tbody tr").eq(0).show();
		});
    var numRows = $table.find("tbody tr").length;  //记录宗条数
    var numPages = Math.ceil(numRows/pageSize);    //总页数
    //console.log(numPages);
    var $pager = $("<div class='page'><a href='javascript:void(0)'><span id='Prev' style='margin-right:4px;'>« Prev</span></a></div>");  //分页div
    for( var page = 0; page < numPages; page++ )
    {
			 
			//为分页标签加上链接
      //if(page==0){$("<a href='javascript:void(0)'><span id='1' class="click_page"></span></a>")}
			$("<a href='javascript:void(0)'><span id='"+(page+1)+"'>"+ (page+1) +"</span></a>")
				   .bind("click", { "newPage": page }, function(event){           
                currentPage = event.data["newPage"];                  
                //console.log($(this).children("span"));
								$(this).children("span").attr("class","click_page");
                $(this).children("span").css({"color":"#FFFFFF"});
								$(".page a span").not($(this).children("span")).attr("class","");
								$(".page a span").not($(this).children("span")).css({"color":"#1155BB"});
								$table.trigger("repaginate");             
            })
						.appendTo($pager); 
         
        $pager.append("  ");
		}
		//$table.trigger("repaginate");
		var next=$("<a href='javascript:void(0)'><span id='Next'>Next »</span></a>");
    $pager.append(next);
		$pager.insertAfter($table);//分页div插入table
		$("#1").attr("class","click_page");
		$("#1").css({"color":"#FFFFFF"});
		$table.trigger("repaginate");
    //console.log($("#1"));
		//$("#1").attr("class","click_page");
		//$("#1").css({"background":"#FFFFFF"});
		$("#Prev").bind("click",function(){
       var prev=Number($(".click_page").text())-2;
       currentPage=prev;
       $(this).css({"background":"#000000"});
			 if(currentPage<0) {
         $(this).css({"background":"#c0c0c0"});
        // return;
         }
       $("#"+(prev+1)).attr("class","click_page");
       $("#"+(prev+1)).css({"color":"#FFFFFF"});
       $(".page a span").not($("#"+(prev+1))).attr("class","");
       $(".page a span").not($("#"+(prev+1))).css({"color":"#1155BB"});
       //console.log(currentPage);
       $table.trigger("repaginate"); 
    });
     $("#Next").bind("click",function(){
       var next=$(".click_page").attr("id");
       currentPage=Number(next);
			 //console.log($(".click_page").text());
       $(this).css({"background":"#FFFFFF"});
			 if((currentPage+1)>numPages) {
         $(this).css({"background":"#c0c0c0"});
         return;
         }
       $("#"+(currentPage+1)).attr("class","click_page");
       $("#"+(currentPage+1)).css({"color":"#FFFFFF"});
       $(".page a span").not($("#"+(currentPage+1))).attr("class","");
       $(".page a span").not($("#"+(currentPage+1))).css({"color":"#1155BB"});
       $table.trigger("repaginate");
    }); 
		//$table.trigger("repaginate");  //初始化 
 }




 /*******************左侧导航栏点击效果**********************************/
  var bindClickMenu=function(id, menu){
   //var menu=["overview.html","engine.html","mdb.html","topological.html"];
   $(".menu_list .menu_body a").each(function(i){
     //console.log($(this));
     $(this).bind("click",function(){
      $(id).attr("src",menu[i]);
      $(this).attr("class","click_visited");
			$(this).css({"color":"#ffffff"});
      $(".menu_list .menu_body a").not($(this)).attr("class","");
      $(".menu_list .menu_body a").not($(this)).css({"color":"#5077A6"});
		 });
   });
  };





/*********************Table显示或者隐藏效果********************************/
 var show_hide_Table=function(){
   $(".table_container .subtitle .zoomIcon").each(function(j){
     //console.log($(this));
     $(this).bind("click",function(){
       // console.log($(this).parent().next() );
        if($(this).css("background-position")=="-240px 0px"){
           $(this).parent().next().hide();
           $(this).css("background-position","-256px 0px");
        }else if($(this).css("background-position")=="-256px 0px"){
           $(this).parent().next().show();
           $(this).css("background-position","-240px 0px");

        }
        });
   }); 
 };





/*************************Tab 选项卡*********************************************/
 function Tabs(){
  $(".tabs .tabs_menu ul li").each(function(i){
       //console.log($(this));
       if(i==0){
         $(this).css({"background":"#698CB8","padding-bottom":"6px","color":"#FFFFFF"});
			  // $(this).addClass("addTabsMenuFocus");
			 }
       $(this).bind("click",function(){
         
				 $(this).css({"background":"#698CB8","padding-bottom":"6px","color":"#FFFFFF"});
				//  $(this).addClass("addTabsMenuFocus");
				 
				 $(".tabs .tabs_menu ul li").not($(this)).css({"background":"#FFFFFF","padding-bottom":"5px","color":"#6C8FBA"});
         
				 //$(".tabs .tabs_menu ul li").not($(this)).addClass("addTabsMenu");
				 var current="#"+$(this).attr("id")+"_content";
         $(current).show();
				 //console.log($(current));
         $(".tabs .tabs_content").children().not($(current)).hide();
         });
     }); 
 }




/****************匹配url参数***************************************************/
 function url_params(param){ //匹配url参数
   var reg = new RegExp("(^|&)" + param + "=([^&]*)(&|$)","i");
   var r = window.location.search.substr(1).match(reg);
   if (r!=null) return decodeURIComponent(r[2]);return null;
 } 


/*****************************************************/

/* function contentIframeHeight(){ 
	//contentIframe Height
  var notIframeHeight=$("#header").height()+$("#top_navigation").height()+$("#footer").height()+30,
	    iframeHeight=$(window).height()-notIframeHeight;
	 //console.log(iframeHeight);
	 $("#contentIframe").height(iframeHeight);
}*/
 
//////////////////////自适应布局////////////////////////////////////////

function contentHeight(){//content/main  Height
     //console.log($(window).height()+"window");
     $(".main").height($(window).height()-5);
     $("#rightIframe").height($(window).height()-20);
     $("#left").height($(window).height()-20);
     $("#right").height($(window).height()-20);
   }

/*****************提示框******************************************/
function Tips(tipStatus,info){//status:alert/complete;
	//console.log(tipStatus+"hahahh");
   var tips=$("#Tips");
   if(tips.attr("class")!=""){
      tips.children().remove();
	    tips.attr("class","");
   var content=$("<div></div><cite></cite>");
	 content.appendTo(tips);
   tips.hide();
   }
   if(tips.css("display")=="none"){
      tips.slideDown("fast");
	 }
	 tips.attr("class",tipStatus);
	/* switch(tipStatus){
	    case "alert"    : $("#Tips img").attr("src","../images/wrong_icon.png");break;
		  case "complete" : $("#Tips img").attr("src","../images/right_icon.png");break;
	
	 }*/
	 $("#Tips cite").text(info);

}


////////////////////////显示时钟模块//////////////////////////////////////////
function systemDate(){
        var date=new Date(); //获取进入系统平台的时间
        var month=date.getMonth()+1;
        var minutes=date.getMinutes(),Min;
        var seconds=date.getSeconds(),updateTime;
        //console.log(seconds);
        updateTime=60-seconds;
        minutes<=9 ? Min="0"+minutes : Min=minutes;
      /*var week=date.getDay(),day;
      switch(week){
        case 1 : day="星期一";break;
        case 2 : day="星期二";break;
        case 3 : day="星期三";break;
        case 4 : day="星期四";break;
        case 5 : day="星期五";break;
        case 6 : day="星期六";break;
        case 7 : day="星期日";break;
      }*/
        var time=date.getFullYear()+"/"+month+"/"+date.getDate()+" "+date.getHours()+":"+Min;
        $("#updateTime").text(time);
        setTimeout('systemDate()',updateTime*1000);
 }



///////////////////检测字符串中是否含有中文//////////////////////////////////
function isChina(s){ 
     var patrn=/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/gi; 
     if(!patrn.exec(s)){ 
        return false; //不包含中文 
      }else{  
        return  true; //包含中文 
      } 
} 


///////////////////////////截取域名////////////////////////////////////////////
function domainURI(str){
	  var durl=/http:\/\/([^\/]+)\//i;
	  domain = str.match(durl);
    return domain[1];
}


///////////////////////////连接字符////////////////////////////////////////////
function join (glue, pieces) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Waldo Malqui Silva
    // +   improved by: Itsacon (http://www.itsacon.net/)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: implode(' ', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: 'Kevin van Zonneveld'
    // *     example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'});
    // *     returns 2: 'Kevin van Zonneveld'
    var i = '',
        retVal = '',
        tGlue = '';
    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }
    if (typeof(pieces) === 'object') {
        if (Object.prototype.toString.call(pieces) === '[object Array]') {
            return pieces.join(glue);
        } 
        for (i in pieces) {
            retVal += tGlue + pieces[i];
            tGlue = glue;
        }
        return retVal;
    }
    return pieces;
}

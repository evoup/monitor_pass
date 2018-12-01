 
/*********************Tabs 添加用户组成员的动作***********************************/
function add_memberAction(TableName,BoxName){
  //$("#add_user_submit").click(function(){
   //var objTable=$("#addMemberTable");
   var objTable=$("#"+TableName);
	 if(objTable.length==0){
     var table=["用户组名","描述","成员用户"],row=1,addMember=[];
     $(".usergroup_select select:last option").each(function(){
       table.push($(this).text());
       table.push($(this).val());
       table.push($(this).attr("id"));
    //  addMember.push($(this).text());
       ++row;
       $(this).val($(this).text());
       $(this).attr("id","");
      });
			if(row!=1){ //没有数据
         var tab=new Table(row,3,TableName,BoxName);
         tab.setTable("90%","90%");
      } 

			$("#"+TableName+" tr td").each(function(x){
         $(this).html(table[x]);
      });
    }else if(objTable.length!=0){
     var current=[];
     var add_user_content=[],old_user=[],add_row=0,n=0;
     $("#"+TableName+" tr").each(function(d){
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
        add_user_content.push($(this).attr("id"));
        $(this).val($(this).text());
        $(this).attr("id","");
        ++add_row;
       }
    }); 
   for(var j=0;j<add_row;j++){
     $("<tr><td></td><td></td><td></td></tr>").appendTo($("#"+TableName));
    }
    $("#"+TableName+" tr td").each(function(m){
      //console.log($(this));
      if($(this).text()!=""){
        ++n;
      }else{
        $(this).text(add_user_content[m-n]);
        }
    });

    }
// });
}

/*******************添加Select Option Action********************************************/
function addSelectOptionAction(TableName){
 if($(".usergroup_select select:first :selected").attr("selected")==true){  //左边的select必须有选中项，否则不执行
   var addmember=[],is_add=false;
   var move_text=$(".usergroup_select select:first :selected").text(); //选中名称
   var move_desc=$(".usergroup_select select:first :selected").val();  //选择描述
   var move_member=$(".usergroup_select select:first :selected").attr("id");
   $(".usergroup_select select:last option").each(function(){
     addmember.push($(this).text());
     if($(this).text()==move_text){
       is_add=true;
     }
    });
    if(!is_add){
       $("<option id='"+move_member+"' value='"+move_desc+"'>"+move_text+"</option>").appendTo($(".usergroup_select select:last"));
       $(".usergroup_select select:first :selected").remove(); 
      }else{
       alert("已经添加好了");
    }
		// });	
   if($(".usergroup_select select:first option").length==0){
     $("#add_option").css({"opacity" : "0.6"});
     $("#add_option").unbind("click");
   }
   if($(".usergroup_select select:last option").length!=0){
     $("#del_option").css({"opacity" : "1"});
     $("#del_option").unbind("click");
     $("#del_option").bind("click",function(){
       //event.stopPropagation(); 
			 delSelectOptionAction(TableName);
     });
   }
  }
}

/****************删除选择框内的元素****************************/
function delSelectOptionAction(tableId){
	
	if($(".usergroup_select select:last :selected").attr("selected")==true){
    var isMoveElement=[],isSelected,isMove=false,delObj;//删除对象
    delObj=$(".usergroup_select select:last :selected");
	  var moveText=delObj.text();
    var move_desc=delObj.val();
    var move_member=delObj.attr("id");
		if($(".usergroup_select select:first option").length!=0){
      $(".usergroup_select select:first option").each(function(){
         isMoveElement.push($(this).text());//
       });
     }
    for(var i in isMoveElement){ //有选中的元素
      if(moveText==isMoveElement[i]){
        isMove=true;   
        delObj.remove();  
      }
     }

    if(isMove==false){ //没有选中的元素
      $("<option id='"+move_member+"' value='"+move_desc+"'>"+moveText+"</option>").appendTo($(".usergroup_select select:first")); 
     } 
    delObj.remove(); 
 //console.log($(tableId+" tr").find(":contains('"+moveText+"')").parent()); 
    $(tableId+" tr").find(":contains('"+moveText+"')").parent().remove();  
    if($(tableId+" tr").length<=1){
        $(tableId).remove();
     }
 
    if($(".usergroup_select select:first option").length!=0){
       $("#add_option").css({"opacity" : "1"});
       $("#add_option").unbind("click");
       $("#add_option").bind("click",function(){
         //event.stopPropagation(); 
				 addSelectOptionAction(tableId); 
        });
        
     }

    if($(".usergroup_select select:last option").length==0){
       $("#del_option").css({"opacity" : "0.6"});
       $("#del_option").unbind("click"); 
     }

   }
 }

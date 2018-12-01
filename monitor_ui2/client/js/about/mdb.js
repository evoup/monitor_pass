window.onload=function(){
 $.ajax({
          type: "get",
          url : "http://211.136.105.207:8282/mmsapi/get/status/@mdb",
          async: true,
          dataType:"json",
          success: function(json, textStatus, jqXHR){//如果调用php成功
            // console.log(json);
            var str=[],on=0,down=0,total;
            for(var one=0;one<json.length;one++){
              if(json[one].status===1){
                 on++;
               }else if(json[one].status==0){
                 down++;
               }
               str[one]=json[one].name;
             }
             total=on+down;
						 //console.log(total);
             $("#mdb .subtitle cite").text(total);
             $("#mdb #serverBox").text(str[0]+"&nbsp&nbsp"+str[1]);
            },
          error:function(jqXHR, textStatus, errorThrown){
             switch(jqXHR.status){
               case 400 : alert("NOT Found!!!");break;
               case 500 : alert("Server Error!!!");break;
              } 
            }
          }); 

};

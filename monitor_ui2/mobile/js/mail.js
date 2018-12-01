
/**************************URL参数匹配**************************************************/
function url_params(param){ //
   var reg = new RegExp("(^|&)" + param + "=([^&]*)(&|$)","i");
   var r = window.location.search.substr(1).match(reg);
   if (r!=null) return decodeURIComponent(r[2]);return null;
 }
var $=function(id){
  return document.getElementById(id);
};

/*var XMLHttpReq;
  //创建XMLHttpRequest对象       
function createXMLHttpRequest() {
   if(window.XMLHttpRequest) { //Mozilla 浏览器
    XMLHttpReq = new XMLHttpRequest();
   }else if (window.ActiveXObject) { // IE浏览器
     try {
       XMLHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
     }catch (e) {
     try {
       XMLHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
      }catch (e) {}
   }
  }
 }
 //发送请求函数
 function sendRequest(url) {
  createXMLHttpRequest();
  XMLHttpReq.open("GET", url);
  XMLHttpReq.onreadystatechange = processResponse;//指定响应函数
  XMLHttpReq.send(null);  // 发送请求
 }
 // 处理返回信息函数
 function processResponse() {
  if (XMLHttpReq.readyState == 4) { // 判断对象状态
   alert(XMLHttpReq.status); 
   if (XMLHttpReq.status == 200) { // 信息已经成功返回，开始处理信息
         var res=XMLHttpReq.responseText;
         //alert(res);
         var data = eval('('+res+')'); //json  
         //console.log(info2);
         DateHandle(data);//处理json数据
    }else{ //页面不正常
         window.alert(XMLHttpReq.status+"error");
      }
     }
    }*/

var XMLHttpReq;
  //创建XMLHttpRequest对象       
function createXMLHttpRequest() {
  if(window.XMLHttpRequest) { //Mozilla 浏览器
   XMLHttpReq = new XMLHttpRequest();
  }
  else if (window.ActiveXObject) { // IE浏览器
   try {
    XMLHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
   } catch (e) {
    try {
     XMLHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {}
   }
  }
 }
 //发送请求函数
 function sendRequest(url,para) {
  createXMLHttpRequest();
  XMLHttpReq.open("POST", url,true);
  XMLHttpReq.onreadystatechange = processResponse;//指定响应函数
              XMLHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");    
  //alert(para);
  XMLHttpReq.send(para);  // 发送请求
  
 }
 // 处理返回信息函数
 function processResponse() {
     if (XMLHttpReq.readyState == 4) { // 判断对象状态
         if (XMLHttpReq.status == 200) { // 信息已经成功返回，开始处理信息
             var res=XMLHttpReq.responseText;
              // window.alert(res);                
             var data = eval('('+res+')'); //json
              //console.log(info2);
             DateHandle(data);//处理json数据
  
          } else { //页面不正常
                window.alert(XMLHttpReq.status+"error");
            }
        }
    }


function createLiNode(eventData){
  var detail=$("detail"),imgsrc;
  var li=document.createElement("li");
  //li.id=detail[1];
  detail.appendChild(li);
  //alert(detail);
  var imageDiv=document.createElement("div");
  imageDiv.className="errorImage";
  li.appendChild(imageDiv);
  
  switch(eventData[3]){
    case "0" : imgsrc="images/down.png";break;
    case "1" : imgsrc="images/up.png";break;
    case "2" : imgsrc="images/caution.png";break;
    case "3" : imgsrc="images/warning.png";break;
   }
  var imgStatus=document.createElement("img");
  imgStatus.src=imgsrc;
  imgStatus.style.width="64px";
  imgStatus.height.height="64px";
  imageDiv.appendChild(imgStatus);  

  var contentDiv=document.createElement("div");
  contentDiv.className="errorContent";
  li.appendChild(contentDiv);

  var eventDate=document.createElement("p");
  eventDate.className="eventDate";
  contentDiv.appendChild(eventDate);

  var eventName=document.createElement("p");
  eventName.className="eventName";
  contentDiv.appendChild(eventName);
  
  var eventInfo=document.createElement("p");
  eventInfo.className="eventInfo";
  contentDiv.appendChild(eventInfo);
   
  //console.log(detail[5]); 
  eventDate.innerText=eventData[5];
  eventName.innerText=eventData[1];
  if(eventData[6]=="" || eventData[6]==null){
    eventInfo.innerText="无";
  }else{
    eventInfo.innerHTML=eventData[6]/*.replace('<br />','')*/; 
  }
}

function DateHandle(json){
	//document.body.innerHTML = JSON.stringify(json);
	//console.log(json);return;
  for(var i in json){
    // console.log(i);
    if(i=="records"){
      for(var j in json[i]){
       createLiNode(json[i][j]); 
    }
    }
  }

}

window.onload=function(){
  //alert("ok");
  var serverId=url_params("server");
  var snapshotid=url_params("snapshotid");
  //alert(snapshotid);
  var version=url_params("version");

	var serverName=document.createTextNode(serverId);
  $("serverId").appendChild(serverName); 
  
  sendRequest("http://27.115.15.8/mmsapi"+version+"/get/event/@all/"+serverId,"line_per_page=100&current_page=1&snapshotid="+snapshotid);
};

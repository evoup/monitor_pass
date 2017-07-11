$(document).ready(function(){
  console.log('wizard step2: monitorCls:'+url_params("monitorCls"));
  var monType = new Array("Generic", "MySQL", "Serving", "Daemon", "Report", "MDN",
    "HDFS", "FreeBSD Jail", "MDB", "Global Load Balance", "Security", "Core Monitor"); 
  $("#wizard2Span").text(monType[url_params("monitorCls")]);
  $("#prevStep").bind("click",function(){
    location.href='monitorWizard.html?monitorCls='+url_params("monitorCls")+'&version='+url_params("version");
  })
  $("#nextStep").bind("click", function(){
    if ($("#hostId").val()=='') {
      console.log('empty');
      alert('请输入服务器名称');
    } else {//请求全部服务器列表
      reqAllserver();
    }
  }) 
  $('#hostId').val(url_params('host'));
});


function reqAllserver() {
  var version=url_params("version"),status=url_params("status");//down /online
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/server/@all","#serverBox_all","loading_small.gif",get_server_allNum_Callback) 

} 
function get_server_allNum_Callback(json, textStatus, jqXHR){ // 如存在该服务器，进入下一步，否则提示
  console.log(json)
    if (jqXHR.status==200) {
      console.log('input host:'+$('#hostId').val())
        for(var i in json){
          if (i==$('#hostId').val()) {
            console.log('exist');
            console.log('monitorCls:'+url_params('monitorCls'));
            //服务器存在，根据选择监控大类，进入第三页
            window.location.href="monitorWizardStep3_"+url_params('monitorCls')+".html?version="+url_params("version")+"&monitorCls="+url_params('monitorCls')+"&host="+$('#hostId').val();
            return true;
          }
        }
    }
    alert('您输入的服务器不存在监控列表中，请先从客户端进行上传!');
    console.log('no exist');
}

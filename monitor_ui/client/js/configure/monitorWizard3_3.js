function reqDaemonDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@daemon","#daemon_form","loading_small.gif",get_daemon_detail_Callback) 
}

function get_daemon_detail_Callback(json, textStatus, jqXHR) { //获取该主机的明细设置(DAEMON部分)
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value) {
        switch (key) {
        case('daemon_web_server'):
          if (value==1) {
            $('input[name=daemon_web_server_monitored]').attr('checked', true);
          }
          break;
        case('daemon_backend_daemon'):
          if (value==1) {
            $('input[name=daemon_backend_daemon_monitored]').attr('checked', true);
          }
          break;
        case('daemon_login'):
          if (value==1) {
            $('input[name=daemon_login_monitored]').attr('checked', true);
          }
          break;
        case('daemon_advt_deliver'):
          if (value==1) {
            $('input[name=daemon_advt_deliver_monitored]').attr('checked', true);
          }
          break;
        case('daemon_error_log'):
          if (value==1) {
            $('input[name=daemon_error_log_monitored]').attr('checked', true);
          }
          break;
        default:
          break;
        }
      });
      return true;
    }
  alert('获取服务器数据失败!');
  console.log('no exist');
}

function createHidDataIframe(){ //创建隐藏数据iframe 
  var hidFrame=$("#hiddenDataFrame",window.top.document);
  if (hidFrame.length<=0) {
    $("body",window.top.document).append('<iframe id="hiddenDataFrame" style="display:none">dummy iframe</iframe>'); 
    console.log('hidden iframe created');
  } else {
    console.log('hidden iframe existed, not created');
  }
  hidFrame.text("");
  return hidFrame;
}

function copyInputToJson() { //把用户输入的各个选项保存到json字符串中 
  var itmArr=new Array();
  var monitorItem=new Array(
    'daemon_web_server_monitored',
    'daemon_backend_daemon_monitored',
    'daemon_login_monitored',
    'daemon_advt_deliver_monitored',
    'daemon_error_log_monitored'
    );
  console.log(monitorItem);
  for (itm in monitorItem) {
    console.log("itm:"+itm+" monitorItem:"+monitorItem[itm]);
    if ($('input[name='+monitorItem[itm]+']:checked').val()) {
      console.log("checked itm:"+itm+" monitorItem:"+monitorItem[itm]);
      itmArr[itm]=1;
    } else {
      itmArr[itm]=0;
    }
  }
  console.log(itmArr);
  var detailSetting={ //监控明细数据对象 k=>监控小项，v=>Array(是否监控，黄色警报，红色警报)
    'daemon':{
      'daemon_web_server':itmArr[0],
      'daemon_backend_daemon':itmArr[1],
      'daemon_login':itmArr[2],
      'daemon_advt_deliver':itmArr[3],
      'daemon_error_log':itmArr[4]
    }
  };
  console.log('detailSetting follow');
  console.log(detailSetting);
  var jsonStr=JSON.stringify(detailSetting); //inlcude json2.js first
  console.log(jsonStr);
  var hidFrame=createHidDataIframe();
  hidFrame.text(jsonStr); //数据存入iframe 
}

$(document).ready(function() {
  reqDaemonDetail();
  var addonUrlParams="?version="+url_params('version')+"&monitorCls="+url_params('monitorCls')+"&host="+url_params('host');
  $("#prevStep").bind("click", function(){
    location.href="monitorWizardStep2.html"+addonUrlParams;
  }); 
  $("#nextStep").bind("click", function(){
    createHidDataIframe();
    copyInputToJson();
    window.location.href="monitorWizardStep4.html"+addonUrlParams;
  }); 
});

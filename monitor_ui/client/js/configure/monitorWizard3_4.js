function reqReportDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@report","#report_form","loading_small.gif",get_report_detail_Callback) 
}

function get_report_detail_Callback(json, textStatus, jqXHR) { //获取该主机的明细设置(REPORT部分)
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value) {
        switch (key) {
        case('report_wait_process_log_number'):
          if (value.monitored==1) {
            $('input[name=report_wait_process_log_number_monitored]').attr('checked', true);
          }
          $('input[name=report_wait_process_log_number_caution]').val(value.caution);
          $('input[name=report_wait_process_log_number_warning]').val(value.warning);
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
    'report_wait_process_log_number_monitored'
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
    'report':{
     'report_wait_process_log_number':[itmArr[0],$('input[name=report_wait_process_log_number_caution]').val(),$('input[name=report_wait_process_log_number_warning]').val()]
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
  reqReportDetail();
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

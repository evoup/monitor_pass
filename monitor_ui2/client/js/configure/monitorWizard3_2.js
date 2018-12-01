function reqServingDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@serving","#serving_form","loading_small.gif",get_serving_detail_Callback) 
}

function get_serving_detail_Callback(json, textStatus, jqXHR) { //获取该主机的明细设置(SERVING部分)
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value) {
        switch (key) {
        case('serving_request_number'):
          if (value.monitored==1) {
            $('input[name=serving_request_number_monitored]').attr('checked', true);
          }
          $('input[name=serving_request_number_caution]').val(value.caution);
          $('input[name=serving_request_number_warning]').val(value.warning);
          break;
        case('serving_advt_publish'):
          if (value==1) {
            $('input[name=serving_advt_publish_monitored]').attr('checked', true);
          }
          break;
        case('serving_log_creation'):
          if (value==1) {
            $('input[name=serving_log_creation_monitored]').attr('checked', true);
          }
          break;
        case('serving_fillrate'):
          if (value.monitored==1) {
            $('input[name=serving_fillrate_monitored]').attr('checked', true);
            $('input[name=serving_fillrate_caution]').val(value.caution);
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
    'serving_request_number_monitored',
    'serving_advt_publish_monitored',
    'serving_log_creation_monitored',
    'serving_fillrate_monitored'
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
    'serving':{
      'serving_request_number':[itmArr[0],$('input[name=serving_request_number_caution]').val(),$('input[name=serving_request_number_warning]').val()],
      'serving_advt_publish':itmArr[1],
      'serving_log_creation':itmArr[2],
      'serving_fillrate':[itmArr[3],$('input[name=serving_fillrate_caution]').val()]
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
  reqServingDetail();
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

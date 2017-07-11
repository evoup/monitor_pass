function addDnsARecordBlockRow(){ // 添加DNS的A记录的监控项目 
  var lineIndex=$("ul").filter('.dns_resovation_data').size();
  var lineCount=lineIndex+1;
  console.log('total_row:'+lineCount);
  appendContent="<h5><input type=checkbox name='dns_resovation_monitored_"+lineIndex+"' />监控DNS解析（A记录）</h5><ul name='dns_resovation_"+lineIndex+"' style='width:95%;' class='dns_resovation_data'><li><label class='tag'>全称域名:</label><input type='text' value='' name='dns_fqdn_"+lineIndex+"' /> <br/><cite>输入完整的域名信息</cite></li><li><label class='tag'>使用DNS服务器:</label><input type='text' value='' name='dns_ip_"+lineIndex+"' /><br/><cite>默认使用互联网DNS服务器,如使用自己的DNS服务器须输入IP</cite></li><li><label class='tag'>监控频率:</label><input type='text' value='' name='dns_duration_"+lineIndex+"' />秒<br/><cite>输入客户端查询DNS解析的间隔</cite></li><li><label class='tag'>重试:</label><input type='text' value='' name='dns_retry_"+lineIndex+"' />次<br/><cite>解析失败重试后确认报警的次数</cite></li><li><input type='button' value='+' style='width:20px;height:20px' onclick='addDnsARecordBlockRow()' /><label class='tag' style='width:60px;padding-top:0px;margin-top:2px;'>继续添加</label></li></ul><hr/>";
  $("#addon").append(appendContent);
}

function expandDnsARecordBlockRow(num){ //根据监控的域名数量扩充表格
  for (var i=1;i<num;i++) {
    addDnsARecordBlockRow();
  }
}

function reqMdnDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@mdn","#mdn_form","loading_small.gif",get_mdn_detail_Callback) 
}
  
function get_mdn_detail_Callback(json, textStatus, jqXHR){ //获取该主机的明细设置(MDN部分)
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value){
        switch(key){
        case("mdn_domain"):
          var countJson=0, idx=0;
          for (var domain in json[key]) {
            countJson++;
          }
          console.log('countDomain:'+countJson);
          expandDnsARecordBlockRow(countJson);
          for (var dm in json[key]) {
            console.log(dm);
            console.log(json[key][dm][0]); //是否监控 
            console.log(json[key][dm][1]); //全称域名
            console.log(json[key][dm][2]); //监控频率
            console.log(json[key][dm][3]); //重试次数
            domainMonitored=json[key][dm][0];
            dnsServerIp=json[key][dm][1];
            monitorDuration=json[key][dm][2];
            monitorRetry=json[key][dm][3];
            if (domainMonitored==1) {
              $('input[name=dns_resovation_monitored_'+idx+']').attr('checked', true);
            }
              $('input[name=dns_fqdn_'+idx+']').val(dm);
              $('input[name=dns_ip_'+idx+']').val(dnsServerIp);
              $('input[name=dns_duration_'+idx+']').val(monitorDuration);
              $('input[name=dns_retry_'+idx+']').val(monitorRetry);
            idx++;
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
  var lineIndex=$("ul").filter('.dns_resovation_data').size(); //传递了多少domain 
  console.log("domains:"+lineIndex);
  var itmArr=new Array();
  var monitorItem=new Array();
  for (var i=0;i<lineIndex;i++) {
    monitorItem.push('dns_resovation_monitored_'+i);
    console.log('push dns_resovation_monitored'+i);
  }
  console.log('monitorItem follow:');
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
  console.log('itmArr follow:');
  console.log(itmArr);
  var detailSetting={ //监控明细数据对象 k=>监控小项，v=>Array(是否监控，黄色警报，红色警报)
    'mdn':{
    }
  };
  for (var i=0;i<lineIndex;i++) {
    detailSetting['mdn']['domain_monitored_'+i]=[itmArr[i],$('input[name=dns_fqdn_'+i+']').val(),$('input[name=dns_ip_'+i+']').val(),$('input[name=dns_duration_'+i+']').val(),$('input[name=dns_retry_'+i+']').val()];
  }
  console.log('detailSetting follow:');
  console.log(detailSetting);
  var jsonStr=JSON.stringify(detailSetting); //inlcude json2.js first
  console.log(jsonStr);
  var hidFrame=createHidDataIframe();
  hidFrame.text(jsonStr); //数据存入iframe 
}

$(document).ready(function(){
  reqMdnDetail();
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

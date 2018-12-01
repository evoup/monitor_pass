function addServiceTableRow(){ // 添加系统服务监控项目 
  var lineIndex=$("#service_tab tr").filter('.service_data').size();
  var lineCount=lineIndex+1;
  console.log('total_row:'+lineCount);
  $("#service_tab").append("<tr class='service_data'><td><input type='checkbox' name='service_monitored_"+lineIndex+"' /></td><td><input type='text' name='service_name_"+lineIndex+"' /></td><td><input type='text' name='service_port_"+lineIndex+"' /></td><td><input type='button' value='+' style='width:20px;height:20px' onclick='addServiceTableRow()' /></td></tr>");
}

function expandServiceTableRow(num){ //根据服务数量扩充服务表格 
  for (var i=1;i<num;i++) {
    addServiceTableRow();
  }
}

function reqGenericDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@generic","#generic_form","loading_small.gif",get_generic_detail_Callback) 
} 

function get_generic_detail_Callback(json, textStatus, jqXHR){ //获取该主机的明细设置(GENERIC部分 )
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value){
        switch(key){
        case("disk_capacity"):
          if (value.monitored==1) {
            $('input[name=disk_capacity_monitored]').attr('checked', true);
          }
          $('input[name=disk_capacity_caution]').val(value.caution);
          $('input[name=disk_capacity_warning]').val(value.warning);
          break;
        case("disk_inode_capacity"):
          if (value.monitored==1) {
            $('input[name=disk_inode_capacity_monitored]').attr('checked', true);
          }
          $('input[name=disk_inode_capacity_caution]').val(value.caution);
          $('input[name=disk_inode_capacity_warning]').val(value.warning);
          break;
        case("load_average"):
          if (value.monitored==1) {
            $('input[name=load_average_monitored]').attr('checked', true);
          }
          $('input[name=load_average_caution]').val(value.caution);
          $('input[name=load_average_warning]').val(value.warning);
          break;
        case("memory_usage"):
          if (value.monitored==1) {
            $('input[name=memory_usage_monitored]').attr('checked', true);
          }
          $('input[name=memory_usage_caution]').val(value.caution);
          $('input[name=memory_usage_warning]').val(value.warning);
          break;
        case("total_processes"):
          if (value.monitored==1) {
            $('input[name=total_processes_monitored]').attr('checked', true);
          }
          $('input[name=total_processes_caution]').val(value.caution);
          $('input[name=total_processes_warning]').val(value.warning);
          break;
        case("cpu_usage"):
          if (value.monitored==1) {
            $('input[name=cpu_usage_monitored]').attr('checked', true);
          }
          $('input[name=cpu_usage_caution]').val(value.caution);
          $('input[name=cpu_usage_warning]').val(value.warning);
          break;
        case("tcp_connection"):
          if (value.monitored==1) {
            $('input[name=tcp_connection_monitored]').attr('checked', true);
          }
          $('input[name=tcp_connection_caution]').val(value.caution);
          $('input[name=tcp_connection_warning]').val(value.warning);
          break;
        case("network_flow"):
          if (value.monitored==1) {
            $('input[name=network_flow_monitored]').attr('checked', true);
          }
          $('input[name=network_flow_caution]').val(value.caution);
          $('input[name=network_flow_warning]').val(value.warning);
          break;
        case("services"):
          //expand service table
          //console.log(json[key].length);
          var countJson=0, idx=0;
          for (var service in json[key]) {
            countJson++;
          }
          expandServiceTableRow(countJson);
          for (var service in json[key]) {
            console.log(key);
            console.log(json[key][service]);
            service_monitored=json[key][service][0];
            service_port=json[key][service][1];
            if (service_monitored==1) {
              $('input[name=service_monitored_'+idx+']').attr('checked', true);
            }
            $('input[name=service_name_'+idx+']').val(service);
            $('input[name=service_port_'+idx+']').val(service_port);
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
  var lineIndex=$("#service_tab tr").filter('.service_data').size(); //查询多少service行 
  console.log("service_line:"+lineIndex);
  var itmArr=new Array();
  var monitorItem=new Array(
    'disk_capacity_monitored',
    'disk_inode_capacity_monitored',
    'load_average_monitored',
    'memory_usage_monitored',
    'total_processes_monitored',
    'cpu_usage_monitored',
    'tcp_connection_monitored',
    'network_flow_monitored'
    );
  for (var i=0;i<lineIndex;i++) {
    monitorItem.push('service_monitored_'+i);
    console.log('push service_monitored'+i);
  }
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
    'generic':{
      'disk_capacity':[itmArr[0],$('input[name=disk_capacity_caution]').val(),$('input[name=disk_capacity_warning]').val()],
      'disk_inode_capacity':[itmArr[1],$('input[name=disk_inode_capacity_caution]').val(),$('input[name=disk_inode_capacity_warning]').val()],
      'load_average':[itmArr[2],$('input[name=load_average_caution]').val(),$('input[name=load_average_warning]').val()],
      'memory_usage':[itmArr[3],$('input[name=memory_usage_caution]').val(),$('input[name=memory_usage_warning]').val()],
      'total_processes':[itmArr[4],$('input[name=total_processes_caution]').val(),$('input[name=total_processes_warning]').val()],
      'cpu_usage':[itmArr[5],$('input[name=cpu_usage_caution]').val(),$('input[name=cpu_usage_warning]').val()],
      'tcp_connection':[itmArr[6],$('input[name=tcp_connection_caution]').val(),$('input[name=tcp_connection_warning]').val()],
      'network_flow':[itmArr[7],$('input[name=network_flow_caution]').val(),$('input[name=network_flow_warning]').val()]
    }
  };
  for (var i=0;i<lineIndex;i++) {
    detailSetting['generic']['service_monitored_'+i]=[itmArr[8+i],$('input[name=service_name_'+i+']').val(),$('input[name=service_port_'+i+']').val()];
  }
  console.log(detailSetting);
  var jsonStr=JSON.stringify(detailSetting); //inlcude json2.js first
  console.log(jsonStr);
  var hidFrame=createHidDataIframe();
  hidFrame.text(jsonStr); //数据存入iframe 
}

$(document).ready(function(){
  reqGenericDetail();
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

function addMysqlTableRow(){ // 添加MySQL关键表监控项目 
  var lineIndex=$("#table_tab tr").filter('.table_data').size();
  var lineCount=lineIndex+1;
  console.log('total_row:'+lineCount);
  $("#table_tab").append("<tr class='table_data'><td><input type='checkbox' name='table_monitored_"+lineIndex+"' /></td><td><input type='text' name='table_name_"+lineIndex+"' /></td><td><input type='text' name='table_size_caution_"+lineIndex+"' /></td><td><input type='text' name='table_size_warning_"+lineIndex+"' /></td><td><input type='button' value='+' style='width:20px;height:20px' onclick='addMysqlTableRow()' /></td></tr>");
}

function expandMysqlTableRow(num){ //根据服务数量扩充服务表格
  for (var i=1;i<num;i++) {
    addMysqlTableRow();
  }
}

function reqMysqlDetail() {
  var version=url_params("version");
  var domain=domainURI(document.location.href); 
  get_data_ajax("http://"+domain+"/mmsapi"+version+"/get/detailSetting/@mysql","#mysql_form","loading_small.gif",get_mysql_detail_Callback) 
} 

function get_mysql_detail_Callback(json, textStatus, jqXHR){ //获取该主机的明细设置(MYSQL部分)
  console.log(json)
    if (jqXHR.status==200) {
      $.each(json,function(key,value){
        switch(key){
        case("mysql_connections"):
          if (value.monitored==1) {
            $('input[name=mysql_connections_monitored]').attr('checked', true);
          }
          $('input[name=mysql_connections_caution]').val(value.caution);
          $('input[name=mysql_connections_warning]').val(value.warning);
          break;
        case("mysql_created_threads"):
          if (value.monitored==1) {
            $('input[name=mysql_created_threads_monitored]').attr('checked', true);
          }
          $('input[name=mysql_created_threads_caution]').val(value.caution);
          $('input[name=mysql_created_threads_warning]').val(value.warning);
          break;
        case("mysql_master_slave"):
          if (value==1) {
            $('input[name=mysql_master_slave_monitored]').attr('checked', true);
          }
          break;
        case('mysql_crucial_table'):
          var countJson=0, idx=0;
          for (var table in json[key]) {
            countJson++;
          }
          console.log('countTable:'+countJson);
          expandMysqlTableRow(countJson);
          for (var table in json[key]) {
            console.log(table);
            console.log(json[key][table][0]); //是否监控 
            console.log(json[key][table][1]); //黄色警报 
            console.log(json[key][table][2]); //红色警报 
            tableMonitored=json[key][table][0];
            tableSizeCaution=json[key][table][1];
            tableSizeWarning=json[key][table][2];
            if (tableMonitored==1) {
              $('input[name=table_monitored_'+idx+']').attr('checked', true);
            }
            $('input[name=table_name_'+idx+']').val(table);
            $('input[name=table_size_caution_'+idx+']').val(tableSizeCaution);
            $('input[name=table_size_warning_'+idx+']').val(tableSizeWarning);
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
  var lineIndex=$("#table_tab tr").filter('.table_data').size(); //查询多少table行 
  console.log("table_line:"+lineIndex);
  var itmArr=new Array();
  var monitorItem=new Array(
    'mysql_connections_monitored',
    'mysql_created_threads_monitored',
    'mysql_master_slave_monitored'
    );
  for (var i=0;i<lineIndex;i++) {
    monitorItem.push('table_monitored_'+i);
    console.log('push table_monitored'+i);
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
    'mysql':{
      'mysql_connections':[itmArr[0],$('input[name=mysql_connections_caution]').val(),$('input[name=mysql_connections_warning]').val()],
      'mysql_created_threads':[itmArr[1],$('input[name=mysql_created_threads_caution]').val(),$('input[name=mysql_created_threads_warning]').val()],
      'mysql_master_slave':[itmArr[2]]
    }
  };
  for (var i=0;i<lineIndex;i++) {
    detailSetting['mysql']['table_monitored_'+i]=[itmArr[3+i],$('input[name=table_name_'+i+']').val(),$('input[name=table_size_caution_'+i+']').val(),$('input[name=table_size_warning_'+i+']').val()];
  }
  console.log(detailSetting);
  var jsonStr=JSON.stringify(detailSetting); //inlcude json2.js first
  console.log(jsonStr);
  var hidFrame=createHidDataIframe();
  hidFrame.text(jsonStr); //数据存入iframe 
}

$(document).ready(function(){
  reqMysqlDetail();
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

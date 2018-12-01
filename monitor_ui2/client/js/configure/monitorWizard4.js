$(document).ready(function(){
  var domain=domainURI(document.location.href);
  var version=url_params('version');
  var monType = new Array("generic", "mysql", "serving", "daemon", "report", "mdn",
    "hdfs", "FreeBSD Jail", "MDB", "Global Load Balance", "Security", "Core Monitor"); 
  $("#wizard2Span").text(monType[url_params("monitorCls")]);
  $('#prevStep').bind('click', function(){
    location.href="monitorWizardStep3_"+url_params('monitorCls')+".html?version="+version+'&monitorCls='+url_params('monitorCls')+'&host='+url_params('host');
  });
  var hidPostValue=$("#hiddenDataFrame",window.top.document).text();
  console.log('in step 4');
  var detailSetting=JSON.parse(hidPostValue);
  console.log('detailSetting follow');
  console.log(detailSetting);
  console.log('type is:'+monType[url_params('monitorCls')]);
  switch (monType[url_params('monitorCls')]) {
  case('generic'):
    //{{{ generic
    var genericSetting=detailSetting['generic'];
    for (var key in genericSetting) { //得到service的key 
      var patrn=/^.*service_monitored_.*$/;
      if (patrn.exec(key)) {
        console.log('found key:'+key);
        var serviceStr=join('|',[genericSetting[key][0],genericSetting[key][1],genericSetting[key][2]]); //格式为:是否监控|服务名|服务端口 
        genericSetting['services']=join('#',[genericSetting['services'],serviceStr]);
      }
    }
    var ds={
      'type':'generic',
      'disk_capacity':join('|',[genericSetting['disk_capacity'][0],genericSetting['disk_capacity'][1],genericSetting['disk_capacity'][2]]),
      'disk_inode_capacity':join('|',[genericSetting['disk_inode_capacity'][0],genericSetting['disk_inode_capacity'][1],genericSetting['disk_inode_capacity'][2]]),
      'load_average':join('|',[genericSetting['load_average'][0],genericSetting['load_average'][1],genericSetting['load_average'][2]]),
      'memory_usage':join('|',[genericSetting['memory_usage'][0],genericSetting['memory_usage'][1],genericSetting['memory_usage'][2]]),
      'total_processes':join('|',[genericSetting['total_processes'][0],genericSetting['total_processes'][1],genericSetting['total_processes'][2]]),
      'cpu_usage':join('|',[genericSetting['cpu_usage'][0],genericSetting['cpu_usage'][1],genericSetting['cpu_usage'][2]]),
      'tcp_connection':join('|',[genericSetting['tcp_connection'][0],genericSetting['tcp_connection'][1],genericSetting['tcp_connection'][2]]),
      'network_flow':join('|',[genericSetting['network_flow'][0],genericSetting['network_flow'][1],genericSetting['network_flow'][2]]),
      'services':genericSetting['services']
    };
    //}}}
    break;
  case('mysql'):
    //{{{ mysql
    var mysqlSetting=detailSetting['mysql'];
    for (var key in mysqlSetting) { //得到table的key 
      var patrn=/^.*table_monitored_.*$/;
      if (patrn.exec(key)) {
        console.log('found key:'+key);
        var tableStr=join('|',[mysqlSetting[key][0],mysqlSetting[key][1],mysqlSetting[key][2],mysqlSetting[key][3]]); //格式为:是否监控|表名|黄色警报大小|红色警报大小
        mysqlSetting['crucial_table']=join('#',[mysqlSetting['crucial_table'],tableStr]);
      }
    }
    console.log(mysqlSetting);
    var ds={
      'type':'mysql',
      'mysql_connections':join('|',[mysqlSetting['mysql_connections'][0],mysqlSetting['mysql_connections'][1],mysqlSetting['mysql_connections'][2]]),
      'mysql_created_threads':join('|',[mysqlSetting['mysql_created_threads'][0],mysqlSetting['mysql_created_threads'][1],mysqlSetting['mysql_created_threads'][2]]),
      'mysql_master_slave':mysqlSetting['mysql_master_slave'][0],
      'mysql_crucial_table':mysqlSetting['crucial_table']
    };
    //}}}
    break;
  case('serving'):
    //{{{ serving
    var servingSetting=detailSetting['serving'];
    var ds={
      'type':'serving',
      'serving_request_number':join('|',[servingSetting['serving_request_number'][0],servingSetting['serving_request_number'][1],servingSetting['serving_request_number'][2]]),
      'serving_advt_publish':servingSetting['serving_advt_publish'],
      'serving_log_creation':servingSetting['serving_log_creation'],
      'serving_fillrate':join('|',[servingSetting['serving_fillrate'][0],servingSetting['serving_fillrate'][1]])
    };
    //}}}
    break;
    case('daemon'):
    //{{{ daemon
    var daemonSetting=detailSetting['daemon'];
    var ds={
      'type':'daemon',
      'daemon_web_server':daemonSetting['daemon_web_server'],
      'daemon_backend_daemon':daemonSetting['daemon_backend_daemon'],
      'daemon_login':daemonSetting['daemon_login'],
      'daemon_advt_deliver':daemonSetting['daemon_advt_deliver'],
      'daemon_error_log':daemonSetting['daemon_error_log']
      };
    //}}}
    break;
    case('report'):
    //{{{ report
    var reportSetting=detailSetting['report'];
    var ds={
      'type':'report',
      'report_wait_process_log_number':join('|',[reportSetting['report_wait_process_log_number'][0],reportSetting['report_wait_process_log_number'][1],reportSetting['report_wait_process_log_number'][2]])
      }
    //}}}
    break;
    case('mdn'):
    //{{{ mdn
    var mdnSetting=detailSetting['mdn'];
    console.log('mdnSetting follow');
    console.log(mdnSetting);
    for (var key in mdnSetting) { //得到mdn设置的key 
      var patrn=/^.*domain_monitored_.*$/;
      if (patrn.exec(key)) {
        console.log('found key:'+key);
        var domainStr=join('|',[mdnSetting[key][0],mdnSetting[key][1],mdnSetting[key][2],mdnSetting[key][3]]); //格式为:是否监控|域名|监控频率|重试次数
        mdnSetting['domains']=join('#',[mdnSetting['domains'],domainStr]);
      }
    }
    var ds={
      'type':'mdn',
      'dns':mdnSetting['domains']
      };
    //}}}
    break;
  }
  console.log('ds follow:');
  console.log(ds);
  $("#apply").bind('click', function(){
    $.ajax({
      type: "post",
      url : "http://"+domain+"/mmsapi"+version+"/update/detailSetting/@self/"+url_params('host'),
      async: false,
      data: ds,
      success: function(json, textStatus, jqXHR){//如果调用php成功
        if(jqXHR.status==200 || jqXHR.status==205){
          Tips("complete",monType[url_params("monitorCls")]+"监控向导设置成功!");
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
               switch(jqXHR.status){
               case 400 : Tips("alert" ,"数据未找到");break;
               case 500 : Tips("alert" ,"服务器出错");break;
               }
             }
    });
  });
});

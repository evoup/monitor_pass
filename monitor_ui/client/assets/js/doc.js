/*
 * 截取域名
 */ 
function domainURI(){
    if (location.port != 80) {
      return location.hostname + ":" + location.port;
    } else {
      return location.hostname;
    } 
}


/*
 * 匹配url参数
 */ 
function urlParams(param){
    var reg = new RegExp("(^|&)" + param + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) 
        return decodeURIComponent(r[2]);
    return null;
}

/**
 * 
 * 定义全局映射
 * 
 */
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

//全局作用域
var smartMad = window.smartMad = window.smartMad || {};

//域名
smartMad.domain = domainURI();

//版本
smartMad.version = urlParams('version');

//API url前缀
smartMad._url = "http://" + smartMad.domain + "/mmsapi" + smartMad.version;

//API
smartMad.api = {
	
    //用户管理
    getUserAll: '/get/user/@all',
    getUser: '/get/user/@self/',
    delUser: '/delete/user/@self/',
    createUser: '/create/user/@self/',
    updateUser: '/update/user/@self/',
    
    //用户组管理
    getUserGroupAll: '/get/userGroup/@all',
    getUserGroup: '/get/userGroup/@self/',
    delUserGroup: '/delete/userGroup/@self/',
    createUserGroup: '/create/userGroup/@self/',
    updateUserGroup: '/update/userGroup/@self/',
    
    getUserAllmember: '/get/user/@allmember',
    
    //监控设置
    getGenericSet: '/get/generic_setting/@self',
    updateGenericSet: '/update/generic_setting/@self',
	getMailSet:'/get/mailSetting/@self',
	updateMailSet:'/update/mailSetting/@self',
	getAlarmSet:'/get/alarmSetting/@self',
	updateAlarmSet:'/update/alarmSetting/@self',
	getEventSet:'/get/event_setting/@self',
	updateEventSet:'/update/event_setting/@self',
	getScanSet:'/get/scan_setting/@self',
	updateScanSet:'/update/scan_setting/@self',			

	getEventKeepaliveSet:'/get/event_setting/@keepalive',
		
	//engine
	getStaMonengine:'/get/status/@monengine',
	getEngineAll : '/get/monengine/@all',
	
	//文档和下载
	getDocs: '/get/docs/@self',
	getDownloads: '/get/downloads/@self'
};


//url path
smartMad.urlPath = 'http://' + domainURI() + (domainURI() == '27.115.15.8' ? '/view/monitorui' : (domainURI() == '211.136.107.44' ? '/monitorui' : '')) + '/client/';



//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

smartMad.monitorOptionMap = {
    generic: ['Generic ', '监控常规的服务器，包括服务器常规性能指标，mysql,memcache mdb端口等', 0],
    mysql: ['MySQL ', '监控Mysql数据库服务器，包括线程、流量和表库等', 1],
    serving: ['Serving ', '监控Serving（包括delivering）投放和其他应用服务器节点状态信息', 2],
    daemon: ['Daemon ', '监控守护进程应用服务器和管理界面的信息', 3],
    report: ['Report ', '监控报表服务器，处理日志的性能', 4],
    mdn: ['MDN ', '监控MDN(移动发布网络)和traffic server的节点状态信息', 5],
    hadoop: ['HADOOP ', '监控Hadoop平台各节点状态信息', 6],
    jail: ['FreeBSD Jail ', '监控FreeBSD Jail的运行状态信息', 7],
    mdb: ['MDB', '监控MadHouse DB的运行状态信息', 8],
    gslb: ['Global Load ', '监控全局负载均衡架构的节点状态信息', 9],
    security: ['Security ', '监控全局网络应用和系统的安全性', 10],
    monitor: ['Mad Monitor ', '监控本地监控服务器的性能', 11]
};

smartMad.genericOptionMap = {
    ping: ['Ping ', '监控Ping的丢包率','黄色警报使用率','红色警报使用率','%', 0],
    disk_capacity: ['Disk Capacity ', '监控磁盘使用率','黄色警报使用率','红色警报使用率','%', 1],
    disk_inode_capacity: ['Disk Inode Capacity ', '监控磁盘Inode使用率','黄色警报使用率','红色警报使用率','%', 2],
    load_average: ['Load Average ', '监控平均负载（1分钟内的）','黄色警报数','红色警报数','', 3],
    memory_usage: ['Memory Usage ', '监控内存占用率','黄色警报占用率','红色警报占用率','%', 4],
    total_processes: ['Total Processes ', '监控运行进程数','黄色警报数','红色警报数','', 5],
    cpu_usage: ['CPU Usage ', '监控CPU占用率','黄色警报占用率','红色警报占用率','%', 6],
    tcp_connection: ['TCP Connections ', '监控TCP连接数','黄色警报数','红色警报数','', 7],
    network_flow: ['Network Flow', '监控网络接口流量','黄色警报字节数','红色警报字节数','bytes', 8],
	services:[],
	processes:[]
};

smartMad.serverStatusMap = [['宕机', 'icon32-danger flicker'], ['在线', 'icon32-success'], ['注意', 'icon32-warning'], ['严重', 'icon32-danger'], ['注意严重', 'icon32-danger'], ['未监控', 'icon32-info'], ['Auto Scaling 未服务', 'icon32-unscaling']];
	


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * common init
 */
$(function($){
	
	//设置iframe主内容区域高
    $(window).resize(function(){
        var m = $('#mainbody');
        var pth = parseInt(m.css('paddingTop'));
        var pbh = parseInt(m.css('paddingBottom'));
        var h = $(window).height() - pth - pbh - 62 - (self === top ? 36 + 32 : 0) + 'px';
        
        m.css({
            'height': h
        });
    }).resize();
	
    
    //根据独立页面或者iframe做不同的处理
    if (self !== top) {
    
        //使iframe可以在独立窗口打开
        $('h1:first-child').append('<a href="' + document.location + '" target="_blank" style="margin-left:5px;" title="在独立窗口打开" ><i class="icon-share-alt icon-white"></i> </a>');
        
    }
    else {
		
		$('body').addClass('own');
    	
		$('h1:first-child').addClass('_ac');
		
        //顶部logo条
        $('<div id="logobar">' +
        '<span id="logo">Madhouse亿动广告传媒|monitor core</span>' +
        '&nbsp;&nbsp;&nbsp;'+
		'<sup id="version">_beta2</sup>' +
        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;' +
        '<a hrer="javascript:void()" id="nav_switch">navigator ▼</a>' +
        '<span id="account"><i class="icon-user icon-white"></i>&nbsp;&nbsp;<strong></strong>'+
		'&nbsp;&nbsp;|&nbsp;&nbsp; <a id="logout" href="javascript:void(0)">退出</a></span>' +
        '</div>').prependTo('body');
        
        //底部状态条
        $('<div id="stabar">' +
        '<span id="updateTime">上次更新：<strong></strong></span>' +
        'Copyright © 2006-2013 &nbsp;&nbsp; Madhouse Inc. All Rights Reserved.' +
        '</div>').appendTo('body');
		
         //导航条
        $('<div id="navbar" tabindex="0" accessKey="q"></div>').appendTo('body')
		.getHtml({
            url: smartMad.urlPath + 'include/nav.html'
        })
		.blur(function(){
			$(this).delay(300).slideUp(200);
		})
		.focus(function(){
			$(this).slideDown(300);
		});
        
		//导航条显示隐藏切换      
        $('#nav_switch').mouseover(function(){
            $('#navbar').slideDown(300).focus();
        })
		.click(function(){
			//$('#navbar').slideToggle(300).focus();
		});
        
		//导航链接处理
        $('#navbar').delegate('a', 'click', function(){
            location.href = smartMad.urlPath + $(this).attr('href') + '?version=' + smartMad.version;
            return false;
        });
        
        
        /////////////////////////////////////////////////////////////////////////////////
        //更新时间
        var updateTimeConf = {
            url: smartMad._url + "/get/time/@scan",
			noLoading:1,
            interval: 20000
        };
        var updateTimeCall = function(e, json){
            var $th = $(this), data, html;
            
            //$th.html(JSON.stringify(json));return;
            
            $th.text(json['last_update']).addClass(json['status'] == 0 ? 'flicker' : '');
        };
        
        $("#updateTime strong").bind('ajax.success', updateTimeCall).getData(updateTimeConf);
        
        
        ////////////////////////////////////////////////////////////////////////////////
        //登录信息
        var loginConf = {
            type: 'get',
            url: smartMad._url + '/get/status/@logininfo',
			noLoading:1,
            interval: 300000,
            success: function(json, textStatus, jqXHR){
				//$(this).html(JSON.stringify(json));return;
				if(json == null){
					$(this).html('null');
					return;
				}
                if (json[1] == null) {
                    location.href = smartMad.urlPath + 'login.html?version=' + smartMad.version;
                }
                $(this).text(json[0] ? json[0] + " ：" + json[1] : json[1]);
            },
            error: function(jqXHR, textStatus, errorThrown){
                if (jqXHR.status == 401) {
                    location.href = smartMad.urlPath + 'login.html?version=' + smartMad.version;
                }
            }
        };
        
        $('#account strong').getData(loginConf);
		
        //////////////////////////////////////////////////////////////////////////////////
        //定时检测登录状态
        $('<p></p>').getData({
            url: smartMad._url + '/get/login/@self',
            interval: 300000,
            success: function(){
                //$(this).html('login ' + new Date);
				window.console && console.log('login ' + new Date);
            }
        });
		
        
        ////////////////////////////////////////////////////////////////////////////////	
        //退出登录
        var logout_conf = {
            type: 'get',
            url: smartMad._url + '/delete/login/@self',
            async: true,
            success: function(json, textStatus, jqXHR){
                alert('已经退出.');
                location.href = smartMad.urlPath + 'login.html?version=' + smartMad.version;
            },
            error: function(jqXHR, textStatus, errorThrown){
                if (jqXHR.status == 401) {
                    location.href = smartMad.urlPath + 'login.html?version=' + smartMad.version;
                }
            }
        };
        
        $('#logout').click(function(){
            $(this).postData(logout_conf);
        });
        
    }
	
	
	
	
});


//jQuery ready函数简写形式
$(function(){
    var version = urlParams('version');
    var domain = domainURI();
    var $server_listen = $('#server_listen');
    var $event_listen = $('#event_listen');
    var $unhandled = $('#unhandled');
    var _url = "http://" + domain + ":8004/mmsapi" + version;
    
	var pullDownAction = Gmonitor.pullDownAction;
    
    var conf_1 = {
        url: _url + "/get/server/@all",
		isTips: 1
    };
    
    var conf_2 = {
        url: _url + "/get/status/@eventsummary"
    };
    
    var conf_3 = {
        url: _url + "/get/event/@unhandled"
    };
	//////////////////////////////////////////////////////////
	
	//自定义下拖页面事件处理函数
    pullDownAction.push(function(){
		conf_1.isLoading = 1;
        $server_listen.getData(conf_1);
        conf_2.isLoading = 1;
        $event_listen.getData(conf_2);
        conf_3.isLoading = 1;
        $unhandled.getData(conf_3);
    });
	
    
    /////////////////////////////////////////////////////////
	
    $server_listen.bind('ajax.success', function(e, json){
    
        var num = 0, count = 0, notMonitorNum = 0,noscal = 0, data, html;
        
        //$server_listen.html(JSON.stringify(json));
        ///////////////////////////////////////////////
        
        if (json == null) {
            $server_listen.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        for (var k in json) {
            $.each(json[k], function(k, v){
                if (k == 0) {
                    switch (v) {
                        case "0":
                            num++;
                            break;
                        case "1":
                            count++;
                            break;
                        case "2":
                            count++;
                            break;
                        case "3":
                            count++;
                            break;
                        case "4":
                            count++;
                            break;
                        case "5":
                            notMonitorNum++;
                            break;
                        case "6":
                            noscal++;
                            break;							
                    }
                }
            });
        }
        
        /////////////////////////////////////////////// 
        
        data = {
            list: [{
                className: num ? 'warning flicker' : '',
                href: 'server/serverList.html?version=' + urlParams("version") + '&status=down',
                name: '宕机',
                coun: num
            }, {
                className: '',
                href: 'server/serverList.html?version=' + urlParams("version") + '&status=up',
                name: '在线',
                coun: count
            }, {
                className: '',
                href: 'server/serverList.html?version=' + urlParams("version") + '&status=unmonitored',
                name: '未监控',
                coun: notMonitorNum
            }, {
                className: '',
                href: 'server/serverList.html?version=' + urlParams("version") + '&status=unscaling',
                name: 'Auto Scaling 未服务',
                coun: noscal
            }]
        };
        
        ///////////////////////////////////////////
        html = template('t_a', data);
        $server_listen.html(html);
        
    });
    
    
    $event_listen.bind('ajax.success', function(e, json){
        var data, arr = [], i, o, html, conf = {
            'warning': '严重事件',
            'caution': '注意事件',
            'ok': '正常'
        };
        
        //$event_listen.html(JSON.stringify(json));return;
        ///////////////////////////////////////////
        
        if (json == null) {
            $event_listen.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        
        for (var i in json) {
            o = {};
            o.name = conf[i];
            o.coun = json[i];
			o.className = (i == 'warning' || i == 'caution') && json[i] > 0 ? i == 'warning' ? 'warning flicker' : 'caution' : '';
            o.href = 'monitor/monitorEvent.html?version=' + urlParams("version") + '&eventStatus=' + i;
            arr.push(o);
        }
        
        ///////////////////////////////////////////
        data = {
            list: arr
        };
        html = template('t_b', data);
        $event_listen.html(html);
        
    });
    
    
    $unhandled.bind('ajax.success', function(e, json){
        var data, html;
        
        //$unhandled.html(JSON.stringify(json));return;
        ///////////////////////////////////////////
        
        if (json == null) {
            $unhandled.html('<div class="error">没有需要处理的事件</div>');
            return;
        }
        
        $.each(json, function(k, v){
            switch (v[3]) {
                case 2:
                    v.push('state_4');
                    v.push('icon_caution');
                    break;
                case 3:
                    v.push('state_4');
                    v.push('icon_warning');
                    break;
                default:
            }
            v.push('server/serverStatus.html?name=' + v[0] + '&version=' + urlParams("version"));
        });
        
        data = {
            list: json
        };
        html = template('t_c', data);
        $unhandled.html(html);
        
    });
    
    $unhandled.delegate('button', 'click', function(){
        var $th = $(this);
        if ($th.text() == '查看状态信息') {
            $th.next().show();
            $th.text('收起状态信息')
        }
        else {
            $th.next().hide();
            $th.text('查看状态信息')
        }
        
    });
    
    //请求服务器监听数据
    $server_listen.getData(conf_1, 180000);
    
    //请求事件监听数据
    $event_listen.getData(conf_2, 180000);
    
    //未处理事件监听数据
    $unhandled.getData(conf_3, 180000);
	
	//更新时间
    var updateTimeConf = {
        url: _url + "/get/time/@scan",
		isLoading: 1,
        interval: 20000
    };
    var updateTimeCall = function(e, json){
        var $th = $(this), data, html;
        
        //$th.html(JSON.stringify(json));return;
        
        $th.text(json['last_update']).addClass(json['status'] == 0 ? 'warning flicker' : '');
    };
    
    $("#updateTime span").css({
        'display': 'inline-block'
    }).bind('ajax.success', updateTimeCall).getData(updateTimeConf);
                     
	
    
    
});

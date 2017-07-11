//jQuery ready函数简写形式
$(function(){
    var version = urlParams('version');
    var serverId = urlParams('name');
    var domain = domainURI();
    var $server_status = $('#server_status');
	var $server_detail = $('#server_detail');
    var _url = "http://" + domain + "/mmsapi" + version;
    
    var conf_1 = {
        url: _url + "/get/server/@self/" + serverId
    };
    var conf_2 = {
        url: _url + "/get/server/@self_detail/" + serverId
    };
    
    
    $server_status.bind('ajax.success', function(e, json){
    
        var $th = $(this), data, html;
        
        //$th.html(JSON.stringify(json));return;
        ///////////////////////////////////////////////
        
        if (json == null) {
            $th.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        
        ///////////////////////////////////////////
        data = {
            list: json
        };
        
        ///////////////////////////////////////////
        
        html = template('t_c', data);
        $th.html(html);
        
    });
    
    $server_detail.bind('ajax.success', function(e, json){
    
        var $th = $(this), data, html;
        
        //$th.html(JSON.stringify(json));return;
        ///////////////////////////////////////////////
        
        if (json == null) {
            $th.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        
        
        ///////////////////////////////////////////
        data = {
            list: json
        };
        
        ///////////////////////////////////////////
        
        html = template('t_c', data);
        $th.html(html);
        
    });
    
    
    //请求服务器监听数据
    $server_status.getData(conf_1);
    $server_detail.getData(conf_2);
});

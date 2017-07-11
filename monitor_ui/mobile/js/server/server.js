//jQuery ready函数简写形式
$(function(){
    var version = urlParams('version');
    var status = urlParams('status') || '';
    var domain = domainURI();
    var $server_list = $('#server_list');
    var _url = "http://" + domain + "/mmsapi" + version;
    
    var conf_1 = {
        url: _url + "/get/server/@all" + status
    };
	
	
	///////////////////////////////////////////////////////////
	
	$('#show').toggle(function(){
		$(this).text('显示列表详情');
		$('#server_list li .box').hide();		

	},function(){
		$(this).text('隐藏列表详情');
		$('#server_list li .box').show();		
	});
	
	$('#see_all').click(function(){
		$server_list.getData({url:_url + "/get/server/@all"});
	});	
	
	$('#search').click(function(){
		//$server_list.getData(ajax_conf({url:_url + "/get/server/@all"}));
	});
	
	$('#add').click(function(){
		location.href = 'addServers.html?version=' + urlParams('version');;
	})
	
	///////////////////////////////////////////////////////////////	
    
    
    $server_list.bind('ajax.success', function(e, json){
    
        var $th = $(this), data, html;
        
       //$th.html(JSON.stringify(json));return;
        ///////////////////////////////////////////////
        
        if (json == null) {
            $th.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        
        $.each(json, function(k, v){
            v.push('serverStatus.html?name=' + k + '&version=' + version);
			v.push('../monitor/monitorEvent.html?selector=' + k + '&version=' + version);
            switch (v[0]) {
                case "0":
                    v.push("宕机");
                    break;
                case "1":
                    v.push("在线");
                    break;
                case "2":
                    v.push("注意");
                    break;
                case "3":
                    v.push("严重");
                    break;
                case "4":
                    v.push("注意+严重");
                    break;
                case "5":
                    v.push("未监控");
                    break;
                case "6":
                    v.push("Auto Scaling 未服务");
                    break;					
            }
        });
        
        ///////////////////////////////////////////
        data = {
            list: json
        };
        
        ///////////////////////////////////////////
        
        html = template('t_c', data);
        $th.html(html);
		
		//////////////////////////////////////////
		//$('#server_list li .box').hide();
		$('#server_list li a').click(function(e){
			 e.stopPropagation();
		})
		
		$('#server_list li p:first-child').toggle(function(){
			$(this).next('.box').slideDown('fast');
		},function(){
			$(this).next('.box').slideUp('fast');
		});		
        
    });
    
    
    //请求服务器监听数据
    $server_list.getData(conf_1);
    
});


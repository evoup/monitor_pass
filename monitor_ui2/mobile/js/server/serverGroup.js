//jQuery ready函数简写形式
$(function(){
    var version = urlParams('version');
    var domain = domainURI();
    var $server_group = $('#server_group');
    var _url = "http://" + domain + "/mmsapi" + version;
    
    var conf_1 = {
        url: _url + "/get/serverGroup/@all"
    };
	
	$('#show').toggle(function(){
		$(this).text('隐藏列表详情');
		$('#server_group li .box').show();
	},function(){
		$(this).text('显示列表详情');
		$('#server_group li .box').hide();		
	});
	
	$('#add').click(function(){
		location.href = 'addServers.html?version=' + urlParams('version');;
	})
    
    
    $server_group.bind('ajax.success', function(e, json){
    
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
        
        html = template('t_a', data);
        $th.html(html);
		
		///////////////////////////////////////////
		
		$('#server_group li .box').hide();
		
		$('#server_group li p:first-child').toggle(function(){
			$(this).next('.box').slideDown('fast');
		},function(){
			$(this).next('.box').slideUp('fast');
		});
		
		$('button.modify').click(function(){
			var name = $(this).attr('data-param');
			location.href = 'modifyServers.html?version=' + urlParams('version'); 
		});

		$('button.delete').click(function(){
			var name = $(this).attr('data-param');
			var conf = {url:_url + "/delete/serverGroup/@self/" + name};
			if(confirm('确定删除！')){
				$(this).parentsUntil('li').parent().delData(conf);
			}
						
		});		
        
    });
    
    //请求服务器监听数据
    $server_group.getData(conf_1);
});

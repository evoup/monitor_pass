$(function(){
    var version = urlParams('version');
    var status = urlParams('eventStatus') || '';
    var selector = urlParams('selector') || '';
    var domain = domainURI();
    var $event_list = $('#event_list');
    var _url = "http://" + domain + "/mmsapi" + version;
    
    var conf_1 = {
        url: _url + "/get/event" + status + "/@all" + (selector ? '/' + selector : ''),
		isTips: 1
    };
	
	var pullDownAction = Gmonitor.pullDownAction;
	
		
	////////////////////////////////////////////////////
	pullDownAction.push(function(){
		$event_list.getData(conf_1);
	});
	
		
	//页面初始化
	if(status){
		$('#control_bar .group a[data-status="' + status +'"]').addClass('current').siblings().removeClass('current');
	}
	
	$('#control_bar .group a').click(function(){
		var $th = $(this);
		var status = $th.attr('data-status');
		$th.addClass('current').siblings().removeClass('current');
		
		if(status){
			$event_list.getData({url:_url + "/get/event" + status + "/@all" });
		}
		
	});
    
    /////////////////////////////////////////////////////
    
    $event_list.bind('ajax.success', function(e, json){
    
        var $th = $(this), data, html;
        
        var _conf = function(){
            var conf = {
                type: 'post',
                url: _url + "/get/event" + status + "/@all"
            };
			
			conf.url = conf.url + (selector ? '/' + selector : '')
			
            return conf;
        };
        
        //$th.html(JSON.stringify(json));return;
        ///////////////////////////////////////////////
        
        if (json == null) {
            $th.html('<div class="error">服务器返回的数据为空</div>');
            return;
        }
        
        $.each(json.records, function(k, v){
            v.push('../server/serverStatus.html?name=' + v[0] + '&version=' + version);
        });
        
        ///////////////////////////////////////////
        data = json;
        
        html = template('t_c', data);
        $th.html(html);
        
        ///////////////////////////////////////////
		
		
		//分页程序
        if ($("#total_page").text() * 1 <= 1) {
            $('#page_info').hide();
            return;
        }
        
        $("#next_page").click(function(){
            var current = Number($("input[name='current_page']").val()), next = current + 1, line_per_page = $("select[name='line_per_page']").val(), total = $("#total_page").text();
            var info = {
                "line_per_page": line_per_page,
                "current_page": next
            };
            var conf = $.extend(true, {
                data: info
            }, _conf());
            if (current != total) {
                $event_list.getData(conf);
            }
        });
        
        
        $("#prev_page").click(function(){
            var current = Number($("input[name='current_page']").val());
            var prev = current - 1, line_per_page = $("select[name='line_per_page']").val();
            var info = {
                "line_per_page": line_per_page,
                "current_page": prev
            }
            var conf = $.extend(true, {
                data: info
            }, _conf());
            if (current != 1) {
                $event_list.getData(conf);
            }
        });
        
        
        $("#go").click(function(){
            var current = Number($("input[name='current_page']").val()), line_per_page = $("select[name='line_per_page']").val(), total = $("#total_page").text();
            var info = {
                "line_per_page": line_per_page,
                "current_page": current
            };
            var conf = $.extend(true, {
                data: info
            }, _conf());
            if (current >= 1 && current <= total) {
                $event_list.getData(conf);
            }
        });
        
        
        $("#first_page").click(function(){
            var line_per_page = $("select[name='line_per_page']").val();
            var info = {
                "line_per_page": line_per_page,
                "current_page": 1
            }
            var conf = $.extend(true, {
                data: info
            }, _conf());
            $event_list.getData(conf);
        });
        
        
        $("#last_page").click(function(){
            var line_per_page = $("select[name='line_per_page']").val(), last_page = $("#total_page").text();
            var info = {
                "line_per_page": line_per_page,
                "current_page": last_page
            }
            var conf = $.extend(true, {
                data: info
            }, _conf());
            $event_list.getData(conf);
        });
        
    });
    
    
    //请求服务器监听数据
    $event_list.getData(conf_1);
    
});

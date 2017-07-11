/*
 * 判断对象属性是否为空
 */
function empty(obj){
    for (var name in obj) {
        return false;
    }
    return true;
}
/*
 * 实现继承功能
 */
function inherit(parent,sub){
	var o = Object.create(parent);
	for(var i in sub){
		o[i] = sub[i];
	}
	return o;
}
/*
 * 包装jQuery ajax函数
 */ 
function ajaxConf(options){
    var de = {
        type: "get",
        url: '',
        async: true,
        cache: false,
        dataType: "json",
        beforeSend: function(){
        },
        success: function(){
        },
        complete: function(jqXHR, textStatus){
            //$.tips('请求完成！');
        },
        error: function(jqXHR, textStatus, errorThrown){
            switch (jqXHR.status) {
                case 400:
                    $.tips('数据不存在！');
                    break;
                case 500:
                    $.tips('服务器出错！');
                    break;
            }
        }
    };
    
    return $.extend(de, options);
}

/*
 * 截取域名
 */ 
function domainURI(){
    return location.hostname;
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

/*
 * 封装桌面提醒
 */
function message(parms){
    var icon = 'http://' + domainURI() + "/view/monitorui/client/assets/img/icon-warning.png";
    var title = "smartMad monitor";
    var body = parms.body;
    var notice = window.webkitNotifications.createNotification(icon, title, body);
    
    notice.onclick = function(){
        window.focus();
		notice.close();
    }
    
    notice.show();
    
    setTimeout(function(){
        notice.close();
    }, 10000);
}
 
 
///////////////////////////////////////////////////////
// 操作提示
///////////////////////////////////////////////////////
$.fn.tips = function(parent){
    var $parent = $(parent || 'body');
    var w = $parent.innerWidth() * 0.4 + 'px', h, top, left;
    var wraper = $('<div class="tipsBox"></div>');
    
    this.addClass('tips1').css({
        'width': w
    });
    this.appendTo(wraper);
    wraper.appendTo($parent);
    
    w = this.width()
    h = this.height();
    top = '-' + h / 2 + 'px';
    left = '-' + w / 2 + 'px';
    this.css({
        'top': '2px',
        'left': left
    })
    
    wraper.animate({
        'opacity': '1'
    }, 500,function(){
		$(this).addClass('animated wobble');
	});
	
    setTimeout(function(){
        wraper.animate({
            'opacity': '0'
        }, 500, function(){
            wraper.remove();
        });
    }, 3000);
	
	return this;
};

$.tips = function(massge){
    $('<div>' + massge + '</div>').tips();
};

///////////////////////////////////////////////////////
//监听enter键
//////////////////////////////////////////////////////
$.fn.enterPress = function(call){
	call = $.proxy(call,this);
	
	var fn = function(e){
		if(e.which == 13){
			call(e);
		}		
	};
	
	this.focus(function(){
		$(this).keypress(fn);
	});
	this.blur(function(){
		$(this).unbind('keypress',fn);
	});
	
	return this;
};
///////////////////////////////////////////////////////
//设置loading
//////////////////////////////////////////////////////
$.fn.setLoading = function(po){
	po = po || {};
	
	var parent = po.parent || this;
	var height = parent.height();
	var loading = po.loading || '<span class="_loading" name="_loading_"></span>';
	
	loading = $(loading).appendTo(parent);
	
	if( parent.css('position') == 'static'){
		parent.css({'position':'relative'});
	}
	
	return this;
};

///////////////////////////////////////////////////////
//清除loading
//////////////////////////////////////////////////////
$.fn.clearLoading = function(){
	this.find('[name="_loading_"]').remove();
	return this;
};

///////////////////////////////////////////////////////
// 扩展jQuery 为实例对象绑定ajax数据请求 
///////////////////////////////////////////////////////
$.fn.getData = function(conf){
	
	var that = this;
	
	var interval = conf.interval;
    var noLoading = conf.noLoading;
    var isTips = conf.isTips;
    var isDisplayError = conf.isDisplayError;
	
    conf.type = conf.type || 'get';
    
	conf.complete = $.proxy(conf.complete || function(json, textStatus, jqXHR){
		
	}, this);
		
    conf.success = $.proxy(conf.success || function(json, textStatus, jqXHR){
		if(json == null || empty(json)){
			this.html('<span class="error">没有相关数据.</span>');
			return;
		}
        this.trigger('ajax.success', [json]);
    }, this);
    
	
	conf.error = $.proxy(conf.error || function(jqXHR, textStatus, errorThrown){
		if(jqXHR.status == 401){
			alert('你尚未登录系统，请先登录系统.');
			location.href = 'http://' + domainURI() + (domainURI() == '27.115.15.8' ? '/view/monitorui' : (domainURI() == '211.136.107.44' ? '/monitorui' : '') ) +  '/client/login.html?version=' + urlParams('version');
		}
		if(jqXHR.status == 403){
			this.html('<span class="error2">ERROR !<br>你没有权限查看相关内容或进行相关操作 !</span>');
		}else{
			this.html('<span class="error2">ERROR!<br>textStatus: ' + jqXHR.status + '<br>errorThrown: ' + errorThrown + '</span>');
		}		
		
	}, this);
	
	
	conf = ajaxConf(conf);
    
    if (interval) {
		that.clearInterval();
        that[0].setIntervalId = setInterval(function(){
            $.ajax(conf)
        }, interval);
    }
    
    !noLoading && this.html('<span style="margin:5px auto;display:inline-block;width:100%;text-align:center;" name="_loading_"><svg width="16" height="16" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg" version="1.1"><path d="M 150,0 a 150,150 0 0,1 106.066,256.066 l -35.355,-35.355 a -100,-100 0 0,0 -70.711,-170.711 z" fill="#3d7fe6"><animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 150 150" to="360 150 150" begin="0s" dur="1s" fill="freeze" repeatCount="indefinite" /></path></svg></span>');
	
	
	setTimeout(function(){
		$.ajax(conf);
	},30);
    
    return this;
};

///////////////////////////////////////////////////////
//取消绑定jquery实例对象上面的定时器
///////////////////////////////////////////////////////
$.fn.clearInterval = function(){
	var id = this[0].setIntervalId;
	if(id){
		//console.log('clearInterval');
		clearInterval(id);
	}
	
	return this;
}

///////////////////////////////////////////////////////
//jQuery实例对象绑定ajax数据 post 操作
///////////////////////////////////////////////////////
$.fn.postData = function(conf){
	
	conf.type = conf.type || 'post';
	conf.dataType = '';
    
	conf.complete = $.proxy(conf.complete || function(json, textStatus, jqXHR){
		this.clearLoading();
	}, this);
    
    conf.success = $.proxy(conf.success || function(json, textStatus, jqXHR){
		$.tips('操作完成!');
    }, this);
    
	
	conf.error = $.proxy(conf.error || function(jqXHR, textStatus, errorThrown){
		if(jqXHR.status == 403){
			alert('ERROR!\n你没有权限进行相关操作 !');
			return;
		}		
		alert('操作失败!');
	}, this);
    
	this.setLoading(conf.loading);
    
	setTimeout(function(){
    	$.ajax(ajaxConf(conf));
	},30);
	
    return this;
};

///////////////////////////////////////////////////////
// jQuery实例对象绑定ajax数据删除请求
///////////////////////////////////////////////////////
$.fn.delData = function(conf){
	
	conf.type = conf.type || 'post';
	
	conf.complete = $.proxy(conf.complete || function(json, textStatus, jqXHR){
		this.clearLoading();
	}, this);
    
    conf.success = $.proxy(conf.success || function(json, textStatus, jqXHR){
        $.tips('已经删除!');
        this.fadeOut(1000,function(){
   			$(this).remove();
 		}); 
    }, this);
    
    conf.error = $.proxy(conf.error || function(jqXHR, textStatus, errorThrown){
		if(jqXHR.status == 403){
			alert('ERROR!\n你没有权限进行相关操作 !');
			return;
		}		
        alert('删除失败!');
    }, this);
	
	
	this.setLoading(conf.loading);
	
	setTimeout(function(){
		 $.ajax(ajaxConf(conf));
	},30);
    
    return this;
};

///////////////////////////////////////////////////////
// ajax加载html子页面
///////////////////////////////////////////////////////
$.fn.getHtml = function(conf,value){
	
	conf.type = 'get';
	conf.dataType = 'html';
    
	conf.complete = $.proxy(conf.complete || function(data, textStatus, jqXHR){
		
	}, this);
    
    conf.success = $.proxy(conf.success || function(data, textStatus, jqXHR){
        if (data && window.template) {
            data = template.compile(data)(value);
        }
        
        $(this).html(data);
    }, this);
    
	
	conf.error = $.proxy(conf.error || function(jqXHR, textStatus, errorThrown){
		alert('error:' + errorThrown);
	}, this);
	
	
	this.html('<div style="margin:5px auto;display:inline-block;width:100%;text-align:center;" name="_loading_"><svg width="16" height="16" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg" version="1.1"><path d="M 150,0 a 150,150 0 0,1 106.066,256.066 l -35.355,-35.355 a -100,-100 0 0,0 -70.711,-170.711 z" fill="#3d7fe6"><animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 150 150" to="360 150 150" begin="0s" dur="1s" fill="freeze" repeatCount="indefinite" /></path></svg></div>');
    
    
	setTimeout(function(){
    	$.ajax(ajaxConf(conf));
	},100);
	
    return this;	
};




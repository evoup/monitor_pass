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


///////////////////////////////////////////////////////
// jQuery操作提示插件
///////////////////////////////////////////////////////
$.fn.tips = function(parent){
    var $parent = $(parent || 'body');
    var w = $parent.innerWidth() * 0.4 + 'px', h, top, left;
    var wraper = $('<div class="tipsBox"></div>');
    
    this.addClass('tips').css({
        'width': w
    });
    this.appendTo(wraper);
    wraper.appendTo($parent);
    
    w = this.width()
    h = this.height();
    top = '-' + h / 2 + 'px';
    left = '-' + w / 2 + 'px';
    this.css({
        'top': top,
        'left': left
    })
    
    wraper.animate({
        'opacity': '0.8'
    }, 500);
    setTimeout(function(){
        wraper.animate({
            'opacity': '0'
        }, 500, function(){
            wraper.remove();
        });
    }, 2000);
}

$.tips = function(massge){
    $('<div>' + massge + '</div>').tips();
}

///////////////////////////////////////////////////////
//设置loading
//////////////////////////////////////////////////////
$.fn.setLoading = function(po){
	po = po || {};
	var parent = po.parent || this;
	var height = parent.height();
	var loading = $('<span class="_loading"></span>').appendTo(parent);
	parent.css({'position':'relative'});
	loading.click(function(){
		return false;
	});
};

///////////////////////////////////////////////////////
//清除loading
//////////////////////////////////////////////////////
$.fn.clearLoading = function(){
	this.find('span._loading').remove();
};

///////////////////////////////////////////////////////
// 扩展jQuery 为实例对象绑定ajax数据请求 
///////////////////////////////////////////////////////
$.fn.getData = function(conf){
	
	var interval = conf.interval;
    var noLoading = conf.noLoading;
    var isTips = conf.isTips;
    var isDisplayError = conf.isDisplayError;
	
    var that = this;
    
    this.bind('ajax.loading', function(){
        $(this).html('<div class="_loading_1"></div>');
    });
    
	conf.complete = conf.complete || function(json, textStatus, jqXHR){
		
	}
		
    conf.success = conf.success || $.proxy(function(json, textStatus, jqXHR){
        this.trigger('ajax.success', [json]);
    }, this);
    
	
	conf.error = conf.error || $.proxy(function(jqXHR, textStatus, errorThrown){
		$(this).html('<div class="error">请求数据出错!<br>textStatus: ' + jqXHR.status + '<br>errorThrown: ' + errorThrown + '</div>');
	}, this);
	
	conf.type = conf.type || 'get';
	conf = ajaxConf(conf);
    
    if (interval) {
        that.setIntervalId = setInterval(function(){
            $.ajax(conf)
        }, interval);
    }
    
    !noLoading && this.trigger('ajax.loading');
	
    $.ajax(conf);
    
    return this;
};

///////////////////////////////////////////////////////
//取消绑定jquery实例对象上面的定时器
///////////////////////////////////////////////////////
$.fn.clearInterval = function(){
	var id = this.setIntervalId;
	if(id){
		clearInterval(id);
	}
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
    
    conf.success = conf.success || $.proxy(function(json, textStatus, jqXHR){
		$.tips('请求完成!');
    }, this);
    
	
	conf.error = conf.error || $.proxy(function(jqXHR, textStatus, errorThrown){
		alert('操作失败!');
	}, this);
    
	this.setLoading(conf.loading);
    
	setTimeout(function(){
    	$.ajax(ajaxConf(conf));
	},100);
	
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
    
    conf.success = conf.success || $.proxy(function(json, textStatus, jqXHR){
        $.tips('已经删除!');
        this.remove();
    }, this);
    
    conf.error = conf.error || $.proxy(function(jqXHR, textStatus, errorThrown){
        alert('删除失败!');
    }, this);
	
	
	this.setLoading(conf.loading);
	
	setTimeout(function(){
		 $.ajax(ajaxConf(conf));
	},100);
    
    return this;
};




/*######################################################################################*/
/*######################################################################################*/
/*######################################################################################*/

///////////////////////////////////////////////////////
// common init 
///////////////////////////////////////////////////////



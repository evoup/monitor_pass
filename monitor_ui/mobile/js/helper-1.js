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
 * 扩展对象
 */
function extend(a,b){
	for(var i in b){
		a[i] = b[i];
	}
	return a;
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
    var reg = new RegExp("(\\?|&)" + param + "=([^&#]*)(&|$|#)", "i");
    var r = window.location.toString().match(reg);
    if (r != null) 
        return decodeURIComponent(r[2]);
    return null;
}


/*
 * ajax加载html子页面
 */
$.fn.getHtml = function(conf){
	
	conf.type = 'get';
	conf.dataType = 'html';
    
	conf.complete = $.proxy(conf.complete || function(data, textStatus, jqXHR){
		
	}, this);
    
    conf.success = $.proxy(conf.success || function(data, textStatus, jqXHR){
		var view = $('<div id="' + conf.callParams + '" class="layer"></div>').appendTo(this).html(data);
		var layer = $.extend(app.face[conf.callParams],{view:view});
		this.trigger('face.init', {layer:layer});
    }, this);
    
	
	conf.error = $.proxy(conf.error || function(jqXHR, textStatus, errorThrown){
		delete app.face[conf.callParams];
		this.trigger('face.init', {
			layer: app.face.error
		});
	}, this);
    
    
	setTimeout(function(){
    	$.ajax(ajaxConf(conf));
	},100);
	
    return this;	
};


/*
 * 扩展jQuery 为实例对象绑定ajax数据请求 
 */ 
$.fn.getData = function(conf, interval, isLoading,isTips){
	
	if(arguments.length == 1){
		conf = conf;
		interval = conf.interval;
		isLoading = conf.isLoading;
		isTips = conf.isTips;
	}
	
	
    var that = this;
    
    this.bind('ajax.loading', function(){
        $(this).html('<div class="_loading_2"></div>');
    });
    
    this.bind('ajax.error', function(e, jqXHR, textStatus, errorThrown){
        $(this).html('<div class="error">请求数据出错!<br>textStatus: ' + jqXHR.status + '<br>errorThrown: ' + errorThrown + '</div>');
    });
	
    
	conf.complete = function(json, textStatus, jqXHR){
		window.GiScroll && window.GiScroll.scrollTo(0, 0, 50);
		
	}
		
	
    if (isTips) {
        conf.complete = function(json, textStatus, jqXHR){
			window.GiScroll && window.GiScroll.scrollTo(0, 0, 50);
		}
    }
    
    conf.success = $.proxy(function(json, textStatus, jqXHR){
        this.trigger('ajax.success', [json]);
		$.tips('请求完成！');
    }, this);
    
    conf.error = $.proxy(function(jqXHR, textStatus, errorThrown){
        this.trigger('ajax.error', [jqXHR, textStatus, errorThrown]);
    }, this);
	
	
	conf = ajaxConf(conf);
    
    if (interval) {
        that.setIntervalId = setInterval(function(){
            $.ajax(conf)
        }, interval);
    }
    
    !isLoading && this.trigger('ajax.loading');
	
	setTimeout(function(){
		$.ajax(conf);
	},100);
    
    return this;
};

/*
 * 取消绑定jquery实例对象上面的定时器
 */
$.fn.clearInterval = function(){
	var id = this.setIntervalId;
	if(id){
		clearInterval(id);
	}
	
	return this;
}

/*
 * jQuery实例对象绑定ajax数据删除请求
 */
$.fn.delData = function(conf){
    var that = this;
    
    conf.success = $.proxy(function(json, textStatus, jqXHR){
        $.tips('删除数据成功');
        this.remove();
    }, this);
    
    conf.error = $.proxy(function(jqXHR, textStatus, errorThrown){
        $.tips('删除数据失败');
    }, this);
    
    $.ajax(conf);
    
    return this;
};

/*
 * 操作提示控件
 */
$.fn.tips = function(parent){
    parent = parent || '#mainbody';
    var $parent = $(parent);
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
        'opacity': '1'
    }, 500);
    setTimeout(function(){
        wraper.animate({
            'opacity': '0'
        }, 500, function(){
            wraper.remove();
        });
    }, 1000);
}
$.tips = function(massge){
    $('<div>' + massge + '</div>').tips();
}

/*
 * 封装手势滑动事件
 */
$.fn.slide = function(opt){
	opt = opt || {};
	
	var startX = 0; 
	var startY = 0; 
	var that = this[0];
	
	var slideLeft = opt.slideLeftCall || function(){history.back();};
	var slideRight = opt.slideRightCall || function(){history.forward();};

	function touchStart(e){
			var touch = e.touches[0];
			startX = touch.pageX;
			startY = touch.pageY;
	}
	function touchMove(e){
			e.preventDefault();
			that.removeEventListener('touchmove', touchMove, false);
			var x = 500;
			var touch = e.touches[0];
			$('#title span').html((touch.pageX - startX) + ' ' + (touch.pageY- startY ))
			
            if (Math.abs(touch.pageY - startY) < 24) {
                if (touch.pageX - startX > 24) {
					//$('#home').text('back');
					window.app.conf.aniIn = 'animated bounceInRight';
					window.app.conf.aniOut = 'animated bounceOutLeft';
                    slideLeft();
                }
                else if(touch.pageX - startX < -24){
					//$('#home').text('forward')
                    slideRight();
                }
            }

	}
	function touchEnd(e){
		that.addEventListener('touchmove', touchMove, false);
	}
	that.addEventListener('touchstart', touchStart, false);
	that.addEventListener('touchmove', touchMove, false);
	that.addEventListener('touchend', touchEnd, false);	
};


//#############################################

/*
 * 定义全局变量
 */
window.Gmonitor = {};
Gmonitor.pullDownAction = [];

//#############################################

/*
 * iscroll 控件
 */ 
function loaded(){
    var pullDownEl, pullDownOffset, pullUpEl, pullUpOffset, app = window.app;
    
    pullDownAction = function (){
		app && app.face.current.pullDownAction();
        //setTimeout(function(){GiScroll.refresh();},2000); 
    }
    
    pullUpAction = function (){
		GiScroll.refresh(); 
    }
    
    
    pullDownEl = document.getElementById('pullDown');
    pullDownOffset = pullDownEl && pullDownEl.offsetHeight || 0;
    pullUpEl = document.getElementById('pullUp');
    pullUpOffset = pullUpEl && pullUpEl.offsetHeight || 0;
    
    
    window.GiScroll = new iScroll('wrapper', {
		//snap: true,
		//momentum: false,
		//hScrollbar: true,
        checkDOMChanges: true,
		//useTransition: true,
		topOffset: pullDownOffset,		
        onRefresh: function(){
            if (pullDownEl.className.match('loading')) {
                pullDownEl.className = '';
                pullDownEl.querySelector('.pullDownLabel').innerHTML = '下拖刷新...';
            }
            else 
                if (pullUpEl.className.match('loading')) {
                    pullUpEl.className = '';
                    pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拖加载更多...';
                }
        },
        onScrollMove: function(){
            if (this.y > 5 && !pullDownEl.className.match('flip')) {
                pullDownEl.className = 'flip';
                pullDownEl.querySelector('.pullDownLabel').innerHTML = '释放刷新...';
                this.minScrollY = 0;
            }
            else 
                if (this.y < 5 && pullDownEl.className.match('flip')) {
                    pullDownEl.className = '';
                    pullDownEl.querySelector('.pullDownLabel').innerHTML = '下拖刷新...';
                    this.minScrollY = -pullDownOffset;
                }
                else 
                    if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
                        pullUpEl.className = 'flip';
                        pullUpEl.querySelector('.pullUpLabel').innerHTML = '释放刷新...';
                        this.maxScrollY = this.maxScrollY;
                    }
                    else 
                        if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
                            pullUpEl.className = '';
                            pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拖加载更多...';
                            this.maxScrollY = pullUpOffset;
                        }
        },
        onScrollEnd: function(){
            if (pullDownEl.className.match('flip')) {
                pullDownEl.className = 'loading';
                pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Loading...';
                pullDownAction(); 
            }
            else 
                if (pullUpEl.className.match('flip')) {
                    pullUpEl.className = 'loading';
                    pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading...';
                    pullUpAction(); 
                }
        }
    });
}

document.addEventListener('touchmove', function(e){
    e.preventDefault();
}, false);

document.addEventListener('DOMContentLoaded', function(){
    setTimeout(loaded, 200);
}, false);


//#############################################

/*
 * jQuery ready function
 * commit init
 */ 
$(function(){
	
	$('body').slide();
    
    $('#footer').delegate('a.top','click',function(){
        GiScroll.scrollTo(0, 0, 100);
    });
    
    $('<div id="pullDown"><span class="pullDownIcon"></span><span class="pullDownLabel">下拖刷新...</span></div>').prependTo('#mainbody');
    
    $('<div id="pullUp"><span class="pullUpIcon"></span><span class="pullUpLabel">上拖加载更多...</span></div>').appendTo('#mainbody');
    
    
    
})

/*
 *  整体框架;
 *  
 *  主要完成路由，视图加载，缓存功能
 *  
 */


$(function(){

	/*
	 * 应用命名空间
	 */
    var app = window.app = window.app || {};
    

    
    /*
     * 全局配置，缓存，界面
     */
	var face = app.face = {};
    var conf = app.conf = {};
    var cache = app.cache = {};
	
	conf.version = urlParams('version');
    conf.domain = domainURI();
	conf._url = "http://" + conf.domain + "/mmsapi" + conf.version;
	
	/*
	 * 界面切换动画class
	 */
	conf.aniIn = 'animated bounceInLeft';
	conf.aniOut = 'animated bounceOutRight';
	
	/*
	 * 界面控制器原型
	 */
    app.face.fn = {
        init: function(){
			this.active();
        },
        data: function(){
        },
        control: function(){
        },
        destory: function(){
        },
        clean: function(){
        },
        recover: function(){
        }, 
		pullDownAction: function(){
		},
		botMenuActive: function(){
			var botMenu = this.botMenu;
			if(!botMenu){
				return; 
			} 
			var footer = app.face.footer;
			footer.current.hide();
			botMenu.show();
			footer.current = botMenu;
		},
		active: function(){
			var view = this.view;
            this.recover();
            view.css({
                'display': 'block'
            });
			view.removeClass('animated bounceInLeft bounceOutRight bounceOutLeft bounceInRight');
			view.addClass(app.conf.aniIn);
			this.botMenuActive();
			
			app.conf.aniIn = 'animated bounceInLeft';
			app.conf.aniOut = 'animated bounceOutRight';			
        },
        freeze: function(){
			var view = this.view;
			view.removeClass('animated bounceInLeft bounceOutRight bounceOutLeft bounceInRight');
			view.addClass(app.conf.aniOut)
            setTimeout(function(){
				view.css({
                'display': 'none'
            });
			},500);
        }
    };	
	
	
    /*
     * 主界面组件部分，头，主体，底部
     */
    var $header = face.head = $('#header');
    var $content = face.content = $('#content');
    var $footer = face.footer = $('#footer');	
	var $title = face.title = $('#title');
    
    /*
     * 应用加载界面
     */
    var loading = face.loading = inherit(app.face.fn, {
        view: $('<div id="loading" class="layer _loading_2"></div>').appendTo($content),
        active: function(){
            this.view.show();
            this.botMenuActive();
        },
        freeze: function(){
            this.view.hide();
        },
    });
    
	/*
	 * 应用出错界面
	 */
    var error = face.error = inherit(app.face.fn, {
        view: $('<div id="error" class="layer error">加载数据出错，请稍后重试..</div>').appendTo($content),
		active: function(){
            this.view.show();
			this.botMenuActive();
        },
        freeze: function(){
            this.view.hide();
        }		
    });
    
	/*
	 * 初始显示界面为loading界面
	 */
    face.current = loading;
	
	$footer.current = $footer.defaul = $('#control_bar');
    
    
    /*
     * 界面绘制
     */
    $content.bind('face.init', function(e, params){
		var layer = params.layer;
        face.current.freeze();
        layer.active();
        face.current = layer;
    });
    
    /*
     * 根据url锚点进行路由，缓存
     */
    $(window).bind('hashchange', function(){
		var hash = location.hash, layerId, layer, path, param;
		if(!hash){
			location.hash = '#main';
			return;
		} 
        hash = hash.replace(/^#/, '').split('?');
        layerPath = hash.shift().replace(/-/g,'/');
		layerId = layerPath.split('/').pop();
        layer = face[layerId];
        path = 'app/' + layerPath + '.html';
		
		$title.text('Madhouse Monitor');
		$footer.current.hide();
		$footer.defaul.show();
		$footer.current = $footer.defaul;		
		
        if (layer) {
            $content.trigger('face.init', {layer:layer});
        }
        else {
            $content.trigger('face.init', {layer:loading});
			
			app.face[layerId] = inherit(app.face.fn,{});
			
            $content.getHtml({
                url: path,
				callParams:layerId
            });
        }
        
    });
	
	
    /*
     * 应用初始化
     */
    $content.trigger('face.init', {layer:loading});
    $(window).trigger('hashchange');
    
});





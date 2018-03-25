/**
	rockmenu 菜单选择插件
	caratename：chenxihu
	caratetime：214-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.xh829.com
*/

(function ($) {
	
	function rockmenu(element, options){
		var obj		= element;
		var can		= options;
		var json	= can.data;
		var rand	= ''+parseInt(Math.random()*99999)+''; 
		var me		= this;
		var hidebo	= false;
		var timeas	= null;
		this.rand	= rand;
		//初始化
		this.init	= function(){
			$(obj).click(function(){
				me.setcontent();
			});
			$(obj).mouseout(function(){
				me.amouseout();
			});
			$(obj).mouseover(function(){
				me.amouseover();
			});
		};
		this.amouseover	= function(){
			hidebo	= false;
		};
		this.amouseout	= function(){
			clearTimeout(timeas);
			hidebo	= true;
			timeas	= setTimeout(function(){
				if(hidebo)me.hidemenu();
			},100);
		};
		this.setcontent	= function(){
			if(json.length<=0)return false;//没有数据
			if(document.getElementById('rockmenu_'+rand+'')){
				this.setweizhi();
				$('#rockmenu_'+rand+'').slideDown(100);
				return false;
			}
			
			var str	= '<div class="rockmenu" id="rockmenu_'+rand+'">'+
			'<div class="rockmenuli">'+
			'<ul>';
			for(var i=0; i<json.length; i++){
				str+='<li temp="'+i+'">';
				if(typeof(json[i].checked)=='boolean'){
					str+='<input type="checkbox" value="'+i+'" name="rockmenu_checked_'+rand+'" '+((json[i].checked)?'checked':'')+'> ';
				}
				if(json[i].icons)str+='<img src="'+PUBLIC+''+json[i].icons+'" width="16" height="16" align="absmiddle">&nbsp ';//图标
				str+=json[i][can.display];
				str+='</li>';
			}
			str+='</ul>'+
			'</div>'+
			'</div>';
			$('body').prepend(str);
			var oac	= $('#rockmenu_'+rand+'');
			this.setweizhi();
			oac.slideDown(100);
			oac.find('li').mouseover(function(){this.className='li01';});
			oac.find('li').mouseout(function(){this.className='';});
			oac.find('li').click(function(){me.itemsclick(this);});
			oac.find('li').find('input').click(function(){me.checkboxclick(this);});
			if(can.width!=0){
				oac.css('width',''+can.width+'px');
			};
			oac.mouseover(function(){
				hidebo = false;
			});
			oac.mouseout(function(){
				me.amouseout();
			});
		};
		this.setweizhi = function(){
			var off		= $(obj).offset();
			var o		= $('#rockmenu_'+rand+'');
			var l		= off.left+can.left;
			if(l<=0)l = 1;
			var ag		= l+o.width()-winWb();
			if(ag>0)l=l-ag-5;//超出边界
			o.css({'left':''+(l)+'px','top':''+(off.top+can.top)+'px'});
		};
		//项目单击
		this.itemsclick = function(o){
			var oi	= $(o).attr('temp');
			var noi	= parseInt(oi);
			can.itemsclick(json[noi],noi,this);
			if(can.autohide)this.hidemenu();
		};
		this.hidemenu	= function(){
			$('#rockmenu_'+rand+'').hide();
		};
		this.checkboxclick	= function(o){
			var noi	= parseInt(o.value);
			can.checkboxclick(o,json[noi],noi,this);
		};
		//获取复选框的值
		this.getcheck		= function(bo){
			var o = $("input[name='rockmenu_checked_"+rand+"']");
			var ca= [];
			for(var i=0; i<o.length; i++){
				if(o[i].checked==bo){
					ca.push(json[parseInt(o[i].value)]);
				}
			}
			return ca;
		};
	};
	
	$.fn.rockmenu = function(options){
		var defaultVal = {
			data:[],
			display:'name',//显示的名称
			left:0,
			top:25,
			width:0,
			checked:false,//是否有复选框
			autohide:true,//是否自动隐藏
			itemsclick:function(){},
			checkboxclick:function(){}
		};
		var can = $.extend({}, defaultVal, options);
		var menu = new rockmenu($(this), can);
		menu.init();
		return menu;
		/*
		return this.each(function(){
			var can = $.extend({}, defaultVal, options);
			var menu = new rockmenu($(this), can);
			menu.init();
			return menu;
		});	*/
	};
})(jQuery); 
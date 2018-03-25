/**
	placeholder,placeval 让低版本ie可以支持placeholder属性，不够获取的值需用使用$('#id').placeval();//来获取
	caratename：chenxihu
	caratetime：214-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.xh829.com
*/

(function ($) {
	
	$.fn.placeholder	= function(){
		return this.each(function () {
			var _t 		= $(this);
			var val		= _t.val();
			var holder	= _t.attr('placeholder');
			_t.attr('holder','false');
			
			if(holder!=''){
				if(val==''){
					_t.css('color','#888888');
					_t.val(holder);
				}else{
					_t.attr('holder','true');
				};
				
				//获取焦点
				_t.focus(function(){
					if(_t.val()==holder){
						if(_t.attr('holder')=='false'){
							_t.val('');
						}
					}
				});
				
				_t.blur(function(){
					if(_t.val()==''){
						_t.val(holder);
						_t.attr('holder','false');
						_t.css('color','#888888');
					};
				});
				
				_t.keyup(function(){
					_t.css('color','');
					_t.attr('holder','true');
				});
			}
		});
	};
	
	$.fn.placeval	= function(){
		var _t 		= $(this);
		var val		= _t.val();
		var holder	= _t.attr('placeholder');
		if(holder!='' && holder !=null){
			var hold	= _t.attr('holder');
			if(val == holder && hold=='false')val = '';
		};
		return val;
	};
	
})(jQuery);
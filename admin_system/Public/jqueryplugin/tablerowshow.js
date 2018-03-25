/**
	tablerowshow 显示隐藏表格列插件,必须和rockmenu菜单显示一起使用
	caratename：chenxihu
	caratetime：214-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.xh829.com
*/

(function ($) {
	
	/**
		element 对应表格对象
	*/
	function tablerowshow(element, element2, options){
		var ele		= element;
		var can		= options;
		var rand	= ''+parseInt(Math.random()*99999)+''; 
		var me		= this;
		this.data	= [];
		
		this.init	= function(){
			
			var obj	= ele.find('th');
			var da	= [{name:'恢复默认列',value:''}];
			for(var i=0; i<obj.length; i++){
				var nam	= $(obj[i]).text().replace(/\s/gi,'');
				da.push({
					name:nam,
					checked:true,
					value:$(obj[i]).attr('referto')
				});
			};
			this.data	= da;
			this.initrowhide();
			for(var i=0; i<obj.length; i++){
				da[i+1].checked=(obj[i].style.display!='none')?true:false;
			};
			//创建下拉框
			var mcan	= {data:da,
				checked:true,//有复选
				autohide:false,
				checkboxclick:this.checkboxclick,
				itemsclick:function(a,oi){
					me.itemsclick(oi);
				}
			};
			for(var a in can)mcan[a]=can[a];
			var abc=element2.rockmenu(mcan);
		};
		this.itemsclick			= function(oi){
			if(oi!=0)return;
			js.savecookie(SESSQOM+"menu_hide_"+can.num+"_"+ADMINID, '');
			location.reload();
		};
		this.rowhideshow 		= function(oi,lx){
			if(!lx){
				ele.find('th:eq('+oi+')').hide();
				ele.find('tr').find('td:eq('+(oi+1)+')').hide();
			}else{
				ele.find('th:eq('+oi+')').show();
				ele.find('tr').find('td:eq('+(oi+1)+')').show();
			}
		};
		
		this.checkboxclick	= function(o,a,oi,nobj){
			me.rowhideshow(oi-1,o.checked);
			//获取选中的列保存到cookie里面
			var carr	= nobj.getcheck(false);
			var menuStr = '0';
			for(var i=0;i<carr.length;i++){
				menuStr+=','+carr[i].value+'';
			}
			//alert(menuStr)
			js.savecookie(SESSQOM+"menu_hide_"+can.num+"_"+ADMINID, menuStr);
		};
		
		this.initrowhide	= function(){
			var cook	= js.cookie(SESSQOM+"menu_hide_"+can.num+"_"+ADMINID);
			if(!cook)return false;
			cook		= ','+cook+',';
			for(var i=0; i<this.data.length; i++){
				var val	= this.data[i].value;
				if(cook.indexOf(','+val+',')>0){
					this.rowhideshow(i-1,false);//存在就隐藏
				}
			}
		}
	};
	
	$.fn.tablerowshow = function(tabss, options){
		var defaultVal = {
			num:MENUID,
			left:0,
			top:25
		};
		var can = $.extend({}, defaultVal, options);
		var nobj = new tablerowshow($('#'+tabss+''),$(this), can);
		nobj.init();
		return nobj;
	};
})(jQuery); 
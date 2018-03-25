/**
	在线编辑器
	editer 类
	创建：chenxihu
	创建时间：2014-03-24
	使用方法
	var editor	= new xyediter();//创建类
	editor.load('区域id',{参数});
*/
var xyediterarray	= [];
function xyediter(){
	var editer	= {
		oi:xyediterarray.length,
		id:'',
		defaults:{width:'600px',height:'150px',format:'font,fontsize,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,align_center,align_justify,align_left,align_right,|,list_bullets,list_numbers,indent,indent_remove,|,link_add,link_del,hr,|,removeformat,selectall,marks,images,undo'},
		divparent:null,
		divself:null,
		rand:''+parseInt(Math.random()*999999)+'',//随机数,
		html:false,
		body:false,
		opt:null,
		formatarray:{
			'font':['字体样式','FontName'],
			'fontsize':['字体大小','FontSize'],
			'forecolor':['字体颜色','ForeColor'],
			'marks':['插入特殊符号','Marks'],
			'images':['插入图片','Images'],
			'backcolor':['字体背景色','BackColor'],
			'html':['查看源码',''],
			'arrow_out':['全屏',''],
			'emot':['插入表情',''],
			'cut':['剪切','Cut'],
			'paste':['粘贴','Paste'],
			'copy':['复制','Copy'],
			'selectall':['全选','SelectAll'],
			'hr':['添加水平线','InsertHorizontalRule'],
			'link_add':['添加链接','CreateLink'],
			'link_del':['删除链接','UnLink'],
			'bold':['加粗','Bold'],
			'italic':['斜体','Italic'],
			'underline':['下划线','Underline'],
			'strikethrough':['删除线','StrikeThrough'],
			'align_center':['居中对齐','JustifyCenter'],
			'align_justify':['两端对齐','JustifyFull'],
			'align_left':['左对齐','JustifyLeft'],
			'align_right':['右对齐','JustifyRight'],
			'list_bullets':['项目符号','insertunorderedlist'],
			'list_numbers':['数字编号','insertorderedlist'],
			'indent':['增加缩进量','Indent'],
			'indent_remove':['减小缩进量','Outdent'],
			'undo':['返回上一步','Undo'],
			'removeformat':['删除格式','RemoveFormat']
		},
	
		
		/**
			初始化加载
		*/
		load:function(id,options)
		{
			if(!get(id))return;//id不存在
			this.id	= id;
			if(!options)options={};
			this.divself	= $('#'+id+'');
			if(!options.height)options.height = this.divself.height()+'px';
			if(!options.width)options.width = this.divself.width()+'px';
			this.defaults	= js.apply(this.defaults,options);	//设置参数属性
			this.divparent	= $('#'+id+'').parent();
			this.divself.hide();
			var str			= '';
			if(this.oi==0){
				str+='<link href="'+PUBLIC+'editer/editer.css" rel="stylesheet" type="text/css" />';
				str+='<iframe style="display:none" src="'+APP+'/System/xyediter" name="xyediterupimages"></iframe>';
			}
			str+= '<div id="editdiv_'+this.rand+'" style="border-radius:5px;border:1px #cccccc solid;width:'+this.defaults.width+';">'+
				'<div id="editertools_'+this.rand+'" style="background:#eeeeee;border-bottom:1px #cccccc solid;padding:2px 5px;"></div>'+
				'<div style="padding:2px"><iframe style="height:'+this.defaults.height+'" id="editeriframe_'+this.rand+'" width="100%" marginwidth="0"  marginheight="0" frameborder="0" contentEditable="true" src="about:blank" allowTransparency="true" ></iframe></div>'+
				'</div>';
				
			this.divparent.prepend(str);
			this.inittools();
			if(this.defaults.format=='')$('#editertools_'+this.rand+'').hide();//没有工具条
			this.html		= get('editeriframe_'+this.rand+'').contentWindow;
			this.editinit();
			//加载工具提醒
			$("img[temp='editertoolsimg_"+this.rand+"']").tooltip();
		},
		
		editinit:function()
		{
			var me	= this;
			this.html.document.designMode = 'on';
			this.html.document.contentEditable = true;
			this.html.document.open();
			this.html.document.write('<html><head><style>body,th,td{font-family:Verdana;font-size:14px;}p{padding:0px;margin:0px}</style></head><body style="padding:0px;overflow:auto;margin:0px"></body></html>');
			this.html.document.close();
			this.body	= this.html.document.body;
			this.setValue(this.divself.val());
			$(this.html.document).blur(function(){me.bodyblur()});//火狐,google的浏览器
			$(this.body).blur(function(){me.bodyblur()});//IE的
			
			$(this.html.document).focus(function(){me.bodyfocus()});
			$(this.body).focus(function(){me.bodyfocus()});
		},
		bodyblur:function()
		{
			this.divself.val(this.getValue());
		},
		bodyfocus:function()
		{
			$('#editmenushowmenu').remove();
		},
		focus:function()
		{
			this.html.focus();
			$('#editmenushowmenu').remove();
		}
		,
		//加载工具条
		inittools:function()
		{
			var str = this.defaults.format;
			if(str=='')return;
			var a	= str.split(',');
			var o	= $('#editertools_'+this.rand+'');
			for(var i=0; i<a.length; i++){
				var lx	= a[i];
				var s	= '';
				var ac	= this.formatarray[lx];
				if(lx=='|'){
					s = '<img src="'+PUBLIC+'editer/images/white.gif" class="toolsplit">';
				}else if(lx=='-'){
					s = '<br>';
				}else if(ac){
					s = '<img data-toggle="tooltip" data-placement="top" temp="editertoolsimg_'+this.rand+'" src="'+PUBLIC+'editer/images/text_'+lx+'.png"  class="toolsicons" onmouseover="this.className=\'toolsicons toolsicons_over\'" onmouseout="this.className=\'toolsicons\'" onclick="xyediterarray['+this.oi+'].toolsclick(\''+lx+'\',this)" title="'+ac[0]+'">';
				}
				o.append(s);
			}
		},
		
		toolsclick:function(val,otol)
		{
			var ac	= this.formatarray[val];
			//字体颜色和背景色
			var opt		= ac[1];
			this.opt	= opt;
			if(opt=='ForeColor'||opt=='BackColor'){
				this.changecolor(otol);
				return;
			}
			if(opt=='FontName'){
				this.changefontname(otol);
				return;
			}
			if(opt=='FontSize'){
				this.changefontsize(otol);
				return;
			}
			if(opt=='Marks'){
				this.changemarks(otol);
				return;
			}
			if(opt=='Images'){
				this.changeimages(otol);
				return;
			}
			if(opt=='')return;
			switch(opt){
				case 'CreateLink'://创建链接
					if(Url = prompt('为选中的文本添加链接——URL地址：', 'http://')){
						this.execcom(encodeURI(Url));
					}
				break;
				case 'insertimg'://插入图片
					
				break;
				default:
					this.execcom(null);
				break;
			}
			
		},
		execcom:function(val,opt)
		{
			if(!opt)opt= this.opt;
			try{this.html.document.execCommand(opt,false,val);}catch(e){}
			this.focus();
		},
		createmenu:function(otol,w)
		{
			$('#editmenushowmenu').remove();
			var off	= $(otol).offset();
			var Y	= off.top+22;
			var X	= off.left;
			var txt='<div id="editmenushowmenu" style="top:'+(Y)+'px;left:'+X+'px;width:'+w+'px" class="showmenuys" onmousedown="this.setCapture()" onmouseup="this.releaseCapture()">';
			txt+='</div>';
			$('body').prepend(txt);
		},
		changecolor:function(otol)
		{
			this.createmenu(otol,154);
			var txt='<ul>';
			var colorBarArr	= {"Black": "黑色", "Sienna": "赭色", "DarkOliveGreen": "暗橄榄绿色", "DarkGreen": "暗绿色", "DarkSlateBlue": "暗灰蓝色", "Navy": "海军色", "Indigo": "靛青色", "DarkSlateGray": "墨绿色", "DarkRed": "暗红色", "DarkOrange": "暗桔黄色", "Olive": "橄榄色", "Green": "绿色", "Teal": "水鸭色", "Blue": "蓝色", "SlateGray": "灰石色", "DimGray": "暗灰色", "Red": "红色", "SandyBrown": "沙褐色", "YellowGreen": "黄绿色", "SeaGreen": "海绿色", "MediumTurquoise": "间绿宝石", "RoyalBlue": "皇家蓝", "Purple": "紫色", "Gray": "灰色", "Magenta": "红紫色", "Orange": "橙色", "Yellow": "黄色", "Lime": "酸橙色", "Cyan": "青色", "DeepSkyBlue": "深天蓝色", "DarkOrchid": "暗紫色", "Silver": "银色", "Pink": "粉色", "Wheat": "浅黄色", "LemonChiffon": "柠檬绸色", "PaleGreen": "苍绿色", "PaleTurquoise": "苍宝石绿", "LightBlue": "亮蓝色", "Plum": "洋李色", "White": "白色"};
			for(key in colorBarArr){
				txt+='<li title="'+colorBarArr[key]+'" style="background-color:'+key+';" onclick="xyediterarray['+this.oi+'].execcom(\''+key+'\')">&nbsp;</li>';
			}
			txt+='</ul>';
			$('#editmenushowmenu').html(txt);
		},

		changefontname:function(otol)
		{
			this.createmenu(otol,'');
			var fontBarArr = {"SimSun": "宋体", "SimHei": "黑体", "Microsoft YaHei": "微软雅黑", "FangSong_GB2312": "仿宋_GB2312", "KaiTi_GB2312": "楷体_GB2312", "MingLiU": "明柳", "Verdana": "Verdana"};
			var txt = '';
			for(key in fontBarArr){
				txt+='<div title="'+fontBarArr[key]+'" onmouseover="this.className=\'divys1\'" onmouseout="this.className=\'\'" onclick="xyediterarray['+this.oi+'].execcom(\''+key+'\')" style="font-family:\''+key+'\'">'+fontBarArr[key]+'</div>';
			}
			$('#editmenushowmenu').html(txt);
		},
		
		changefontsize:function(otol)
		{
			this.createmenu(otol,'60');
			var txt = '';
			for(j=1; j<=7; j++) {
				txt+='<div title="'+j+'号字" onmouseover="this.className=\'divys1\'" onmouseout="this.className=\'\'" onclick="xyediterarray['+this.oi+'].execcom(\''+j+'\')"><font style="font-size:'+(8+j*2)+'px">'+j+'号</font></div>';
			}
			$('#editmenushowmenu').html(txt);
		},
		
		changemarks:function(otol)
		{
			var marks = ["※", "§", "〃", "№", "〓", "○", "●", "△", "▲", "◎", "☆", "★", "◇", "◆", "□", "■", "▽", "▼", "㊣", "♀", "♂", "⊕", "⊙", "↑", "↓", "←", "→", "↖", "↗", "↙", "↘", "【", "】", "『", "』", "≈", "≠", "＝", "≤", "≥", "＜", "＞", "≮", "≯", "∷", "±", "＋", "－", "×", "÷", "／", "∫", "∮", "∝", "∞", "∧", "∨", "∑", "∏", "∪", "∩", "∈", "∵", "∴", "⊥", "∥", "∠", "⌒", "⊙", "≌", "∽", "√", "≦", "≧", "≒", "≡", "～", "∟", "⊿", "㏒", "㏑", "°", "′", "″", "＄", "￥", "〒", "￠", "￡", "％", "＠", "℃", "℉", "﹩", "﹪", "‰", "﹫", "㏕", "㎜", "㎝", "㎞", "㏎", "㎡", "㎎", "㎏", "㏄", "°", "○", "¤", "Ⅰ", "Ⅱ", "Ⅲ", "Ⅳ", "Ⅴ", "Ⅵ", "Ⅶ", "Ⅷ", "Ⅸ", "Ⅹ", "€", "￥", "￡", "?", "?", "?", "…"];
			this.createmenu(otol,'250');
			var txt = '<ul>';
			for(i=0;i<marks.length;i++){
				txt+='<li title="'+marks[i]+'" onmouseover="this.className=\'liys1 divys1\'" onmouseout="this.className=\'liys1\'" class="liys1" onclick="xyediterarray['+this.oi+'].insertFile(\''+marks[i]+'\')">'+marks[i]+'</li>';
			}
			txt+='</ul>';
			$('#editmenushowmenu').html(txt);
		},
		
		//添加图片
		changeimages:function(otol){
			xyediterupimages.clickup(this.oi);
		},
		//图片回传
		uploadback:function(files){
			this.insertFile(files.path);
		},
		
		//插入文件
		insertFile:function(val) {
			this.html.focus();
			var newVal=val;
			if(!val)return;
			var now 	= new Date(); 
			var regId 	= now.getYear()+''+now.getMonth()+''+now.getDate()+''+now.getHours()+''+now.getMinutes()+''+now.getSeconds()+''+now.getMilliseconds();
			//插入特殊符号
			if (this.opt == 'Marks') {
				newVal = 'insertMark:'+regId;
			}
			
			this.execcom(newVal,'InsertImage');
			if(this.opt=='Marks'){
				var reg = new RegExp('<img src="'+newVal+'">', 'ig');
				this.body.innerHTML = this.body.innerHTML.replace(reg,val);
			}
		},
		
		//获取编辑器上的html内容
		getValue:function()
		{
			return this.body.innerHTML;
		},
		
		//设置编辑器的内容
		setValue:function(str)
		{
			this.body.innerHTML	= str;
		},
		
		//获取编辑器上的内容不带HTML格式的
		getText:function()
		{
			var val = '';
			if(document.all){
				val=this.body.innerText
			}else{
				val=this.body.textContent
			}
			return val;
		}
	}
	
	xyediterarray.push(editer)
	return editer;
}
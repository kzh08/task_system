<?php 
/**
	颜色转换本文件主要用于切换颜色时预览的样式
*/
function color($color,$l=127.5)
{
	$r=hexdec(substr($color,1,2));
	$g=hexdec(substr($color,3,2));
	$b=hexdec(substr($color,5));
	$yb=127.5;
	if($l > $yb){
		$l = $l - $yb;
		$r = ($r * ($yb - $l) + 255 * $l) / $yb;
		$g = ($g * ($yb - $l) + 255 * $l) / $yb;
		$b = ($b * ($yb - $l) + 255 * $l) / $yb;
	}else{
		$r = ($r * $l) / $yb;
		$g = ($g * $l) / $yb;
		$b = ($b * $l) / $yb;
	}
	$nr=tohex($r);
	$ng=tohex($g);
	$nb=tohex($b);
	return '#'.$nr.$ng.$nb;
}

function tohex($n)
{
	$hexch = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
	$n = round($n);
	$l = $n % 16;
	$h = floor(($n / 16)) % 16;
	return ''.$hexch[$h].''.$hexch[$l].'';
}


$mcolor		= $_GET['color'];
if(!$mcolor)$mcolor= '000000';//默认样式

$mcolor		= '#'.$mcolor;
$xtcolor	= color($mcolor,140);
$bgcolor	= color($mcolor,155);//背景颜色
$ahover		= color($mcolor,150);
$abott		= color($mcolor,160);
$abotc		= color($mcolor,145);
$gridsel	= color($mcolor,200);
$scoloa		= color($mcolor,225);

//渐变颜色
$topjs		= $ahover;
$topje		= $xtcolor;
?>


/*全局样式*/
body{padding:0px;margin:0px;border:0;overflow:auto}
*{font-family:'微软雅黑';font-size:14px}
a{TEXT-DECORATION:none;cursor:pointer;list-style-type:none;}
img{border:0}
h2,h1,h3,h4{font-family:'微软雅黑'}
label{font-weight:100;}
.blank10{height:10px;overflow:hidden}
.blank20{height:20px;overflow:hidden}
.blank15{height:15px;overflow:hidden}
.blank5{height:5px;overflow:hidden}
.icons{height:16px;width:16px;overflow:hidden;cursor:pointer}


/*首页菜单*/
.page-menu{overflow:auto}
.page-menu li,ul{list-style-type:none;padding:0}
.page-menu a{TEXT-DECORATION:none;overflow:hidden;border-bottom:1px <?=$abott?> solid;padding:10px 10px;display:list-item;color:#ffffff;margin:2px 2px;position: relative;}
.page-menu a:hover{background:<?=$ahover?>;color:#ffffff;}
.page-menu a font{position: absolute; right:0px; top:13px}
.page-menu a img{margin-right:10px}
.page-content{overflow:hidden;}
.menuout1{position: absolute;z-index:98;background-color:<?=$xtcolor?>;padding:10px 10px;border:2px <?=$xtcolor?> solid;width:204px;color:#ffffff}
.menuout1 img{margin-right:10px}
.menuout2{position: absolute;z-index:99;left:199px;border:2px <?=$xtcolor?> solid;background-color:<?=$xtcolor?>;width:230px;box-shadow: 5px 5px 5px #aaaaaa;}
.menuout2 a{border-bottom:1px <?=$abotc?> solid}
.indexbody{background-color:<?=$bgcolor?>;overflow:hidden;border:1px <?=$mcolor?> solid;}
.indexborder{border:1px <?=$mcolor?> solid;}
.splitys{border:1px <?=$abott?> solid;}
.box{box-shadow:4px 4px 4px #aaaaaa}
/*首页头部*/
.indextop{overflow:hidden;height:45px;border:1px <?=$mcolor?> solid;color:#ffffff;
	background:<?=$xtcolor?>;
	background:-moz-linear-gradient(top, <?=$ahover?>, <?=$xtcolor?>);
	background:<?=$xtcolor?> -webkit-linear-gradient(top,<?=$ahover?>,<?=$xtcolor?>);
}
.indextop .tnav a{TEXT-DECORATION:none;overflow:hidden;display:block;height:45px;float:left;padding:12px 12px;color:#ffffff}
.indextop .tnav a:hover{color:#ffffff;background:block}
.indextop .tnav .active{color:#ffffff;background:<?=$mcolor?>}


/*选择卡样式*/
.xttabs{height:41px}
.xttabs a {TEXT-DECORATION:none;overflow:hidden;border-bottom:1px #cccccc solid;padding:0px 15px 0px 15px;display:block;position: relative; height:40px;float:left;cursor:pointer;color:#888888;}
.xttabs a font{margin-top:8px;display:block;}
.xttabs a.active{border:1px #cccccc solid;border-width:1px 1px 0px 1px;background:white;font-weight:bold;padding:0px 15px;color:<?=$xtcolor?>}
.xttabs a.activeleft{padding:0px;width:10px}
.xttabs a span{position: absolute;right:2px;top:0px;cursor:pointer;}
.xttabs a span img{opacity:0.2;filter:Alpha(Opacity=20);}
.xttabs a.active span img{opacity:1;filter:Alpha(Opacity=100);}


/*表格列样式border-radius:5px*/
.mborder{border:1px #cccccc solid;}
.gridtable td,.gridtable th{border-bottom:1px #dddddd solid;padding:2px 3px;height:30px;}
.gridtable th{border-left:1px #cccccc solid;}
.gridtable tr:hover{background-color:#dddddd;}
.gridtable div.headtr{font-weight:bold;position: relative;}
.gridtable div.headtr img.asc{position: absolute; right:1px;top:5px;cursor:pointer;}
.gridtable div.headtr img.desc{position: absolute; right:1px;top:12px;cursor:pointer;}

/*树的样式*/
.treetable td,.treetable th{border-bottom:1px #dddddd solid;padding:2px 3px;height:30px;}
.treetable th{border-left:1px #cccccc solid;}
.treetable tr:hover{background-color:#dddddd;}
.treetable div.headtr{font-weight:bold;}



/*总样式*/
a.zhu{color:#ffffff}
.selected{background-color:<?=$gridsel?>}
input.button{border:1px <?=$mcolor?> solid;color:#ffffff;padding:3px 5px;height:30px;line-height:20px;cursor:pointer;
background: -moz-linear-gradient(top, <?=$ahover?>, <?=$xtcolor?>);
background:-webkit-linear-gradient(top,<?=$ahover?>,<?=$xtcolor?>);
background-color:<?=$xtcolor?>;
}
input.button:hover{background:<?=$mcolor?>;}
input.button-big{padding:5px 15px;height:35px;line-height:25px;}
input.button-sml{padding:2px 5px;height:25px}

.stitle{background-color:<?=$scoloa?>;line-height:30px;text-align:center;border:1px <?=$scoloa?> solid;}
.barinput{padding:0px 2px;width:150px;height:23px;border:1px #888888 solid;line-height:18px}
.xttitle{
	font-size:16px;color:#ffffff;padding:6px 10px;
	background: -moz-linear-gradient(top, <?=$ahover?>, <?=$xtcolor?>);
	background:-webkit-linear-gradient(top,<?=$ahover?>,<?=$xtcolor?>);
	background-color:<?=$xtcolor?>;
	position:relative;
}
.xtbody{ padding:10px 15px}
.xtborder{border:1px <?=$mcolor?> solid;}
.pagination li.active{background-color:<?=$mcolor?>}
.xttitle span{position:absolute;top:8px;right:5px}

.inputdate{background:#ffffff url(../images/calendar.png)no-repeat right}
.inputtime{background:#ffffff url(../images/clock.png)no-repeat right}
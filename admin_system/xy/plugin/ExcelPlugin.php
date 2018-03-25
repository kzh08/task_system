<?
/*
    Name: phpClass
    Author: Rock
    Version: 1.0.0
    Date: 2013-09-11 10:00:00
*/


final class ExcelPlugin
{

	/**
		获取url上的参数
		@param	string  $key	参数名
		@param  string  $dev    默认值
		@return	string
	*/
	public function request($key, $dev='')
	{
		$val	=	$dev;
		if(isset($_REQUEST[$key]))
		{
			$val=trim($_REQUEST[$key]);
		}
		return $val;
	}

	/**
		创建table表格数据
		@param	string  $title   标题
		@param	string  $rows  	 下载导出数据
		@param	string  $headstr (选填)表格表头(如：lie1,列1,left@lie2,列2,center)
		@return	string
	*/
	public function createtable($title, $rows, $headstr='', $type='')
	{
		if($headstr == '')$headstr	= $this->request('header');
		if($headstr	== '')return '';
		$arrh		= explode('@', $headstr);
		$thead		= count($arrh);
		for($i=0; $i<$thead; $i++){
			$te_str	= $arrh[$i];
			if(count(explode(',', $te_str)) < 3)$te_str.=',center';
			$head[]	= explode(',', $te_str);
		}
		$txt	 = '';	
		if($type == '')$txt	 = '<html><head><title>'.$title.'</title><style>*{font-size:12px}table td{padding:2px 3px}</style></head><body>';
		$txt	.= '<table width="100%" border="1" cellspacing="0" bordercolor="#cccccc" cellpadding="0" align="center" style="border-collapse:collapse;" >';
		$txt	.= '<tr><td colspan="'.$thead.'" align="center" height="30"><b>'.$title.'</b></td></tr>';
		$txt	.= '<tr bgcolor="#6fc6ff">';
		for($h=0; $h<$thead; $h++)$txt.= '<td align="'.$head[$h][2].'" nowrap>'.$head[$h][1].'</td>';
		$txt	.= '</tr>';
		if($rows){
			foreach($rows as $rs){
				$txt	.= '<tr>';
				for($h=0; $h<$thead; $h++)$txt	.= '<td align="'.$head[$h][2].'">'.$rs[$head[$h][0]].'</td>';
				$txt	.= '</tr>';
			}
		}
		if($type == ''){
			$txt	.= '<tr><td colspan="'.$thead.'" align="left" >时间：'.date('Y-m-d H:i:s').' &nbsp; 共'.count($rows).'条记录';
			$txt	.= '</td></tr>';
		}
		$txt	.= '</table>';
		if($type == '')$txt	.= '</body></html>';
		
		header('Content-Type: text/xls'); 
		header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );     
		header('Content-Disposition: attachment;filename="'.iconv("UTF-8", "GB2312", $title).'.xls"');      
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');        
		header('Expires:0');         
		header('Pragma:public');
		
		exit(iconv("UTF-8", "GB2312", $txt));
	}
}
<?php
// +----------------------------------------------------------------------
// | 分页的插件哦
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.rili123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: chenxh <qqqq2900@126.com>
// +----------------------------------------------------------------------
//todo:需要重构

final class PagingPlugin {
	public $prefix;
	public $limit		= 10;	//默认每页数目
	
	public $louarr		= array('LIKE', '=', '>', '>=', '<', '<=', '<>', 'LEFT LIKE','RIGHT LIKE','NOT LIKE');
	
	/**
		分页
		@param	string	$table 	表
		@param	string	$where 	条件
		@param	string	$order 	排序如`id` desc
		@param	string	$fields 字段
		@param	array	$arr 	其他参数数组
		@return array
	*/
	public function getLimit($table, $where, $order='', $fields='*', $arr=array())
	{
		if(strpos($table,' ')===false){
			$this->prefix	= $GLOBALS['X_G']['db']['prefix'];
			$table			= $this->prefix . $table;
		}
		$group	  		= '';
		$count			= 0;
		$limitall		= false;
		$getwhbool		= true;
		$getorbool		= true;
		if(isset($arr['all']))$limitall= $arr['all'];	//判断是不是要全部数据
		if(isset($arr['count']))$count = $arr['count'];
		if(isset($arr['getwhbool']))$getwhbool = $arr['getwhbool'];
		if(isset($arr['getorbool']))$getorbool = $arr['getorbool'];
		if(isset($arr['group']))$group=" group by ".$arr['group']." ";
		
		$page		= (int)$_REQUEST['page'];		//当前页数
		$limit		= (int)$_REQUEST['limit'];		//每页数量
		$gridorder 	= $_REQUEST['gridorder'];
		$order		= $this->getOrder($order,$getorbool);
        $order      = empty($order) ? '' : 'order by'.$order;
		$where		= $this->getWhere($where);
		if(isset($arr['sou'])){
			$where		= str_replace($arr['sou'],$arr['rep'],$where);
			$order		= str_replace($arr['sou'],$arr['rep'],$order);
		}
		if($limit<=0)$limit=$this->limit;
		$db	= $GLOBALS['X_G']["Db"];
		
		//计算总记录数
		if($count==0){
			$sqlc	= "SELECT count(1) FROM $table where $where";
			$result	= $db->exec($sqlc);
			$row    = mysql_fetch_row($result);
			$count 	= (int)$row[0];
		}
		
		$maxpage= ceil($count/$limit);//总页数
		
		if($page>$maxpage)$page=$maxpage;
		if($page<=0)$page=1;
		
		$sql	= "select $fields from $table where $where $group $order ";
		if(!$limitall)$sql.=" limit ".($page-1)*$limit.",$limit";
		
		if($gridorder!='')$url.='/gridorder/'.$gridorder.'';
		$url.='/menuid/'.$_REQUEST['menuid'].'';
		$url.=$this->getUrl();
		$paging	= array(
			'page'	=> $page,
			'limit'	=> $limit,
			'count'	=> $count,
			'maxpage'=> $maxpage,
			'nextpage'=>$page+1,
			'prevpage'=>$page-1,
			'url'	 => $url	//其他参数URL
		);
		$rows	= array();
		$result	= $db->exec($sql);
		while($row	= mysql_fetch_array($result)){
			$rows[]	= $row;
		}
		
		$resuc	= array(
			'num'		=> rand(100,999),
			'gridorder'	=> $gridorder,
			'rows'		=> $rows,
			'selcheck'	=> false,		//是否有复选框
			'paging'	=> $paging		//分页的参数
		);
		return $resuc;
	}
	
	/**
	 *	单独分页函数
	 *	@param	int	$count 	数据总量
	 *	@return array
	 */
	public function getPage($count){
		$page		= $_REQUEST['page']			? $_REQUEST['page']		: 1;
		$limit		= $_REQUEST['limit'] > 0	? $_REQUEST['limit']	: $this->limit;
		$gridorder 	= $_REQUEST['gridorder'];
		
		$maxpage	= ceil($count/$limit);//总页数
		
		if($gridorder!=''){
			$url	.='/gridorder/'.$gridorder.'';
		}
		$url	.= '/menuid/'.$_REQUEST['menuid'].'';
		$url	.= $this->getUrl();
		
		$paging	= array(
			'page'		=> $page,
			'limit'		=> $limit,
			'count'		=> $count,
			'maxpage'	=> $maxpage,
			'nextpage'	=> $page+1,
			'prevpage'	=> $page-1,
			'url'	 	=> $url
		);

        $startDate = $_REQUEST['startDate'];
        $endDate   = $_REQUEST['endDate'];
        if (!empty($endDate) && !empty($startDate)) {
            $paging["date"] = "startDate/".$startDate."/endDate/".$endDate;
        } elseif(!empty($endDate)) {
            $paging["date"] = "endDate/".$endDate;
        } elseif (!empty($startDate)) {
            $paging["date"] = "startDate/".$startDate;
        }

        return $paging;
	}
	
	/**
		获取排序
		@param	string	$order 	默认排序
		@param	boolean	$bol 	是否合并
		@return string		
	*/
	public function getOrder($order='',$bol=true)
	{
		$gridorder = $_REQUEST['gridorder'];	//排序的
		if($gridorder != ''){
			$orarr	= explode('-',$gridorder);
			$order	="`$orarr[0]` $orarr[1]";
		}
		if($order != '')$order=" $order ";
		return $order;
	}
	
	/**
		获取条件参数是以param0/1/2/3_开头，数组代表连接处理
		@param	string	$where 	条件
		@return string			
	*/
	public function getWhere($where = '')
	{
		foreach($_REQUEST as $k=>$v){
			if(strpos($k, 'param') !== false && strpos($k, '_') !== false){
				$cant	= explode('_', $k);
				$lj	= (int)substr($cant[0],-1,1);
				$ljv= $this->louarr[$lj];
				$val= $v;
				if($ljv == 'LIKE' || $ljv == 'NOT LIKE'){
					$val = "'%".$val."%'";
				}else if($ljv == 'LEFT LIKE'){
					$ljv = 'LIKE';
					$val = "'".$val."%'";
				}else if($ljv == 'RIGHT LIKE'){
					$ljv = 'LIKE';
					$val = "'%".$val."'";
				}else{
					$val = "'".$val."'";
				}
				$cans	= $cant[1];//如果参数字段也有_时param0_date_time的情况
				if(count($cant)>2)$cans.='_'.$cant[2].'';
				if(count($cant)>3)$cans.='_'.$cant[3].'';
				$where.=" and `$cans` $ljv $val";
			}
		}
		return $where;
	}
	
	/**
		获取URL
	*/
	public function getUrl($url='')
	{
		foreach($_REQUEST as $k=>$v){
			if(strpos($k, 'param') !== false && strpos($k, '_') !== false){
				$url.='/'.$k.'/'.$v.'';
			}
		}
		return $url;
	}
}

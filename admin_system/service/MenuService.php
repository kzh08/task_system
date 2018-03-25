<?php
/*
   +----------------------------------------------------------------------+
   |                  			  xy framework                    	  	  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2013 http://www.xiaoy.name   All rights reserved.      |
   +----------------------------------------------------------------------+
   | 菜单服务，提供搜索菜单等               				      	 	  	      |
   +----------------------------------------------------------------------+
   | Authors: xiaoy <zs1379@vip.qq.com>       CreateTime:2014-08-27       |
   +----------------------------------------------------------------------+
*/


class MenuService extends XyService {

	public $prefix	= "";

	public function init(){
		$this->prefix	= $GLOBALS['X_G']["db"]["prefix"];
	}

	/**
     * todo:重构，是否还有意义
	*/
	function getUserExt($uid, $lec='')
	{
		$gasql	= " ( id in( select sid from xy_privilege where type='ug' and mid='$uid') or id in( select mid from ".$this->prefix."privilege where type='gu' and sid='$uid') )";//用户所在组id
		$gsql	= "select id from ".$this->prefix."dept where $gasql ";
		
		$owhe	= " and (id in(select sid from ".$this->prefix."privilege where ((type='um' and mid='$uid') or (type='uu' and mid='$uid') or (type='gm' and mid in($gsql) ) ) ) or id in(select mid from ".$this->prefix."privilege where ((type='mu' and sid='$uid') or (type='mg' and sid in($gsql) )) ))";
		
		$sql	= "select count(1) from xy_dept where ispir=0 and $gasql";
		$result	= $this->doSql($sql);
		$row    = mysql_fetch_row($result);
		$count 	= (int)$row[0];

		if($count>0) $owhe=''; 	//不用权限验证的用户(是管理员)
		
		$guid	= '[0]';
		if($owhe != '' || $lec=='all'){
			$arss	= $this->doSql("select id,pid,(select pid from xy_menu where id=a.pid)as mpid from xy_menu a where (status=1 $owhe) or (status=1 and ispir=0) order by sort");
			while(list($bid, $bpid, $bmpid)= mysql_fetch_array($arss)){
				$guid.=',['.$bid.']';
				//读取上级id
				if(strpos($guid, '['.$bpid.']')===false){
					$guid.=',['.$bpid.']';
				}
				//读取上级id的上级id
				if($bmpid !=null && $bmpid!=''){
					if(strpos($guid, '['.$bmpid.']')===false){
						$guid.=',['.$bmpid.']';
					}
				}
			}
		}else{
			$guid = '-1';
		}
		return $guid;
	}

    /**
     * 根據PID取得相关的菜单
     *
     * @param $where int pid的值
     * @return array 菜单
     */
    public function getMenu($where){

        //取得用戶信息
        $whereUser = array(
            'id'  => $_SESSION[$GLOBALS['X_G']['website']['projectEnName'] . 'adminId'],
        );

        $userInfo = s('xy_admin')->get($whereUser, 'privilege');

        $privilege = ',' . $userInfo['privilege'] . ',';

        $sql    = "select id,name,icons,url,num,color,level,pid,ispir from xy_menu where pid = " . $where . " and status=1 order by level,pid,sort";

        $menus	= $this->doSql($sql);

        $result     = array();
        $lastResult = array();

        while($row	= mysql_fetch_array($menus, MYSQL_ASSOC)){
            if($userInfo['privilege'] != '-1' && strpos($privilege, $row['id']) === false && $row['ispir'] != 0){
                continue;
            }

            $tempResult['id']                       = $row['id'];
            $tempResult['name']                     = $row['name'];
            $tempResult['icons']                    = $row['icons'];
            $tempResult['url']                      = $row['url'];
            $tempResult['color']                    = $row['color'];

            if($row['level'] == 2){
                $result[$row['id']]                 = $tempResult;
            }else{

                $result[$row['pid']]['total']       += 1;
                $result[$row['pid']]['children'][]  = $tempResult;
            }
        }

        foreach($result as $value){
            $lastResult[] = $value;
        }

        return $lastResult;
	}

    /**
     * 取得菜单列表用于管理
     *
     * @param $pid      int         pid值
     * @param $where    string      搜索项
     * @return array
     */
    public function getMenuTree($pid = 0, $where = '')
	{
        $this->db->tableName = 'xy_menu';

		$rows = $this->getAll("pid = '" . $pid . "'" . $where, '*', 'sort');

		foreach($rows as $k=>$rs){
			$id	        = $rs['id'];

			//判断是否有下级
			$nestCount	= $this->getNum("pid='$id'");

			if($nestCount > 0){
                $rows[$k]['children']	= $this->getmenuTree($id, $where);
            }
		}

		return $rows;
	}

    /**
     * 保存用户权限
     *
     * @param $id           int     要修改权限的用户
     * @param $checkAid     string  用户的新权限
     */
    public function saveExtent($id, $checkAid)
	{
        $this->db->tableName = 'xy_admin';

        $updateData = array(
            "privilege" => $checkAid
        );

        $where = array(
            'id' => $id
        );

		$this->U($updateData, $where);
	}
	
}